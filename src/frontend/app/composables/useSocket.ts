import type { Socket } from "socket.io-client";

export const useSocket = () => {
    const { $socket } = useNuxtApp()
    const socket = $socket as Socket
    const isConnected = useState<boolean>('isSocketConnected')

    const joinEvent = (eventId: number) => {
        socket.emit("join-event", eventId);
    };

    const lockSeat = (eventId: number, seatId: string | number) => {
        socket.emit("seat-lock", { eventId, seatId });
    };

    const unlockSeat = (eventId: number, seatId: string | number) => {
        socket.emit("seat-unlock", { eventId, seatId });
    };

    const releaseAllSeats = (eventId: number) => {
        socket.emit("release-all-seats", eventId);
    };

    const onSeatLocked = (callback: (data: { seatId: string, userId: string }) => void) => {
        socket.on("seat-locked", callback);
    };

    const onSeatUnlocked = (callback: (seatId: string) => void) => {
        socket.on("seat-unlocked", callback);
    };

    const onSeatsReleased = (callback: (seatIds: string[]) => void) => {
        socket.on("seats-released", callback);
    };

    const onSyncLockedSeats = (callback: (lockedSeats: Record<string, string>) => void) => {
        socket.on("sync-locked-seats", callback);
    };

    const onSeatsSold = (callback: (seatIds: (string | number)[]) => void) => {
        socket.on("seats-sold", callback);
    };

    const cleanup = () => {
        socket.off("seat-locked");
        socket.off("seat-unlocked");
        socket.off("seats-released");
        socket.off("sync-locked-seats");
        socket.off("seats-sold");
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
        onSeatsSold,
        cleanup
    };
};
