<?php

namespace App\Filament\Resources\CitaResource\Widgets;

use App\Filament\Resources\CitaResource;
use App\Models\Cita;
use Saade\FilamentFullCalendar\Widgets\FullCalendarWidget;
use Saade\FilamentFullCalendar\Actions;

class CitaCalendarWidget extends FullCalendarWidget
{
    public function fetchEvents(array $fetchInfo): array
    {
        return Cita::query()
            ->where('fecha_cita', '>=', $fetchInfo['start'])
            ->where('fecha_cita', '<=', $fetchInfo['end'])
            ->with(['paciente', 'expediente'])
            ->get()
            ->map(function (Cita $cita) {
                return [
                    'id' => $cita->id,
                    'title' => $cita->paciente?->nombre ?? 'Sin paciente',
                    'start' => $cita->fecha_cita->toISOString(),
                    'end' => $cita->fecha_cita->addMinutes(30)->toISOString(),
                    'extendedProps' => [
                        'expediente' => $cita->expediente?->numero_expediente,
                        'descripcion' => $cita->descripcion,
                    ],
                ];
            })
            ->all();
    }

    public function getFormSchema(): array
    {
        return CitaResource::form(
            \Filament\Forms\Form::make($this)
        )->getSchema();
    }

    protected function headerActions(): array
    {
        return [
            Actions\CreateAction::make()
                ->mountUsing(
                    function (\Filament\Forms\Form $form, array $arguments) {
                        $form->fill([
                            'fecha_cita' => $arguments['start'] ?? now(),
                        ]);
                    }
                ),
        ];
    }

    protected function modalActions(): array
    {
        return [
            Actions\EditAction::make()
                ->mountUsing(
                    function (Cita $record, \Filament\Forms\Form $form, array $arguments) {
                        $form->fill($record->attributesToArray());
                    }
                ),
            Actions\DeleteAction::make(),
        ];
    }

    public function config(): array
    {
        return [
            'firstDay' => 1,
            'locale' => 'es',
            'headerToolbar' => [
                'left' => 'prev,next today',
                'center' => 'title',
                'right' => 'dayGridMonth,timeGridWeek,timeGridDay',
            ],
            'businessHours' => [
                'daysOfWeek' => [1, 2, 3, 4, 5],
                'startTime' => '08:00',
                'endTime' => '18:00',
            ],
            'slotMinTime' => '07:00:00',
            'slotMaxTime' => '20:00:00',
            'height' => 'auto',
            'nowIndicator' => true,
            'selectable' => true,
            'selectMirror' => true,
        ];
    }
}