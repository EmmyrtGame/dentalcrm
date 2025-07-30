<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class Cita extends Model
{

    protected $fillable = [
        'paciente_id',
        'expediente_id',
        'fecha_cita',
        'descripcion',
        'team_id',
    ];

    protected $casts = [
        'fecha_cita' => 'datetime',
    ];

    public function paciente()
    {
        return $this->belongsTo(Paciente::class);
    }

    public function expediente()
    {
        return $this->belongsTo(Expediente::class);
    }
    
    public function team()
    {
        return $this->belongsTo(Team::class);
    }

    protected static function boot()
    {
        parent::boot();

        static::saving(function ($model) {
            $date = Carbon::parse($model->fecha_cita);
            $minutes = $date->minute;
            if ($minutes % 30 !== 0) {
                throw new \Exception('La cita debe programarse en intervalos de 30 minutos.');
            }
        });
    }
}
