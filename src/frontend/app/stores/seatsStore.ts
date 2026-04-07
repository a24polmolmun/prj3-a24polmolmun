import { defineStore } from 'pinia'

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
            seats,
            maxSelection: 0
        }
    },
    getters: {
        selectedCount: (state) => state.seats.filter(s => s.estat === 'seleccionat').length
    },
    actions: {
        toggleSeatSelection(id: string) {
            const seat = this.seats.find(s => s.id === id)
            if (seat) {
                if (seat.estat === 'seleccionat') {
                    seat.estat = 'disponible'
                } else if (seat.estat === 'disponible') {
                    if (this.selectedCount < this.maxSelection) {
                        seat.estat = 'seleccionat'
                    } else {
                        // Optional: trigger a notification or just block
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
