import { defineStore } from 'pinia'

interface Session {
    hora: string
}

interface Movie {
    id: number
    titol: string
    descripcio: string
    imatge: string
    sessions: string[]
}

export const useEventsStore = defineStore('events', {
    state: () => ({
        movies: [
            {
                id: 1,
                titol: 'Dune: Part Two',
                descripcio: "Paul Atreides s'uneix a Chani i als Fremen mentre busca venjança contra els conspiradors que van destruir la seva família.",
                imatge: 'https://images.unsplash.com/photo-1536440136628-849c177e76a1?q=80&w=1000&auto=format&fit=crop',
                sessions: ['16:00', '18:15', '20:30', '22:45']
            },
            {
                id: 2,
                titol: 'Oppenheimer',
                descripcio: "La història del físic J. Robert Oppenheimer i el seu paper en el desenvolupament de la bomba atòmica.",
                imatge: 'https://images.unsplash.com/photo-1485846234645-a62644f84728?q=80&w=1000&auto=format&fit=crop',
                sessions: ['17:00', '19:30', '22:00']
            },
            {
                id: 3,
                titol: 'Spider-Man: Across the Spider-Verse',
                descripcio: "Miles Morales és catapultat a través del Multivers, on es troba amb un equip de Spider-People encarregats de protegir-ne l'existència.",
                imatge: 'https://images.unsplash.com/photo-1635805737707-575885ab0820?q=80&w=1000&auto=format&fit=crop',
                sessions: ['15:45', '18:00', '20:15']
            }
        ] as Movie[]
    }),
    getters: {
        allMovies: (state: { movies: Movie[] }) => state.movies,
        getMovieById: (state: { movies: Movie[] }) => (id: number) => state.movies.find(m => m.id === id)
    }
})
