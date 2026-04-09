<?php

namespace App\Http\Controllers;

use App\Models\Reserva;
use App\Models\Seient;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class ReservaController extends Controller
{
    /**
     * Processa la confirmació de la reserva d'entrades.
     */
    public function confirmarCompra(Request $request)
    {
        $request->validate([
            'esdeveniment_id' => 'required|exists:esdeveniments,id',
            'entrades' => 'required|array|min:1|max:10',
            'entrades.*.seient_id' => 'required|exists:seients,id',
            'entrades.*.tipus_id' => 'required|exists:tipus_entrades,id',
            'nom' => 'required|string',
            'email' => 'required|email'
        ], [
            'entrades.max' => 'No pots comprar més de 10 butaques de cop.',
            'entrades.required' => 'Has de seleccionar almenys una butaca.'
        ]);

        $entrades = $request->entrades;
        $seientsIds = array_column($entrades, 'seient_id');
        $eventId = $request->esdeveniment_id;

        try {
            $localitzador = strtoupper(str()->random(6));

            DB::transaction(function () use ($request, $entrades, $eventId, $localitzador, $seientsIds) {
                // 1. Bloquejar els seients a la BD per evitar que cap altra petició els llegeixi/escrigui simultàniament
                $seients = Seient::whereIn('id', $seientsIds)->lockForUpdate()->get();

                // 2. Validar que tots els seients existeixen i estan realment disponibles
                if ($seients->count() !== count($seientsIds)) {
                    throw new \Exception('Algunes de les butaques seleccionades ja no existeixen.');
                }

                foreach ($seients as $seient) {
                    if ($seient->estat !== 'disponible') {
                        throw new \Exception('La butaca ' . $seient->fila . $seient->numero . ' ja ha estat reservada per un altre usuari.');
                    }
                }

                // 3. Buscar o crear usuari pel seu email
                $usuari = User::firstOrCreate(
                ['email' => $request->email],
                [
                    'name' => $request->nom,
                    'password' => bcrypt(str()->random(16))
                ]
                );

                // 4. Per a cada seient, creem la reserva i actualitzem el seu estat
                foreach ($entrades as $entrada) {
                    Reserva::create([
                        'usuari_id' => $usuari->id,
                        'seient_id' => $entrada['seient_id'],
                        'tipus_entrada_id' => $entrada['tipus_id'],
                        'localitzador' => $localitzador,
                        'estat' => 'confirmada',
                        'data_expiracio' => now()->addYear()
                    ]);

                    // Actualitzem l'estat del seient a 'venut' (dins del lock)
                    Seient::where('id', $entrada['seient_id'])->update(['estat' => 'venut']);
                }
            });

            // Notificació al servidor WebSocket (Node.js) via HTTP POST
            try {
                // Utilitzem el nom del servei de docker-compose per a la comunicació interna
                Http::timeout(2)->post('http://prj-backend-ws:4000/api/broadcast-sold', [
                    'eventId' => $eventId,
                    'seatIds' => $seientsIds
                ]);
            }
            catch (\Exception $e) {
                // Registrem l'error però no fallem la resposta al client, ja que la BD s'ha actualitzat
                Log::error('Error notificant venda a WebSocket: ' . $e->getMessage());
            }

            return response()->json([
                'success' => true,
                'message' => 'Compra realitzada amb èxit',
                'localitzador' => $localitzador
            ]);

        }
        catch (\Exception $e) {
            Log::error('Error en el procés de compra: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => $e->getMessage() ?: 'S\'ha produït un error inesperat durant la compra'
            ], 409); // Usem 409 Conflict per a Race Conditions
        }
    }

    /**
     * Consulta les reserves d'un usuari per email.
     */
    public function getReservesByEmail(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'localitzador' => 'required|string|size:6'
        ]);

        $email = $request->query('email');
        $localitzador = strtoupper($request->query('localitzador'));

        $usuari = User::where('email', $email)->first();

        if (!$usuari) {
            return response()->json([], 200);
        }

        $reserves = Reserva::where('usuari_id', $usuari->id)
            ->where('localitzador', $localitzador)
            ->with(['seient.sessio.esdeveniment'])
            ->orderBy('created_at', 'desc')
            ->get();

        return response()->json($reserves);
    }
}