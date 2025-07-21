<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Expediente extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'paciente_id',
        'numero_expediente',
        'tipo_consulta',
        'diagnostico',
        'tratamiento',
        'fecha_proxima_cita',
        'completado',
        'team_id',
    ];

    protected $casts = [
        'fecha_proxima_cita' => 'date',
        'completado'         => 'boolean',
    ];

    /* RELACIONES */
    public function paciente()
    {
        return $this->belongsTo(Paciente::class);
    }

    public function team()
    {
        return $this->belongsTo(Team::class);
    }

    /* BOOT: genera número incremental AAAA-0001 y refresca última visita */
    protected static function booted(): void
    {
        static::creating(function (self $expediente) {
            $expediente->numero_expediente = now()->format('Y') . '-' .
                str_pad(self::whereYear('created_at', now()->year)->count() + 1, 4, '0', STR_PAD_LEFT);
        });

        static::created(function (self $expediente) {
            $expediente->paciente->update(['ultima_visita' => now()]);
        });
    }
}
