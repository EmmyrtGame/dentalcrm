<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Team extends Model
{
    use HasFactory;
    protected $guarded = [];

    public function members()
    {
        return $this->belongsToMany(User::class, 'team_user', 'team_id', 'user_id');
    }

    public function pacientes()
    {
        return $this->hasMany(Paciente::class);
    }

    public function expedientes()
    {
        return $this->hasMany(Expediente::class);
    }
}
