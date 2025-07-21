<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;
use Carbon\Carbon;

class Paciente extends Model
{
    use HasFactory;

    protected $fillable = [
        'numero_expediente',
        'nombre',
        'apellido_paterno',
        'apellido_materno',
        'fecha_nacimiento',
        'sexo',
        'telefono',
        'telefono_secundario',
        'email',
        'direccion',
        'ciudad',
        'estado',
        'codigo_postal',
        'pais',
        'contacto_emergencia_nombre',
        'contacto_emergencia_telefono',
        'contacto_emergencia_relacion',
        'alergias',
        'condiciones_medicas',
        'medicamentos_actuales',
        'seguro_nombre',
        'seguro_numero_poliza',
        'seguro_vigencia',
        'fotografia',
        'notas_generales',
        'activo',
        'ultima_visita',
        'team_id',
    ];

    protected $casts = [
        'fecha_nacimiento' => 'date',
        'seguro_vigencia' => 'date',
        'ultima_visita' => 'datetime',
        'activo' => 'boolean',
    ];

    protected $appends = [
        'tiene_seguro',
    ];

    // Accessor para verificar si tiene seguro
    public function getTieneSeguroAttribute(): bool
    {
        return filled($this->seguro_nombre) || filled($this->seguro_numero_poliza) || filled($this->seguro_vigencia);
    }

    // Accessor para nombre completo
    public function getNombreCompletoAttribute(): string
    {
        return trim($this->nombre . ' ' . $this->apellido_paterno . ' ' . $this->apellido_materno);
    }

    // Accessor para edad
    public function getEdadAttribute(): int
    {
        return $this->fecha_nacimiento->age;
    }

    // Generar número de expediente automáticamente
    protected static function boot()
    {
        parent::boot();
        
        static::creating(function ($paciente) {
            if (empty($paciente->numero_expediente)) {
                $paciente->numero_expediente = self::generarNumeroExpediente();
            }
        });
    }

    private static function generarNumeroExpediente(): string
    {
        $ultimoNumero = self::max('id') + 1;
        return str_pad($ultimoNumero, 4, '0', STR_PAD_LEFT);
    }

    public function scopeBuscar(Builder $query, string $termino): Builder
    {
        return $query->where(function ($q) use ($termino) {
            $q->where('nombre', 'like', "%{$termino}%")
              ->orWhere('apellido_paterno', 'like', "%{$termino}%")
              ->orWhere('apellido_materno', 'like', "%{$termino}%")
              ->orWhere('telefono', 'like', "%{$termino}%")
              ->orWhere('numero_expediente', 'like', "%{$termino}%");
        });
    }

    public function scopeActivos(Builder $query): Builder
    {
        return $query->where('activo', true);
    }

    public function team()
    {
        return $this->belongsTo(Team::class);
    }

    public function expedientes()
    {
        return $this->hasMany(Expediente::class);
    }

    public function expedienteReciente()
    {
        return $this->hasOne(Expediente::class)->latestOfMany();
    }
}
