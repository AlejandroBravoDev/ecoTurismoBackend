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
        Schema::create('hospedajes', function (Blueprint $table) {
            $table->id();
            $table->string('nombre', 150);
            $table->text('descripcion')->nullable();
            $table->string('coordenadas', 50)->nullable();
            $table->string('ubicacion', 255)->nullable();
            $table->unsignedBigInteger('municipio_id')->nullable();
            $table->text('hoteles_cercanos')->nullable();
            $table->text('comentarios')->nullable();
            $table->text('imagenes')->nullable();
            $table->text('recomendaciones')->nullable();
            $table->string('updated_at', 250)->nullable();
            $table->date('created_at')->nullable()->default(DB::raw('curdate()'));

            $table->foreign('municipio_id')->references('id')->on('municipios')->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('hospedajes');
    }
};
