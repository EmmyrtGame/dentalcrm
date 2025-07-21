<?php

namespace App\Filament\Resources\PacienteResource\Pages;

use App\Filament\Resources\PacienteResource;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Support\Arr;

class CreatePaciente extends CreateRecord
{
    protected static string $resource = PacienteResource::class;
    protected static bool $canCreateAnother = false;
    
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

    /**
     * Se ejecuta justo antes de hacer update() en la BD.
     */
    protected function mutateFormDataBeforeSave(array $data): array
    {
        // Verifica el estado del toggle que SÍ viaja en la petición
        if (empty($data['tiene_seguro'])) {
            $data['seguro_nombre']        = null;
            $data['seguro_numero_poliza'] = null;
            $data['seguro_vigencia']      = null;
        }

        // Elimina el campo virtual
        Arr::forget($data, 'tiene_seguro');

        return $data;
    }
}
