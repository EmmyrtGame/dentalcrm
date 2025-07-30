<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

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
}
