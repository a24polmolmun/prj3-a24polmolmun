# transversals
Esquema mínim de carpetes pels projectes transversals

És obligatori seguir aquesta estructura tot i que la podeu ampliar.

## Atenció
Aquest fitxer conté l'explicació i manual d'instal·lació corresponent al projecte Cinema Pol.

# Aquest fitxer ha de contenir com a mínim:
* **Autor:** Pol Molina
* **Nom del projecte:** Cinema Pol
* **Petita descripció:** Plataforma web completa per a la reserva i compra d'entrades de cinema en temps real. Evita la sobrevenda mitjançant bloquejos de concurrència i WebSockets, i inclou un panell d'administració en viu.
* **Diagrames:**
  
Diagrama de Casos d'Ús

<img width="541" height="739" alt="image" src="https://github.com/user-attachments/assets/1d03b31a-6559-49a2-8a96-1bd77cb5d0a2" />

Diagrama Entitat-Relació (ER)

<img width="659" height="694" alt="image" src="https://github.com/user-attachments/assets/b507406c-e18c-46e3-8734-530a6e145ce0" />

Diagrama de Seqüència (Procés de Compra + Socket.IO)

<img width="1132" height="676" alt="image" src="https://github.com/user-attachments/assets/0f959141-d8fb-4c58-ac59-c0e60d9ffd18" />

* **Diagrama de Pantalles:** https://stitch.withgoogle.com/projects/6361264361055921812
* **URL de producció:** ["Pendent de desplegament"]

---

## Resum / Abstract
**Curs:** 2DAW 2025-2026  
**Integrants:** Pol Molina  

Cinema Pol és una plataforma web de venda d'entrades en temps real. El seu objectiu és oferir una experiència de compra fluida per als clients, evitant la sobrevenda mitjançant bloquejos de seients concurrents, i proporcionar als administradors un panell integral per gestionar pel·lícules, aforaments i analítiques d'ocupació en directe.

---

## Arquitectura del Sistema
Aquest projecte utilitza tecnologies modernes separades en microserveis per garantir escalabilitat i separar responsabilitats:

* **Frontend (Vue 3 / Nuxt 3):** És la cara visible de l'aplicació. Utilitza la Composition API i Pinia per centralitzar l'estat global i reactiu de les butaques.
* **Backend Principal (Laravel 11 / PHP):** És el cervell de la lògica de negoci. Gestiona el CRUD, valida dades de forma estricta i protegeix la base de dades contra condicions de cursa usant *Pessimistic Locking*.
* **Servidor en Temps Real (Node.js / Socket.IO):** Manté connexions persistents per emetre els canvis d'estat de les butaques i gestionar expiracions.
* **Base de Dades (MySQL):** Emmagatzematge relacional consistent.
* **Vídeo de Presentació:**  [ENLLAÇ AL VÍDEO]

---

## Manual d'Instal·lació i Configuració (Entorn Local)

A continuació s'explica com arrencar tot el sistema localment des de zero.

### Requisits previs
* **Docker i Docker Compose** (Per a la base de dades i l'API de Laravel).
* **Node.js** (v18 o superior).
* **Git**.

### Pas 1: Clonar el repositori
```bash
git clone [URL_DEL_TEU_REPOSITORI_GIT]
cd [nom-de-la-carpeta-del-repo]
```

### Pas 2: Configuració del Backend (Laravel API)
1. Accedeix a la carpeta del backend:
```bash
cd src/backend-api
```
2. Copia l'arxiu d'entorn:
```bash
cp .env.example .env
```
3. Aixeca els contenidors de Docker:
```bash
docker compose up -d
```
4. Instal·la les dependències de PHP i genera la clau:
```bash
docker exec prj-backend-api composer install
docker exec prj-backend-api php artisan key:generate
```
5. Executa les migracions i carrega les dades inicials (Seeders):
```bash
docker exec prj-backend-api php artisan migrate:fresh --seed
```

### Pas 3: Configuració del Servidor de Temps Real (WebSockets)
1. Obre un nou terminal i accedeix a la carpeta del servidor de sockets:
```bash
cd src/backend-ws
```
2. Instal·la les dependències:
```bash
npm install
```
3. Inicia el servidor (per defecte escoltarà al port 3001):
```bash
node server.js
```

### Pas 4: Configuració del Frontend (Vue / Nuxt 3)
1. Obre un nou terminal i accedeix a la carpeta del frontend:
```bash
cd src/frontend
```
2. Copia l'arxiu d'entorn i assegura't que apunta a l'API (localhost:8000) i als Sockets (localhost:3001):
```bash
cp .env.example .env
```
3. Instal·la les dependències:
```bash
npm install
```
4. Arrenca el servidor de desenvolupament:
```bash
npm run dev
```

## 🚀 Accés a l'aplicació
Un cop tot estigui en funcionament, pots accedir a les següents rutes al teu navegador:
* **Aplicació Client / Web:** `http://localhost:3000`
* **Panell d'Administració:** `http://localhost:3000/admin`
* **API REST (Laravel):** `http://localhost:8000/api`
