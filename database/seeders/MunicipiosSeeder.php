<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class MunicipiosSeeder extends Seeder
{
    public function run(): void
    {
        DB::table('municipios')->insert([
            ['id' => 1, 'nombre' => 'Pereira',      'clima' => 'Templado', 'lugares' => null, 'hospedajes' => null],
            ['id' => 2, 'nombre' => 'Dosquebradas', 'clima' => 'Templado', 'lugares' => null, 'hospedajes' => null],
            ['id' => 3, 'nombre' => 'Santa Rosa',   'clima' => 'Frío',     'lugares' => null, 'hospedajes' => null],
            ['id' => 4, 'nombre' => 'Quinchía',     'clima' => 'Templado', 'lugares' => null, 'hospedajes' => null],
            ['id' => 5, 'nombre' => 'Marsella',     'clima' => 'Frío',     'lugares' => null, 'hospedajes' => null],
            ['id' => 6, 'nombre' => 'Mistrato',     'clima' => 'Templado', 'lugares' => null, 'hospedajes' => null],
            ['id' => 7, 'nombre' => 'Balboa',       'clima' => 'Templado', 'lugares' => null, 'hospedajes' => null],
            ['id' => 8, 'nombre' => 'Guatica',      'clima' => 'Templado', 'lugares' => null, 'hospedajes' => null],
        ]);
    }
}