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
        Schema::create('expedientes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('paciente_id')->constrained()->cascadeOnDelete();
            // Foreign key para equipos
            $table->foreignId('team_id')->references('id')->on('teams')->onDelete('cascade');
            $table->string('numero_expediente')->unique();
            // Datos odontológicos básicos
            $table->enum('tipo_consulta', ['valoración', 'limpieza', 'operatoria', 'endodoncia', 'ortodoncia']);
            $table->string('diagnostico')->nullable();
            $table->text('tratamiento')->nullable();
            $table->date('fecha_proxima_cita')->nullable();
            $table->boolean('completado')->default(false);
            $table->timestamps();
            $table->softDeletes();
        });

    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('expedientes');
    }
};