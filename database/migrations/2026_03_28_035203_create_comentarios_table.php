<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('resenas', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('lugar_id')->nullable();
            $table->unsignedBigInteger('usuario_id');
            $table->text('contenido');
            $table->tinyInteger('rating')->unsigned()->nullable();
            $table->string('image_path', 255)->nullable();
            $table->string('category', 50)->nullable();
            $table->timestamps();
            $table->unsignedBigInteger('hospedaje_id')->nullable();

            $table->foreign('lugar_id')->references('id')->on('lugares')->nullOnDelete();
            $table->foreign('usuario_id')->references('id')->on('usuarios')->cascadeOnDelete();
            $table->foreign('hospedaje_id')->references('id')->on('hospedajes')->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('comentarios');
    }
};
