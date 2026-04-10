# Log de Prompts - Sistema de Ressenyes (SDD)

## Fase de Disseny (opsx:propose)
* **Data:** 10/04/2026
* **Acció:** Sol·licitud d'especificació completa per al sistema de valoracions.
* **Prompt:** "Vull implementar una nova funcionalitat de Sistema de Ressenyes i Valoracions per al projecte Cinema Pol utilitzant la metodologia OpenSpec. Genera una proposta (opsx:propose) creant foundations.md, spec.md i plan.md."
* **Resultat:** OK. La IA ha generat una arquitectura sòlida amb model, controlador, rutes i components de Nuxt.
* **Decisió:** Accepto la proposta sense canvis per procedir a la Implementació immediata.

## Fase d'Implementació Backend (opsx:apply - Part 1)
* **Data:** 10/04/2026
* **Acció:** Generació de codi Laravel (Model, Migració i Controlador).
* **Prompt:** "D'acord, accepto la proposta de l'especificació. Som-hi amb l'opsx:apply de la Fase 1: Backend (Laravel)."
* **Resultat:** OK. S'ha creat la migració de la taula `reviews`, el model `Review` amb relacions, el `ReviewController` (index/store) i les rutes d'API. S'ha executat la migració correctament al contenidor `prj-backend-api`.

## Fase d'Implementació Frontend (opsx:apply - Part 2)
* **Data:** 10/04/2026
* **Acció:** Implementació de components Nuxt 3 i integració.
* **Prompt:** "Backend verificat. Som-hi amb la Fase 2: Frontend (Nuxt 3) de l'OpenSpec."
* **Resultat:** OK. S'ha creat el Pinia `reviewStore.ts`, els components `ReviewList.vue` i `ReviewForm.vue`, i s'ha realitzat la integració a la pàgina de detall de l'esdeveniment.

## Hotfix: Reversió d'Autenticació
* **Data:** 10/04/2026
* **Acció:** Eliminació de requeriments de Sanctum per a ressenyes.
* **Prompt:** "Antigravity, tenim un problema de coherència: l'especificació demana autenticació (Sanctum), però el meu projecte actual encara no té sistema de login d'usuaris. Fes un hotfix immediat."
* **Resultat:** OK. S'ha fet el camp `user_id` nullable a la BD, s'han obert les rutes a `api.php` sense middleware i s'ha actualitzat el controlador per acceptar submissions anònimes (assignant l'usuari 1 o null). El formulari és ara totalment públic.

## Millora de UX i correcció de restriccions de base de dades
* **Data:** 10/04/2026
* **Acció:** Eliminació de UNIQUE constraint i implementació de camp `nom_usuari`.
* **Prompt:** "Antigravity, tenim un error de base de dades i un requeriment de disseny nou: Elimina la restricció UNIQUE, afegeix un camp nom_usuari, modifica ReviewController, ReviewForm i ReviewList."
* **Resultat:** Èxit. S'ha eliminat la restricció UNIQUE(user_id, esdeveniment_id) que impedia múltiples ressenyes anònimes. S'ha afegit el camp `nom_usuari` per personalitzar les valoracions públiques. Ara el formulari obliga a introduir un nom abans de publicar.