<?php

namespace App\Http\Controllers;

use App\Models\Esdeveniment;
use Illuminate\Http\Request;

class EsdevenimentController extends Controller
{
    /**
     * Retorna tots els esdeveniments amb els seus tipus d'entrades.
     */
    public function index()
    {
        $esdeveniments = Esdeveniment::with(['tipusEntrades', 'sessions'])->get();

        return response()->json([
            'success' => true,
            'data' => $esdeveniments
        ]);
    }

    /**
     * Retorna un esdeveniment específic amb els seus seients i tipus d'entrades.
     */
    public function show(Request $request, $id)
    {
        $hora = $request->query('hora');

        // Busquem l'esdeveniment i la sessió específica
        $esdeveniment = Esdeveniment::with(['sessions', 'tipusEntrades'])->find($id);

        if (!$esdeveniment) {
            return response()->json([
                'success' => false,
                'message' => 'Esdeveniment no trobat'
            ], 404);
        }

        // Si es demana una hora, només retornem els seients d'aquella sessió
        if ($hora) {
            $sessio = \App\Models\Sessio::where('esdeveniment_id', $id)
                ->where('hora', $hora)
                ->first();

            if (!$sessio) {
                return response()->json([
                    'success' => false,
                    'message' => 'Sessió no trobada per a aquesta hora'
                ], 404);
            }

            $seients = \App\Models\Seient::where('sessio_id', $sessio->id)->get();
            $esdeveniment->setRelation('seients', $seients);
        }
        else {
            // Si no hi ha hora, retornem l'esdeveniment sense seients o amb tots (opcional)
            // En aquest flux, el frontend sempre hauria de demanar hora.
            $esdeveniment->setRelation('seients', collect());
        }

        return response()->json([
            'success' => true,
            'data' => $esdeveniment
        ]);
    }
}