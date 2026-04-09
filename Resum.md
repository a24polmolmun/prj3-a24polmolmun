# Resum del Projecte: Cinema Pol

**Curs:** 2DAW 2023-2024  
**Integrants:** Pol Molina Muñoz

## Abstract / Objectiu de l'App
Cinema Pol és una plataforma web de venda d'entrades en temps real. El seu objectiu és oferir una experiència de compra fluida per als clients, evitant la sobrevenda mitjançant bloquejos de seients concurrents, i proporcionar als administradors un panell integral per gestionar pel·lícules, aforaments i analítiques d'ocupació en directe.

---

## Arquitectura del Sistema
El sistema es divideix en serveis independents que s'interrelacionen per garantir escalabilitat i seguretat:

### 1. Frontend (Interfície d'Usuari)
* **Tecnologies:** Vue 3, Nuxt 3, Pinia, Tailwind CSS.
* **Funció:** És la cara visible del projecte. Gestiona la interacció de l'usuari, el renderitzat de la cartellera (amb SSR per a SEO) i manté l'estat global reactiu de les butaques a través de Pinia.

### 2. Backend Principal (API REST)
* **Tecnologies:** PHP, Laravel.
* **Funció:** És el cervell de la lògica de negoci. Gestiona el CRUD d'esdeveniments, valida les dades de forma estricta i protegeix la base de dades contra condicions de cursa (usant *Pessimistic Locking* durant la compra).

### 3. Servidor en Temps Real
* **Tecnologies:** Node.js, Socket.IO.
* **Funció:** Manté connexions persistents amb els clients. S'encarrega d'escoltar i emetre els canvis d'estat de les butaques (lliure, bloquejat, venut) i gestionar els temporitzadors d'expiració de reserves.

### 4. Base de Dades
* **Tecnologies:** MySQL.
* **Funció:** Emmagatzematge persistent de dades relacionals (esdeveniments, sessions, usuaris, entrades venudes).

### Interrelació
El **Frontend** fa peticions HTTP tradicionals a la **API de Laravel** per llegir o escriure dades definitives (com carregar pel·lícules o pagar una entrada). Simultàniament, manté una connexió constant via WebSocket amb el **Servidor Node.js** per enviar i rebre els bloquejos temporals de butaques. Quan Laravel confirma una venda definitiva a **MySQL**, el canvi es reflecteix globalment per a tots els usuaris connectats.