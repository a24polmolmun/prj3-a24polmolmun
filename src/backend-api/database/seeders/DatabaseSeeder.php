<?php

namespace Database\Seeders;

use App\Models\Esdeveniment;
use App\Models\Seient;
use App\Models\TipusEntrada;
use App\Models\User;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Crear un esdeveniment de prova
        $esdeveniment = Esdeveniment::create([
            'nom' => 'Inception',
            'data_hora' => now()->addDays(7),
            'recinte' => 'Sala Principal',
            'descripcio' => 'Un lladre que roba secrets corporatius a través de l\'ús de la tecnologia d\'intercanvi de somnis.',
        ]);

        // Crear tipus d'entrades per a l'esdeveniment
        TipusEntrada::create([
            'esdeveniment_id' => $esdeveniment->id,
            'nom' => 'Jove',
            'preu' => 5.00,
        ]);
        TipusEntrada::create([
            'esdeveniment_id' => $esdeveniment->id,
            'nom' => 'Adult',
            'preu' => 12.00,
        ]);
        TipusEntrada::create([
            'esdeveniment_id' => $esdeveniment->id,
            'nom' => 'Reduïda',
            'preu' => 8.00,
        ]);

        // Generar sessions per a l'esdeveniment
        $hores = ['17:00', '19:30', '22:00'];
        foreach ($hores as $hora) {
            $sessio = \App\Models\Sessio::create([
                'esdeveniment_id' => $esdeveniment->id,
                'hora' => $hora,
                'dia' => $esdeveniment->data_hora->format('Y-m-d'),
            ]);

            // Generar butaques (Seients) per a aquesta sessió
            $files = ['A', 'B', 'C', 'D', 'E', 'F', 'G', 'H'];
            $numSeientsPerFila = 12;

            foreach ($files as $fila) {
                for ($i = 1; $i <= $numSeientsPerFila; $i++) {
                    Seient::create([
                        'sessio_id' => $sessio->id,
                        'fila' => $fila,
                        'numero' => $i,
                        'estat' => 'disponible',
                    ]);
                }
            }
        }

        echo "Base de dades poblada amb èxit per l'esdeveniment ID: " . $esdeveniment->id . " amb 3 sessions.\n";
    }
}