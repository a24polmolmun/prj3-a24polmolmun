import { defineStore } from 'pinia'
import { useSocket } from '../composables/useSocket'

export type SeatStatus = 'disponible' | 'reservat' | 'venut' | 'seleccionat'

export interface Seient {
    id: string | number
    fila: string
    numero: number
    estat: SeatStatus
}

export const useSeatsStore = defineStore('seats', {
    state: () => {
        return {
            seats: [] as Seient[],
            maxSelection: 0,
            currentEventId: null as number | null
        }
    },
    getters: {
        selectedCount: (state) => state.seats.filter(s => s.estat === 'seleccionat').length
    },
    actions: {
        setSeats(seats: Seient[]) {
            this.seats = seats
        },
        initSocket(eventId: number) {
            this.currentEventId = eventId
            const { onSeatLocked, onSeatUnlocked, onSeatsReleased, onSyncLockedSeats, onSeatsSold, joinEvent } = useSocket()

            joinEvent(eventId)

            onSyncLockedSeats((lockedSeats) => {
                Object.keys(lockedSeats).forEach(seatId => {
                    const seat = this.seats.find(s => String(s.id) === String(seatId))
                    if (seat && seat.estat === 'disponible') {
                        seat.estat = 'reservat'
                    }
                })
            })

            onSeatLocked(({ seatId }) => {
                const seat = this.seats.find(s => String(s.id) === String(seatId))
                if (seat && seat.estat === 'disponible') {
                    seat.estat = 'reservat'
                }
            })

            onSeatUnlocked((seatId) => {
                const seat = this.seats.find(s => String(s.id) === String(seatId))
                if (seat && seat.estat === 'reservat') {
                    seat.estat = 'disponible'
                }
            })

            onSeatsReleased((seatIds) => {
                seatIds.forEach(id => {
                    const seat = this.seats.find(s => String(s.id) === String(id))
                    if (seat && seat.estat === 'reservat') {
                        seat.estat = 'disponible'
                    }
                })
            })

            onSeatsSold((seatIds) => {
                seatIds.forEach(id => {
                    const seat = this.seats.find(s => String(s.id) === String(id))
                    if (seat) {
                        seat.estat = 'venut'
                    }
                })
            })
        },
        toggleSeatSelection(id: string | number) {
            const seat = this.seats.find(s => String(s.id) === String(id))
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
