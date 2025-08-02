<?php

namespace App\Filament\Resources\CitaResource\Pages;

use App\Filament\Resources\CitaResource;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;

class CreateCita extends CreateRecord
{
    protected static string $resource = CitaResource::class;
    protected static bool $canCreateAnother = false;

    protected $listeners = [
        'calendarDateSelected' => 'setFechaCita',
    ];

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }

    public function setFechaCita(array $data): void
    {
        $date = \Carbon\Carbon::parse($data['date'])->setTimezone(config('app.timezone'));

        $this->resetValidation('fecha_cita');
        
        $this->form->fill([
            ...$this->form->getState(),
            'fecha_cita' => $date
        ]);
    }

    protected function getFooterWidgets(): array
    {
        return [
            CitaResource\Widgets\CreateCitaCalendarWidget::class,
        ];
    }
}
