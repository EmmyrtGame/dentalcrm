<?php

namespace App\Filament\Resources\PacienteResource\Pages;

use App\Filament\Resources\PacienteResource;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;

class CreatePaciente extends CreateRecord
{
    protected static string $resource = PacienteResource::class;
    
    /**
     * Redirect to the index page after creating a patient
     */
    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }

    /**
     * Customize the success notification title
     */
    protected function getCreatedNotificationTitle(): ?string
    {
        return 'Paciente registrado exitosamente';
    }
}
