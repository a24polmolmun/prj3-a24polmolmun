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
            'seients_ids' => 'required|array',
            'nom' => 'required|string',
            'email' => 'required|email'
        ]);

        $seientsIds = $request->seients_ids;
        $eventId = $request->esdeveniment_id;

        // Comprovar si tots els seients estan realment disponibles a la base de dades
        $seientsDisponibles = Seient::whereIn('id', $seientsIds)
            ->where('esdeveniment_id', $eventId)
            ->where('estat', 'disponible')
            ->count();

        if ($seientsDisponibles !== count($seientsIds)) {
            return response()->json([
                'success' => false,
                'message' => 'Alguns seients ja no estan disponibles o ja han estat venuts'
            ], 422);
        }

        try {
            DB::transaction(function () use ($request, $seientsIds, $eventId) {
                // Buscar o crear usuari pel seu email
                $usuari = User::firstOrCreate(
                ['email' => $request->email],
                [
                    'name' => $request->nom,
                    'password' => bcrypt(str()->random(16))
                ]
                );

                // Per a cada seient, creem la reserva i actualitzem el seu estat
                foreach ($seientsIds as $seientId) {
                    Reserva::create([
                        'usuari_id' => $usuari->id,
                        'seient_id' => $seientId,
                        'estat' => 'confirmada',
                        'data_expiracio' => now()->addYear()
                    ]);

                    // Actualitzem l'estat del seient a 'venut'
                    Seient::where('id', $seientId)->update(['estat' => 'venut']);
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
                'message' => 'Compra realitzada amb èxit'
            ]);

        }
        catch (\Exception $e) {
            Log::error('Error en el procés de compra: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'S\'ha produït un error inesperat durant la compra'
            ], 500);
        }
    }
}