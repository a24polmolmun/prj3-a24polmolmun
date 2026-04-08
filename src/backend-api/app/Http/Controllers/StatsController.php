<?php

namespace App\Http\Controllers;

use App\Models\Reserva;
use App\Models\Esdeveniment;
use App\Models\Seient;
use App\Models\TipusEntrada;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class StatsController extends Controller
{
    /**
     * Retorna les estadístiques globals per al panell d'administració.
     */
    public function index()
    {
        // 1. Recaptació total i desglossada per tipus d'entrada
        $recaptacio = DB::table('reserves')
            ->join('tipus_entrades', 'reserves.tipus_entrada_id', '=', 'tipus_entrades.id')
            ->where('reserves.estat', 'confirmada')
            ->select('tipus_entrades.nom', DB::raw('SUM(tipus_entrades.preu) as total'))
            ->groupBy('tipus_entrades.nom')
            ->get();

        // 2. Ocupació mitjana
        $totalSeients = Seient::count();
        $seientsVenuts = Seient::where('estat', 'venut')->count();
        $ocupacio = $totalSeients > 0 ? ($seientsVenuts / $totalSeients) * 100 : 0;

        // 3. Reserves actives vs confirmades
        $reservesActives = Reserva::where('estat', 'confirmada')->count();

        // 4. Evolució de vendes (últims 7 dies)
        $evolucioVendes = Reserva::where('estat', 'confirmada')
            ->where('created_at', '>=', now()->subDays(7))
            ->select(DB::raw('DATE(created_at) as data'), DB::raw('count(*) as total'))
            ->groupBy('data')
            ->orderBy('data')
            ->get();

        return response()->json([
            'success' => true,
            'data' => [
                'recaptacio' => $recaptacio,
                'ocupacio' => round($ocupacio, 2),
                'reserves_actives' => $reservesActives,
                'evolucio' => $evolucioVendes
            ]
        ]);
    }
}