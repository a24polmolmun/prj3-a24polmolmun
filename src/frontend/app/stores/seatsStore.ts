import { defineStore } from 'pinia'
import { useSocket } from '../composables/useSocket'

export type SeatStatus = 'disponible' | 'reservat' | 'venut' | 'seleccionat'

export interface Seient {
    id: string
    fila: string
    numero: number
    estat: SeatStatus
}

/**
 * Nota: L'estat 'reservat' es visualitza en color groc/taronja.
 * Aquest estat se sincronitzarà via WebSockets per mostrar bloquejos
 * en temps real d'altres usuaris.
 */
export const useSeatsStore = defineStore('seats', {
    state: () => {
        const rows = ['A', 'B', 'C', 'D', 'E']
        const seatsPerRow = 8
        const seats: Seient[] = []

        rows.forEach(fila => {
            for (let i = 1; i <= seatsPerRow; i++) {
                // Tots els seients comencen com a disponibles per ara
                let estat: SeatStatus = 'disponible'

                seats.push({
                    id: `${fila}${i}`,
                    fila,
                    numero: i,
                    estat
                })
            }
        })

        return {
            seats,
            maxSelection: 0,
            currentEventId: null as number | null
        }
    },
    getters: {
        selectedCount: (state) => state.seats.filter(s => s.estat === 'seleccionat').length
    },
    actions: {
        initSocket(eventId: number) {
            this.currentEventId = eventId
            const { onSeatLocked, onSeatUnlocked, onSeatsReleased, onSyncLockedSeats, joinEvent } = useSocket()

            joinEvent(eventId)

            onSyncLockedSeats((lockedSeats) => {
                Object.keys(lockedSeats).forEach(seatId => {
                    const seat = this.seats.find(s => s.id === seatId)
                    if (seat && seat.estat === 'disponible') {
                        seat.estat = 'reservat'
                    }
                })
            })

            onSeatLocked(({ seatId }) => {
                const seat = this.seats.find(s => s.id === seatId)
                if (seat && seat.estat === 'disponible') {
                    seat.estat = 'reservat'
                }
            })

            onSeatUnlocked((seatId) => {
                const seat = this.seats.find(s => s.id === seatId)
                if (seat && seat.estat === 'reservat') {
                    seat.estat = 'disponible'
                }
            })

            onSeatsReleased((seatIds) => {
                seatIds.forEach(id => {
                    const seat = this.seats.find(s => s.id === id)
                    if (seat && seat.estat === 'reservat') {
                        seat.estat = 'disponible'
                    }
                })
            })
        },
        toggleSeatSelection(id: string) {
            const seat = this.seats.find(s => s.id === id)
            if (seat) {
                const { lockSeat, unlockSeat } = useSocket()
                if (seat.estat === 'seleccionat') {
                    seat.estat = 'disponible'
                    if (this.currentEventId) unlockSeat(this.currentEventId, id)
                } else if (seat.estat === 'disponible') {
                    if (this.selectedCount < this.maxSelection) {
                        seat.estat = 'seleccionat'
                        if (this.currentEventId) lockSeat(this.currentEventId, id)
                    } else {
                        console.log('Límit d\'entrades assolit')
                    }
                }
            }
        },
        setMaxSelection(count: number) {
            this.maxSelection = count
        },
        resetSelection() {
            this.seats.forEach(seat => {
                if (seat.estat === 'seleccionat') {
                    seat.estat = 'disponible'
                }
            })
        }
    }
})
