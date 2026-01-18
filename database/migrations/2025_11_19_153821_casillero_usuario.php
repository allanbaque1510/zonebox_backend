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
        Schema::create('casilleros', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('id_usuario');
            $table->unsignedBigInteger('id_ciudad');
            $table->string('codigo')->unique();
            $table->string('direccion');
            $table->string('estado');
            $table->string('zip');
            $table->string('telefono');
            $table->boolean('activo')->default(true);
            $table->timestamps();

            $table->foreign('id_usuario')->references('id')->on('users');
            $table->foreign('id_ciudad')->references('id')->on('ciudades');
        });

            
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('casilleros');
 
    }
};
