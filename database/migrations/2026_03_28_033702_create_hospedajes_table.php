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
            $table->string('ubicacion', 255)->nullable();
            $table->text('descripcion')->nullable();
            $table->text('comentarios')->nullable();
            $table->text('imagenes')->nullable();
            $table->unsignedBigInteger('municipio_id')->nullable();
            $table->text('lugares')->nullable();
            $table->string('coordenadas', 133)->nullable();
            $table->string('updated_at', 250)->nullable();
            $table->datetime('created_at')->nullable()->default(DB::raw('current_timestamp()'));

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
