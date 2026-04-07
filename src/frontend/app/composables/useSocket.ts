import { io, type Socket } from "socket.io-client";
import { ref } from "vue";

let socket: Socket | null = null;

export const useSocket = () => {
    const isConnected = ref(false);

    if (!socket) {
        // Connectar al servidor WebSocket (localhost per a desenvolupament)
        socket = io("http://localhost:4000");

        socket.on("connect", () => {
            isConnected.value = true;
            console.log("Connectat al servidor WebSocket");
        });

        socket.on("disconnect", () => {
            isConnected.value = false;
            console.log("Desconnectat del servidor WebSocket");
        });
    }

    const joinEvent = (eventId: number) => {
        socket?.emit("join-event", eventId);
    };

    const lockSeat = (eventId: number, seatId: string | number) => {
        socket?.emit("seat-lock", { eventId, seatId });
    };

    const unlockSeat = (eventId: number, seatId: string | number) => {
        socket?.emit("seat-unlock", { eventId, seatId });
    };

    const releaseAllSeats = (eventId: number) => {
        socket?.emit("release-all-seats", eventId);
    };

    const onSeatLocked = (callback: (data: { seatId: string, userId: string }) => void) => {
        socket?.on("seat-locked", callback);
    };

    const onSeatUnlocked = (callback: (seatId: string) => void) => {
        socket?.on("seat-unlocked", callback);
    };

    const onSeatsReleased = (callback: (seatIds: string[]) => void) => {
        socket?.on("seats-released", callback);
    };

    const onSyncLockedSeats = (callback: (lockedSeats: Record<string, string>) => void) => {
        socket?.on("sync-locked-seats", callback);
    };

    const onSeatsSold = (callback: (seatIds: (string | number)[]) => void) => {
        socket?.on("seats-sold", callback);
    };

    return {
        socket,
        isConnected,
        joinEvent,
        lockSeat,
        unlockSeat,
        releaseAllSeats,
        onSeatLocked,
        onSeatUnlocked,
        onSeatsReleased,
        onSyncLockedSeats,
        onSeatsSold
    };
};
