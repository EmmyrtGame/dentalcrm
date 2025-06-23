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
        Schema::create('pacientes', function (Blueprint $table) {
            $table->id();

            // Datos demográficos (RF-001)
            $table->string('numero_expediente')->unique();
            $table->string('nombre');
            $table->string('apellido_paterno');
            $table->string('apellido_materno')->nullable();
            $table->date('fecha_nacimiento');
            $table->enum('sexo', ['masculino', 'femenino', 'otro']);
            $table->string('telefono');
            $table->string('telefono_secundario')->nullable();
            $table->string('email')->nullable();
            
            // Dirección
            $table->text('direccion');
            $table->string('ciudad');
            $table->string('estado');
            $table->string('codigo_postal', 10);
            $table->string('pais')->default('México');
            
            // Contacto de emergencia (RF-001)
            $table->string('contacto_emergencia_nombre')->nullable();
            $table->string('contacto_emergencia_telefono')->nullable();
            $table->string('contacto_emergencia_relacion')->nullable();
            
            // Información médica (RF-001)
            $table->text('alergias')->nullable();
            $table->text('condiciones_medicas')->nullable();
            $table->text('medicamentos_actuales')->nullable();
            
            // Seguro médico (RF-001)
            $table->string('seguro_nombre')->nullable();
            $table->string('seguro_numero_poliza')->nullable();
            $table->date('seguro_vigencia')->nullable();
            
            // Archivos y notas
            $table->string('fotografia')->nullable();
            $table->text('notas_generales')->nullable();
            
            // Control
            $table->boolean('activo')->default(true);
            $table->timestamp('ultima_visita')->nullable();

            // Foreign key para equipos
            $table->foreignId('team_id')->references('id')->on('teams')->onDelete('cascade');

            $table->timestamps();

            // Índices para búsqueda rápida (RF-003)
            $table->index(['nombre', 'apellido_paterno']);
            $table->index('telefono');
            $table->index('fecha_nacimiento');
            $table->index('numero_expediente');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pacientes');
    }
};
