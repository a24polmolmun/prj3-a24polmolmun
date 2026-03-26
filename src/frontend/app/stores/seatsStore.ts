import { defineStore } from 'pinia'

export type SeatStatus = 'disponible' | 'reservat' | 'venut' | 'seleccionat'

export interface Seient {
    id: string
    fila: string
    numero: number
    estat: SeatStatus
}

export const useSeatsStore = defineStore('seats', {
    state: () => {
        const rows = ['A', 'B', 'C', 'D', 'E']
        const seatsPerRow = 8
        const seats: Seient[] = []

        rows.forEach(fila => {
            for (let i = 1; i <= seatsPerRow; i++) {
                // All seats start as available for now
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
            seats
        }
    },
    actions: {
        toggleSeatSelection(id: string) {
            const seat = this.seats.find(s => s.id === id)
            if (seat && (seat.estat === 'disponible' || seat.estat === 'seleccionat')) {
                seat.estat = seat.estat === 'disponible' ? 'seleccionat' : 'disponible'
            }
        },
        resetSelection() {
            this.seats.forEach(seat => {
                seat.estat = 'disponible'
            })
        }
    }
})
