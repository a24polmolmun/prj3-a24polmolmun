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
        $esdeveniments = Esdeveniment::with('tipusEntrades')->get();

        return response()->json([
            'success' => true,
            'data' => $esdeveniments
        ]);
    }

    /**
     * Retorna un esdeveniment específic amb els seus seients i tipus d'entrades.
     */
    public function show($id)
    {
        $esdeveniment = Esdeveniment::with(['tipusEntrades', 'seients'])->find($id);

        if (!$esdeveniment) {
            return response()->json([
                'success' => false,
                'message' => 'Esdeveniment no trobat'
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => $esdeveniment
        ]);
    }
}