# Plan: Sistema de Ressenyes i Valoracions

## Fase 1: Backend (Laravel)

### 1. Model i Migració
- Crear el model `Review` i la migració de la taula `reviews`.
- Definir les relacions en els models:
  - `User` hasMany `Review`.
  - `Esdeveniment` hasMany `Review`.
  - `Review` belongsTo `User`, `Review` belongsTo `Esdeveniment`.

### 2. Controlador i Validació
- Crear `ReviewController` amb els mètodes `index`, `store`, `update`, `destroy`.
- Implementar validació:
  - `rating`: required, integer, min:1, max:5.
  - `comment`: nullable, string, max:500.
  - `esdeveniment_id`: existing id in `esdeveniments`.

### 3. Rutes i Seguretat
- Afegir rutes a `routes/api.php`.
- Protegir `store`, `update`, `destroy` amb el middleware `auth:sanctum`.
- Implementar una `Policy` per assegurar que només l'autor pot editar o esborrar la seva ressenya.

## Fase 2: Frontend (Nuxt 3)

### 1. Components UI
- **`ReviewList.vue`**: Mostrarà les ressenyes existents i la nota mitjana.
- **`ReviewForm.vue`**: Formulari per afegir o editar ressenyes (amb visualització d'estrelles).

### 2. Integració
- Modificar `pages/esdeveniment/[id].vue` per carregar les ressenyes i mostrar els nous components.
- Gestionar l'estat d'autenticació per mostrar/amagar el formulari.

### 3. Disseny
- Utilitzar icones de Lucide o similars per a les estrelles.
- Aplicar estils Tailwind coherents amb el disseny premium (glassmorphism, subtle shadows, accents grocs).

## Fase 3: Verificació
- Provar la creació d'una ressenya com a usuari autenticat.
- Verificar que un usuari no pot crear-ne dos per al mateix esdeveniment.
- Comprovar que els visitants poden veure-les però no editar-les.
