<?php

namespace App\Http\Controllers;

use App\Models\Esdeveniment;
use App\Models\Sessio;
use App\Models\TipusEntrada;
use App\Models\Seient;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class AdminController extends Controller
{
    /**
     * Llista tots els esdeveniments per a l'administrador.
     */
    public function index()
    {
        $esdeveniments = Esdeveniment::withCount('sessions')->with('tipusEntrades')->get();
        return response()->json([
            'success' => true,
            'data' => $esdeveniments
        ]);
    }

    /**
     * Crea un nou esdeveniment amb les seves sessions i preus.
     */
    public function store(Request $request)
    {
        $request->validate([
            'nom' => 'required|string',
            'data_hora' => 'required|date',
            'recinte' => 'required|string',
            'descripcio' => 'nullable|string',
            'imatge' => 'nullable|string',
            'aforament_total' => 'required|integer|min:1',
            'sessions' => 'required|array',
            'sessions.*.dia' => 'required|date',
            'sessions.*.hora' => 'required|string',
            'preus' => 'required|array',
            'preus.*.nom' => 'required|string',
            'preus.*.preu' => 'required|numeric|min:0'
        ]);

        try {
            return DB::transaction(function () use ($request) {
                // 1. Crear l'esdeveniment
                $esdeveniment = Esdeveniment::create($request->only([
                    'nom', 'data_hora', 'recinte', 'descripcio', 'imatge', 'aforament_total'
                ]));

                // 2. Crear les sessions i les butaques per a cada sessió
                foreach ($request->sessions as $sData) {
                    $sessio = $esdeveniment->sessions()->create([
                        'dia' => $sData['dia'],
                        'hora' => $sData['hora']
                    ]);

                    // Generar butaques per a aquesta sessió (ex: 8 files de 12 butaques per defecte)
                    $this->generarSeientsPerSessio($sessio, $request->aforament_total);
                }

                // 3. Crear els tipus d'entrades (preus)
                foreach ($request->preus as $pData) {
                    $esdeveniment->tipusEntrades()->create([
                        'nom' => $pData['nom'],
                        'preu' => $pData['preu']
                    ]);
                }

                return response()->json([
                    'success' => true,
                    'message' => 'Esdeveniment creat correctament',
                    'data' => $esdeveniment->load(['sessions', 'tipusEntrades'])
                ], 201);
            });
        }
        catch (\Exception $e) {
            Log::error('Error creant esdeveniment: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error en crear l\'esdeveniment: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Obté un esdeveniment per editar.
     */
    public function show($id)
    {
        $esdeveniment = Esdeveniment::with(['sessions', 'tipusEntrades'])->findOrFail($id);
        return response()->json([
            'success' => true,
            'data' => $esdeveniment
        ]);
    }

    /**
     * Elimina un esdeveniment.
     */
    public function destroy($id)
    {
        $esdeveniment = Esdeveniment::findOrFail($id);
        $esdeveniment . delete(); // Això esborrarà sessions i butaques en cascada per les migracions

        return response()->json([
            'success' => true,
            'message' => 'Esdeveniment eliminat correctament'
        ]);
    }

    /**
     * Genera les butaques per a una sessió concreta basat en l'aforament.
     */
    private function generarSeientsPerSessio(Sessio $sessio, int $total)
    {
        $files = ['A', 'B', 'C', 'D', 'E', 'F', 'G', 'H'];
        $butaquesPerFila = 12;
        $comptador = 0;

        foreach ($files as $fila) {
            for ($num = 1; $num <= $butaquesPerFila; $num++) {
                if ($comptador >= $total)
                    break;

                Seient::create([
                    'sessio_id' => $sessio->id,
                    'fila' => $fila,
                    'numero' => $num,
                    'estat' => 'disponible'
                ]);
                $comptador++;
            }
            if ($comptador >= $total)
                break;
        }
    }
}