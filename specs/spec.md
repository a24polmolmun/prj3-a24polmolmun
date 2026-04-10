# Specification: Sistema de Ressenyes i Valoracions

## User Experience (UX)

### 1. Visualització de Ressenyes (Públic)
- A la pàgina de detall de l'esdeveniment, sota la secció de reserva, apareixerà una nova secció "Ressenyes de la Comunitat".
- Es mostrarà la nota mitjana (ex: 4.5/5) i el nombre total de valoracions.
- Llista de comentaris amb el nom de l'usuari, la nota (estrelles) i la data.

### 2. Afegir una Ressenya (Autenticat)
- Si l'usuari està autenticat, veurà un botó "Escriure Ressenya" o un formulari directament si encara no n'ha deixat cap.
- Selecció de 1 a 5 estrelles mitjançant icones interactives.
- Àrea de text per al comentari (opcional, màxim 500 caràcters).

### 3. Gestió de Ressenyes Pròpies
- L'usuari veurà un indicador de que ja ha valorat l'esdeveniment.
- Opció d'editar o esborrar la seva ressenya existent.

## API Endpoints (Backend)

| Mètode | Endpoint | Descripció | Auth |
|---|---|---|---|
| GET | `/api/esdeveniments/{id}/reviews` | Llista les ressenyes i nota mitjana. | No |
| POST | `/api/reviews` | Crea una nova ressenya. | Sí |
| PUT | `/api/reviews/{id}` | Actualitza una ressenya existent. | Sí (Owner) |
| DELETE | `/api/reviews/{id}` | Esborra una ressenya. | Sí (Owner) |

## Regles de Negoci
- Un usuari no pot valorar el mateix esdeveniment més d'una vegada.
- El comentari és opcional, però el rating és obligatori.
- Les ressenyes s'ordenen per data de creació (més recents primer).
