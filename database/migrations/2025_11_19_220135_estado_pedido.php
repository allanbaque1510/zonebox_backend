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
        Schema::create('estados', function (Blueprint $table) {
            $table->id();
            $table->string('codigo')->unique();
            $table->string('nombre');
            $table->integer('orden');
            $table->string('descripcion')->nullable();
            $table->string('color')->nullable();
            $table->boolean('activo')->default(true);
            $table->timestamps();
        });
              
        Schema::create('pedido', function (Blueprint $table) {
            $table->id();
            $table->string('codigo')->unique();
            $table->string('numero_tracking');
            $table->text('descripcion');
            $table->decimal('precio', 10, 2)->nullable();
            $table->decimal('peso', 10, 2)->nullable();
            $table->unsignedBigInteger('id_empresa_envio')->nullable();
            $table->unsignedBigInteger('id_tienda');
            $table->text('otra_tienda')->nullable();
            $table->unsignedBigInteger('id_ciudad_destino');
            $table->dateTime('fecha_entrega')->nullable();
            $table->dateTime('fecha_entrega_estimada')->nullable();
            $table->text('instruccion')->nullable();
            $table->integer('anio');
            $table->unsignedBigInteger('id_cliente');
            $table->unsignedBigInteger('id_usurio_gestion')->nullable();
            $table->unsignedBigInteger('id_estado');
            $table->boolean('activo')->default(true);
            $table->timestamps();

            $table->foreign('id_empresa_envio')->references('id')->on('empresas_envio');
            $table->foreign('id_tienda')->references('id')->on('tiendas');
            $table->foreign('id_ciudad_destino')->references('id')->on('ciudades');
            $table->foreign('id_cliente')->references('id')->on('users');
            $table->foreign('id_usurio_gestion')->references('id')->on('users');
            $table->foreign('id_estado')->references('id')->on('estados');
        });
        
    }

    public function down(): void
    {
        Schema::dropIfExists('pedido');
        Schema::dropIfExists('estados');
    }
};
