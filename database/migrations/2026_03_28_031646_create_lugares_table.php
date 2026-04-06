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
        Schema::create('lugares', function (Blueprint $table) {
            $table->id();
            $table->string('nombre', 150);
            $table->text('descripcion')->nullable();
            $table->string('coordenadas', 133)->nullable();
            $table->string('ubicacion', 255)->nullable();
            $table->unsignedBigInteger('municipio_id')->nullable();
            $table->text('hoteles_cercanos')->nullable();
            $table->text('comentarios')->nullable();
            $table->text('imagenes')->nullable();
            $table->text('recomendaciones')->nullable();
            $table->timestamps();

            $table->foreign('municipio_id')->references('id')->on('municipios')->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('lugares');
    }
};
