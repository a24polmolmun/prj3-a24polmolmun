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
// Temporitzadors d'expiració: { "eventId-seatId": timerId }
const lockTimers = {};
let totalConnectedUsers = 0;

const LOCK_TIMEOUT = 5 * 60 * 1000; // 5 minuts

// Endpoint HTTP per a Laravel
app.post("/api/broadcast-sold", (req, res) => {
    const { eventId, seatIds } = req.body;

    if (!eventId || !seatIds || !Array.isArray(seatIds)) {
        return res.status(400).json({ success: false, message: "Falten dades o format incorrecte" });
    }

    // Emet l'esdeveniment 'seats-sold' a tots els clients de la sala de l'esdeveniment
    io.to(`event-${eventId}`).emit("seats-sold", seatIds);

    // Netejar bloquejos temporals i temporitzadors si n'hi hagués per a aquests seients
    if (lockedSeats[eventId]) {
        seatIds.forEach(id => {
            const timerKey = `${eventId}-${id}`;
            if (lockTimers[timerKey]) {
                clearTimeout(lockTimers[timerKey]);
                delete lockTimers[timerKey];
            }
            delete lockedSeats[eventId][id];
        });
    }

    console.log(`Venda confirmada rebiuda de Laravel per a l'esdeveniment ${eventId}:`, seatIds);
    res.json({ success: true, message: "Notificació de venda enviada" });
});

io.on("connection", (socket) => {
    totalConnectedUsers++;
    console.log("Client connectat:", socket.id, "| Total:", totalConnectedUsers);

    // Notificar a tothom (o almenys als admins) el nou comptador
    io.emit("user-count-update", totalConnectedUsers);

    socket.on("join-admin", () => {
        socket.join("admin-room");
        console.log(`L'usuari ${socket.id} ha entrat al panell d'administració`);
        socket.emit("user-count-update", totalConnectedUsers);
    });

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

        // Gestionar temporitzador d'expiració (5 minuts)
        const timerKey = `${eventId}-${seatId}`;
        if (lockTimers[timerKey]) clearTimeout(lockTimers[timerKey]);

        lockTimers[timerKey] = setTimeout(() => {
            if (lockedSeats[eventId] && lockedSeats[eventId][seatId] === socket.id) {
                delete lockedSeats[eventId][seatId];
                delete lockTimers[timerKey];
                io.to(`event-${eventId}`).emit("seat-unlocked", seatId);
                console.log(`Seient ${seatId} alliberat per expiració de temps (5 min)`);
            }
        }, LOCK_TIMEOUT);

        socket.to(`event-${eventId}`).emit("seat-locked", { seatId, userId: socket.id });
        console.log(`Seient ${seatId} bloquejat per ${socket.id} (Esdeveniment: ${eventId})`);
    });

    socket.on("seat-unlock", ({ eventId, seatId }) => {
        if (lockedSeats[eventId] && lockedSeats[eventId][seatId] === socket.id) {
            delete lockedSeats[eventId][seatId];

            // Cancel·lar el temporitzador
            const timerKey = `${eventId}-${seatId}`;
            if (lockTimers[timerKey]) {
                clearTimeout(lockTimers[timerKey]);
                delete lockTimers[timerKey];
            }

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

                    // Netejar temporitzadors
                    const timerKey = `${eventId}-${seatId}`;
                    if (lockTimers[timerKey]) {
                        clearTimeout(lockTimers[timerKey]);
                        delete lockTimers[timerKey];
                    }

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
        totalConnectedUsers--;
        console.log("Client desconnectat:", socket.id, "| Total:", totalConnectedUsers);
        io.emit("user-count-update", totalConnectedUsers);

        // Alliberar seients i netejar temporitzadors en desconnectar
        for (const eventId in lockedSeats) {
            const releasedSeats = [];
            for (const seatId in lockedSeats[eventId]) {
                if (lockedSeats[eventId][seatId] === socket.id) {
                    releasedSeats.push(seatId);

                    // Cancel·lar temporitzador d'expiració
                    const timerKey = `${eventId}-${seatId}`;
                    if (lockTimers[timerKey]) {
                        clearTimeout(lockTimers[timerKey]);
                        delete lockTimers[timerKey];
                    }

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
