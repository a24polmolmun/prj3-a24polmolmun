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
        // 1. Recaptació total desglossada per tipus (Fem un join per obtenir el preu actual del tipus)
        // Nota: Si el tipus s'ha esborrat, usem un LEFT JOIN per no perdre la reserva del recompte
        $recaptacio = DB::table('reserves')
            ->leftJoin('tipus_entrades', 'reserves.tipus_entrada_id', '=', 'tipus_entrades.id')
            ->where('reserves.estat', 'confirmada')
            ->select(
            DB::raw('COALESCE(tipus_entrades.nom, "Altres") as nom'),
            DB::raw('SUM(COALESCE(tipus_entrades.preu, 0)) as total')
        )
            ->groupBy('nom')
            ->get();

        // 2. Ocupació mitjana del cinema
        $totalSeients = Seient::count();
        $seientsVenuts = Seient::where('estat', 'venut')->count();
        $ocupacio = $totalSeients > 0 ? ($seientsVenuts / $totalSeients) * 100 : 0;

        // 3. Recompte de reserves confirmades
        $reservesConfirmades = Reserva::where('reserves.estat', 'confirmada')->count();

        // 4. Evolució de vendes dels últims 7 dies
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
                'reserves_actives' => $reservesConfirmades,
                'evolucio' => $evolucioVendes
            ]
        ]);
    }
}