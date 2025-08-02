<?php

namespace App\Rules;

use App\Models\Cita;
use Carbon\Carbon;
use Closure;
use Filament\Facades\Filament;
use Illuminate\Contracts\Validation\ValidationRule;

class NoSolapamientoCitas implements ValidationRule
{
    protected ?int $excludeId;

    public function __construct(?int $excludeId = null)
    {
        $this->excludeId = $excludeId;
    }

    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if (!$value) {
            return;
        }

        $citaDateTime = Carbon::parse($value);
        $citaStart = $citaDateTime;
        $citaEnd = $citaDateTime->copy()->addMinutes(30);

        $query = Cita::query()
            ->where(function ($q) use ($citaStart, $citaEnd) {
                // Verificar solapamiento: nueva cita empieza antes de que termine una existente
                // Y nueva cita termina después de que empiece una existente
                $q->where(function ($subQuery) use ($citaStart, $citaEnd) {
                    $subQuery->whereRaw('fecha_cita < ?', [$citaEnd])
                             ->whereRaw('DATE_ADD(fecha_cita, INTERVAL 30 MINUTE) > ?', [$citaStart]);
                });
            });

        // Excluir la cita actual si estamos editando
        if ($this->excludeId) {
            $query->where('id', '!=', $this->excludeId);
        }

        // Filtrar por tenant si estamos usando tenancy
        if (Filament::getTenant()) {
            $query->where('team_id', Filament::getTenant()->id);
        }

        $overlappingCita = $query->first();

        if ($overlappingCita) {
            $overlappingDateTime = Carbon::parse($overlappingCita->fecha_cita)->format('d/m/Y H:i');
            $pacienteNombre = $overlappingCita->paciente?->nombre ?? 'Sin paciente';
            
            $fail("Ya existe una cita programada el {$overlappingDateTime} para {$pacienteNombre}. Las citas no pueden solaparse.");
        }
    }
}