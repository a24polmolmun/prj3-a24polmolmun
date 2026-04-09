import { describe, it, expect, beforeEach, vi } from 'vitest'
import { setActivePinia, createPinia } from 'pinia'
import { useSeatsStore } from '../app/stores/seatsStore'

// Mock de useSocket ja que no volem provar la connexió real de xarxa
vi.mock('../app/composables/useSocket', () => ({
    useSocket: () => ({
        lockSeat: vi.fn(),
        unlockSeat: vi.fn(),
        onSeatLocked: vi.fn(),
        onSeatUnlocked: vi.fn(),
        onSeatsReleased: vi.fn(),
        onSyncLockedSeats: vi.fn(),
        onSeatsSold: vi.fn(),
        joinEvent: vi.fn(),
        cleanup: vi.fn()
    })
}))

describe('Seats Store', () => {
    beforeEach(() => {
        setActivePinia(createPinia())
    })

    it('inicialitza amb un estat buit', () => {
        const store = useSeatsStore()
        expect(store.seats).toEqual([])
        expect(store.selectedCount).toBe(0)
    })

    it('permet carregar seients', () => {
        const store = useSeatsStore()
        const mockSeats = [
            { id: 1, fila: 'A', numero: 1, estat: 'disponible' as const },
            { id: 2, fila: 'A', numero: 2, estat: 'disponible' as const }
        ]
        store.setSeats(mockSeats)
        expect(store.seats).toHaveLength(2)
        expect(store.seats[0].fila).toBe('A')
    })

    it('canvia l\'estat a seleccionat en triar una butaca disponible', () => {
        const store = useSeatsStore()
        store.setSeats([{ id: 1, fila: 'A', numero: 1, estat: 'disponible' as const }])
        store.setMaxSelection(5)

        store.toggleSeatSelection(1)

        expect(store.seats[0].estat).toBe('seleccionat')
        expect(store.selectedCount).toBe(1)
    })

    it('deselecciona una butaca si ja estava seleccionada', () => {
        const store = useSeatsStore()
        store.setSeats([{ id: 1, fila: 'A', numero: 1, estat: 'seleccionat' as const }])
        store.setMaxSelection(5)

        store.toggleSeatSelection(1)

        expect(store.seats[0].estat).toBe('disponible')
    })
})
