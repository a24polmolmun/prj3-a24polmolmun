import { defineStore } from 'pinia'

export const useReviewStore = defineStore('reviews', {
    state: () => ({
        reviews: [] as any[],
        averageRating: 0,
        totalReviews: 0,
        isLoading: false,
        error: null as string | null
    }),

    actions: {
        async fetchReviews(eventId: number | string) {
            this.isLoading = true
            this.error = null
            try {
                const response: any = await $fetch(`http://localhost:8000/api/esdeveniments/${eventId}/reviews`)
                if (response.success) {
                    this.reviews = response.data
                    this.averageRating = response.average_rating
                    this.totalReviews = response.total_reviews
                }
            } catch (err: any) {
                this.error = "Error al carregar les ressenyes"
                console.error(err)
            } finally {
                this.isLoading = false
            }
        },

        async submitReview(reviewData: { esdeveniment_id: number | string, nom_usuari: string, rating: number, comment: string }) {
            this.isLoading = true
            this.error = null
            try {
                // En un entorn real, s'hauria de passar el token de Sanctum
                // Suposem que el middleware de Nuxt o el plugin de fetch ja el gestiona si s'ha fet login
                const response: any = await $fetch('http://localhost:8000/api/reviews', {
                    method: 'POST',
                    body: reviewData
                })

                if (response.success) {
                    // Refresquem per veure la nova ressenya i la nova mitjana
                    await this.fetchReviews(reviewData.esdeveniment_id)
                    return { success: true }
                }
            } catch (err: any) {
                const message = err.data?.message || err.data?.errors?.message || "Error al enviar la ressenya"
                this.error = message
                return { success: false, message }
            } finally {
                this.isLoading = false
            }
        }
    }
})
