<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class UsersSeeder extends Seeder
{
    public function run(): void
    {
        DB::table('usuarios')->insert([
            [
                'id'             => 12,
                'nombre_completo' => 'admin1',
                'email'          => 'admin@gmail.com',
                'password'       => Hash::make('admin123'),
                'rol'            => 'admin',
                'avatar'         => null,
                'banner'         => null,
                'created_at'     => '2025-12-16 12:09:02',
                'updated_at'     => '2025-12-16 09:18:43',
            ],
            [
                'id'             => 13,
                'nombre_completo' => 'prueba1',
                'email'          => 'prueba1@gmail.com',
                'password'       => Hash::make('prueba123'),
                'rol'            => 'user',
                'avatar'         => null,
                'banner'         => null,
                'created_at'     => '2026-03-29 12:00:00',
                'updated_at'     => '2026-03-29 12:00:00',
            ],
            [
                'id'             => 14,
                'nombre_completo' => 'prueba2',
                'email'          => 'prueba2@gmail.com',
                'password'       => Hash::make('prueba123'),
                'rol'            => 'user',
                'avatar'         => null,
                'banner'         => null,
                'created_at'     => '2026-03-29 12:00:00',
                'updated_at'     => '2026-03-29 12:00:00',
            ],
            [
                'id'             => 15,
                'nombre_completo' => 'prueba3',
                'email'          => 'prueba3@gmail.com',
                'password'       => Hash::make('prueba123'),
                'rol'            => 'user',
                'avatar'         => null,
                'banner'         => null,
                'created_at'     => '2026-03-29 12:00:00',
                'updated_at'     => '2026-03-29 12:00:00',
            ],
        ]);
    }
}