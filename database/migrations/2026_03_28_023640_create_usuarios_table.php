<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('usuarios', function (Blueprint $table) {
            $table->id();
            $table->string('nombre_completo', 150);
            $table->string('email', 100)->unique();
            $table->string('password', 255);
            $table->enum('rol', ['admin', 'user'])->default('user');
            $table->string('avatar', 255)->nullable();
            $table->timestamps();
            $table->string('banner', 255)->nullable();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('usuarios');
    }
};