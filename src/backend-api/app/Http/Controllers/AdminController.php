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
        $esdeveniments = Esdeveniment::withCount('sessions')->with(['tipusEntrades', 'sessions'])->get();
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
            'imatge' => 'nullable|image|max:2048', // Màxim 2MB
            'aforament_total' => 'required|integer|min:20|max:120',
            'sessions' => 'required|array',
            'sessions.*.dia' => 'required|date',
            'sessions.*.hora' => 'required|string',
            'preus' => 'required|array',
            'preus.*.nom' => 'required|string',
            'preus.*.preu' => 'required|numeric|min:0'
        ]);

        try {
            return DB::transaction(function () use ($request) {
                $data = $request->only([
                    'nom', 'data_hora', 'recinte', 'descripcio', 'aforament_total'
                ]);

                // Gestionar la pujada de la imatge
                if ($request->hasFile('imatge')) {
                    $path = $request->file('imatge')->store('esdeveniments', 'public');
                    $data['imatge'] = '/storage/' . $path;
                }

                // 1. Crear l'esdeveniment
                $esdeveniment = Esdeveniment::create($data);

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
        $esdeveniment->delete(); // Això esborrarà sessions i butaques en cascada per les migracions

        return response()->json([
            'success' => true,
            'message' => 'Esdeveniment eliminat correctament'
        ]);
    }

    /**
     * Actualitza un esdeveniment existent.
     */
    public function update(Request $request, $id)
    {
        // Nota: Si s'envia multipart/form-data, PHP sovint no pobla $_FILES en rutes PUT.
        // Recomanem fer anar POST amb _method=PUT des del frontend.
        $request->validate([
            'nom' => 'required|string',
            'data_hora' => 'required|date',
            'recinte' => 'required|string',
            'descripcio' => 'nullable|string',
            'imatge' => 'nullable', // Pot ser fitxer o string (si no canvia)
            'aforament_total' => 'required|integer|min:20|max:120',
            'sessions' => 'required|array',
            'sessions.*.id' => 'nullable|integer',
            'sessions.*.dia' => 'required|date',
            'sessions.*.hora' => 'required|string',
            'preus' => 'required|array',
            'preus.*.nom' => 'required|string',
            'preus.*.preu' => 'required|numeric|min:0'
        ]);

        $esdeveniment = Esdeveniment::findOrFail($id);

        try {
            return DB::transaction(function () use ($request, $esdeveniment) {
                $data = $request->only([
                    'nom', 'data_hora', 'recinte', 'descripcio', 'aforament_total'
                ]);

                // Gestionar la pujada de la imatge si n'hi ha una de nova
                if ($request->hasFile('imatge')) {
                    $path = $request->file('imatge')->store('esdeveniments', 'public');
                    $data['imatge'] = '/storage/' . $path;
                }

                // 1. Actualitzar dades bàsiques
                $esdeveniment->update($data);

                // 2. Sincronitzar Tipus d'Entrades (Esborrar i recrear com demana l'usuari)
                $esdeveniment->tipusEntrades()->delete();
                foreach ($request->preus as $pData) {
                    $esdeveniment->tipusEntrades()->create([
                        'nom' => $pData['nom'],
                        'preu' => $pData['preu']
                    ]);
                }

                // 3. Sincronitzar Sessions
                $inputSessions = $request->sessions;
                $existingSessions = $esdeveniment->sessions;

                // Sessions a mantenir/actualitzar
                foreach ($inputSessions as $sData) {
                    if (isset($sData['id']) && $sData['id']) {
                        $sessio = Sessio::find($sData['id']);
                        if ($sessio && $sessio->esdeveniment_id == $esdeveniment->id) {
                            $sessio->update([
                                'dia' => $sData['dia'],
                                'hora' => $sData['hora']
                            ]);
                            // SINCRONITZAR SEIENTS (Per si ha canviat l'aforament)
                            $this->sincronitzarSeientsPerSessio($sessio, $esdeveniment->aforament_total);
                        }
                    }
                    else {
                        // Nova sessió
                        $novaSessio = $esdeveniment->sessions()->create([
                            'dia' => $sData['dia'],
                            'hora' => $sData['hora']
                        ]);
                        $this->generarSeientsPerSessio($novaSessio, $esdeveniment->aforament_total);
                    }
                }

                // Sessions a eliminar (les que no estan a l'input)
                $inputSessionIds = collect($inputSessions)->pluck('id')->filter()->toArray();
                foreach ($existingSessions as $existingSessio) {
                    if (!in_array($existingSessio->id, $inputSessionIds)) {
                        // Comprovar si té reserves
                        $teReserves = Seient::where('sessio_id', $existingSessio->id)
                            ->whereHas('reserves')
                            ->exists();

                        if (!$teReserves) {
                            $existingSessio->delete();
                        }
                    }
                }

                return response()->json([
                    'success' => true,
                    'message' => 'Esdeveniment actualitzat correctament',
                    'data' => $esdeveniment->load(['sessions', 'tipusEntrades'])
                ]);
            });
        }
        catch (\Exception $e) {
            Log::error('Error actualitzant esdeveniment: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error en actualitzar l\'esdeveniment: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Genera les butaques per a una sessió concreta basat en l'aforament.
     */
    private function generarSeientsPerSessio(Sessio $sessio, int $total)
    {
        $butaquesPerFila = 12;
        $comptador = 0;
        $numFiles = ceil($total / $butaquesPerFila);

        for ($i = 0; $i < $numFiles; $i++) {
            $filaLabel = $this->getFilaLabel($i);

            for ($num = 1; $num <= $butaquesPerFila; $num++) {
                if ($comptador >= $total)
                    break;

                Seient::create([
                    'sessio_id' => $sessio->id,
                    'fila' => $filaLabel,
                    'numero' => $num,
                    'estat' => 'disponible'
                ]);
                $comptador++;
            }
        }
    }

    /**
     * Sincronitza els seients d'una sessió existent amb el nou aforament.
     */
    private function sincronitzarSeientsPerSessio(Sessio $sessio, int $nouTotal)
    {
        $seientsActuals = $sessio->seients()->count();

        if ($seientsActuals === $nouTotal) {
            return;
        }

        if ($seientsActuals < $nouTotal) {
            // Augmentar aforament: Afegir seients faltants
            $perAfegir = $nouTotal - $seientsActuals;
            $butaquesPerFila = 12;

            for ($i = $seientsActuals; $i < $nouTotal; $i++) {
                $filaIndex = floor($i / $butaquesPerFila);
                $num = ($i % $butaquesPerFila) + 1;
                $filaLabel = $this->getFilaLabel($filaIndex);

                Seient::create([
                    'sessio_id' => $sessio->id,
                    'fila' => $filaLabel,
                    'numero' => $num,
                    'estat' => 'disponible'
                ]);
            }
        }
        else {
            // Disminuir aforament: Eliminar els últims seients
            $perEliminar = $seientsActuals - $nouTotal;

            // Obtenir els últims N seients (per ordre de fila desc i numero desc)
            $seientsAValidar = $sessio->seients()
                ->orderBy('fila', 'desc')
                ->orderBy('numero', 'desc')
                ->take($perEliminar)
                ->get();

            // Comprovar si algun té reserves
            foreach ($seientsAValidar as $seient) {
                if ($seient->reserves()->exists()) {
                    throw new \Exception("No es pot reduir l'aforament de la sessió {$sessio->hora}: hi ha butaques reservades que s'haurien d'eliminar.");
                }
            }

            // Si tot és correcte, eliminar
            foreach ($seientsAValidar as $seient) {
                $seient->delete();
            }
        }
    }

    /**
     * Helper per obtenir l'etiqueta de la fila (A, B... Z, AA, AB...).
     */
    private function getFilaLabel(int $index)
    {
        $alphabet = "ABCDEFGHIJKLMNOPQRSTUVWXYZ";
        if ($index < 26)
            return $alphabet[$index];
        return $alphabet[floor($index / 26) - 1] . $alphabet[$index % 26];
    }
}