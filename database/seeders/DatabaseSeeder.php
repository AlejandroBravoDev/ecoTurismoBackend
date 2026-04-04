<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->call([
            MunicipiosSeeder::class,
            HospedajesSeeder::class,
            LugaresSeeder::class,
            UsersSeeder::class,
        ]);
    }
}