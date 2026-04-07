const { Server } = require("socket.io");
const http = require("http");

const httpServer = http.createServer();
const io = new Server(httpServer, {
    cors: {
        origin: "*", // En producció s'hauria de restringir
    },
});

// Estat en memòria: { eventId: { seatId: userId } }
const lockedSeats = {};

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
httpServer.listen(PORT, () => {
    console.log(`Servidor WebSocket funcionant al port ${PORT}`);
});
