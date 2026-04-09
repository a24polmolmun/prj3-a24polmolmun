import { describe, it, expect } from 'vitest'
import { mount } from '@vue/test-utils'
import SeatItem from '../app/components/SeatItem.vue'

describe('SeatItem.vue', () => {
    it('aplica la classe CSS de reservat correctament', () => {
        const mockSeient = { id: 1, fila: 'A', numero: 5, estat: 'reservat' as const }
        const wrapper = mount(SeatItem, {
            props: {
                seient: mockSeient
            }
        })

        // Comprovar que la classe seat-reservat està present
        expect(wrapper.classes()).toContain('seat-reservat')

        // Comprovar que el botó està deshabilitat
        const button = wrapper.find('button')
        expect(button.element.disabled).toBe(true)
    })

    it('mostra el número de la butaca', () => {
        const mockSeient = { id: 1, fila: 'A', numero: 12, estat: 'disponible' as const }
        const wrapper = mount(SeatItem, {
            props: {
                seient: mockSeient
            }
        })

        expect(wrapper.text()).toContain('12')
    })

    it('emet l\'esdeveniment toggle en fer clic si està lliure', async () => {
        const mockSeient = { id: 1, fila: 'A', numero: 1, estat: 'disponible' as const }
        const wrapper = mount(SeatItem, {
            props: {
                seient: mockSeient
            }
        })

        await wrapper.find('button').trigger('click')

        expect(wrapper.emitted()).toHaveProperty('toggle')
        expect(wrapper.emitted('toggle')![0]).toEqual([1])
    })
})
