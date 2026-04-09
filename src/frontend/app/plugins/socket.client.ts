import { io, type Socket } from 'socket.io-client'

export default defineNuxtPlugin(() => {
    // Inicialitzem l'estat global de la connexió (compartit entre SSR i Client)
    // Per defecte posem true per evitar el banner durant el renderitzat del servidor (SSR)
    const isSocketConnected = useState('isSocketConnected', () => true)

    let socket: Socket | null = null

    if (process.client || import.meta.client) {
        console.log('Plugin Socket.IO: Intentant connectar a http://localhost:4000...');

        socket = io('http://localhost:4000', {
            reconnection: true,
            reconnectionAttempts: 5, // Limitem per debug
            reconnectionDelay: 1000,
        })

        // No posar isSocketConnected.value = false aquí directament per evitar el flaix
        // si la connexió és ràpida. Només si falla.

        socket.on('connect', () => {
            isSocketConnected.value = true
            console.log('Plugin Socket.IO: ✅ Connectat al port 4000')
        })

        socket.on('disconnect', (reason) => {
            isSocketConnected.value = false
            console.log('Plugin Socket.IO: ❌ Desconnectat:', reason)
        })

        socket.on('connect_error', (err) => {
            isSocketConnected.value = false
            console.warn('Plugin Socket.IO: ⚠️ Error de connexió:', err.message)
        })
    } else {
        console.log('Plugin Socket.IO: Running on server, skipping init.')
    }

    // Proveir el socket globalment (serà null al servidor, la qual cosa és correcta)
    return {
        provide: {
            socket: socket
        }
    }
})
