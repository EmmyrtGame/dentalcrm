<?php

namespace App\Filament\Resources\PacienteResource\Pages;

use App\Filament\Resources\PacienteResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Support\Arr;

class EditPaciente extends EditRecord
{
    protected static string $resource = PacienteResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\ViewAction::make(),
            Actions\DeleteAction::make(),
        ];
    }

    /**
     * Redirect to the index page after updating a patient
     */
    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }

    /**
     * Customize the success notification title
     */
    protected function getSavedNotificationTitle(): ?string
    {
        return 'Información del paciente actualizada';
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
