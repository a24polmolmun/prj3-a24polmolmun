# Foundations: Sistema de Ressenyes i Valoracions

## Context
El projecte **Cinema Pol** ja disposa d'un sistema de reserva d'entrades per a esdeveniments cinematogràfics. Per millorar el "engagement" i el feedback de la comunitat, es vol implementar un sistema on els usuaris puguin valorar la seva experiència i deixar comentaris sobre les pel·lícules/esdeveniments.

## Usuaris i Rols
- **Usuari Autenticat**: Pot escriure, editar i esborrar les seves pròpies ressenyes.
- **Usuari Visitant**: Pot visualitzar les ressenyes i la nota mitjana de cada esdeveniment.
- **Administrador**: (Futur) Podrà moderar o eliminar ressenyes inapropiades.

## Restriccions i Decisions de Disseny
1. **Autenticació**: S'utilitzarà el sistema existent basat en Laravel Sanctum.
2. **Valoració**: Escala de 1 a 5 estrelles (números enters).
3. **Unicitat**: Un usuari només pot deixar una ressenya per esdeveniment (evitar spam).
4. **Relacions**: Cada ressenya està vinculada a un `User` i a un `Esdeveniment`.
5. **Estètica**: S'ha de mantenir el disseny "premium" del projecte, integrant-lo amb el nou estil hídrid (dark headers + white backgrounds).

## Estructura de Dades Proposada
### Taula `reviews`
- `id`: PK
- `user_id`: FK -> `users.id`
- `esdeveniment_id`: FK -> `esdeveniments.id`
- `rating`: TinyInteger (unsigned, 1-5)
- `comment`: Text (opcional)
- `timestamps`
