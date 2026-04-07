const { Server } = require("socket.io");
const http = require("http");
const express = require("express");

const app = express();
const httpServer = http.createServer(app);
const io = new Server(httpServer, {
    cors: {
        origin: "*", // En producció s'hauria de restringir
    },
});

app.use(express.json());

// Estat en memòria: { eventId: { seatId: userId } }
const lockedSeats = {};

// Endpoint HTTP per a Laravel
app.post("/api/broadcast-sold", (req, res) => {
    const { eventId, seatIds } = req.body;

    if (!eventId || !seatIds || !Array.isArray(seatIds)) {
        return res.status(400).json({ success: false, message: "Falten dades o format incorrecte" });
    }

    // Emet l'esdeveniment 'seats-sold' a tots els clients de la sala de l'esdeveniment
    io.to(`event-${eventId}`).emit("seats-sold", seatIds);

    // Netejar bloquejos temporals si n'hi hagués per a aquests seients
    if (lockedSeats[eventId]) {
        seatIds.forEach(id => delete lockedSeats[eventId][id]);
    }

    console.log(`Venda confirmada rebiuda de Laravel per a l'esdeveniment ${eventId}:`, seatIds);
    res.json({ success: true, message: "Notificació de venda enviada" });
});

io.on("connection", (socket) => {
    console.log("Client connectat:", socket.id);

    socket.on("join-event", (eventId) => {
        socket.join(`event-${eventId}`);
        console.log(`L'usuari ${socket.id} s'ha unit a l'esdeveniment ${eventId}`);

        // Enviar seients bloquejats actualment a aquest nou usuari
        if (lockedSeats[eventId]) {
            socket.emit("sync-locked-seats", lockedSeats[eventId]);
        }
    });

    socket.on("seat-lock", ({ eventId, seatId }) => {
        if (!lockedSeats[eventId]) {
            lockedSeats[eventId] = {};
        }

        lockedSeats[eventId][seatId] = socket.id;
        socket.to(`event-${eventId}`).emit("seat-locked", { seatId, userId: socket.id });
        console.log(`Seient ${seatId} bloquejat per ${socket.id} (Esdeveniment: ${eventId})`);
    });

    socket.on("seat-unlock", ({ eventId, seatId }) => {
        if (lockedSeats[eventId] && lockedSeats[eventId][seatId] === socket.id) {
            delete lockedSeats[eventId][seatId];
            socket.to(`event-${eventId}`).emit("seat-unlocked", seatId);
            console.log(`Seient ${seatId} desbloquejat per ${socket.id} (Esdeveniment: ${eventId})`);
        }
    });

    socket.on("release-all-seats", (eventId) => {
        if (lockedSeats[eventId]) {
            const releasedSeats = [];
            for (const seatId in lockedSeats[eventId]) {
                if (lockedSeats[eventId][seatId] === socket.id) {
                    releasedSeats.push(seatId);
                    delete lockedSeats[eventId][seatId];
                }
            }

            if (releasedSeats.length > 0) {
                socket.to(`event-${eventId}`).emit("seats-released", releasedSeats);
                console.log(`L'usuari ${socket.id} ha alliberat tots els seients a ${eventId}:`, releasedSeats);
            }
        }
    });

    socket.on("disconnect", () => {
        console.log("Client desconnectat:", socket.id);

        // Alliberar seients automàticament en desconnectar de tots els esdeveniments
        for (const eventId in lockedSeats) {
            const releasedSeats = [];
            for (const seatId in lockedSeats[eventId]) {
                if (lockedSeats[eventId][seatId] === socket.id) {
                    releasedSeats.push(seatId);
                    delete lockedSeats[eventId][seatId];
                }
            }

            if (releasedSeats.length > 0) {
                io.to(`event-${eventId}`).emit("seats-released", releasedSeats);
                console.log(`L'usuari desconnectat ${socket.id} ha alliberat seients a ${eventId}:`, releasedSeats);
            }
        }
    });
});

const PORT = 4000;
httpServer.listen(PORT, "0.0.0.0", () => {
    console.log(`Servidor WebSocket i API de notificacions funcionant al port ${PORT}`);
});
