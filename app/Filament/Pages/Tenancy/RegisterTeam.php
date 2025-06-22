<?php
namespace App\Filament\Pages\Tenancy;

use App\Models\Team;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Pages\Tenancy\RegisterTenant;
use Illuminate\Support\Facades\Auth;

class RegisterTeam extends RegisterTenant
{
    public static function getLabel(): string
    {
        return "Register Team";
    }

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                TextInput::make('name'),
            ]);
    }

    public function handleRegistration(array $data): Team
    {
        $team = Team::create($data);

        $team->members()->attach(Auth::user());

        return $team;
    }
}