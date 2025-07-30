<?php

namespace App\Filament\Resources\CitaResource\Widgets;

use App\Filament\Resources\CitaResource;
use App\Models\Cita;
use Filament\Facades\Filament;
use Illuminate\Database\Eloquent\Model;
use Saade\FilamentFullCalendar\Widgets\FullCalendarWidget;
use Saade\FilamentFullCalendar\Actions;
use Saade\FilamentFullCalendar\Data\EventData;
use Filament\Forms;

class CitaCalendarWidget extends FullCalendarWidget
{
    public Model | string | null $model = Cita::class;

    // Añadir listeners para eventos
    protected $listeners = ['refreshCalendar' => '$refresh'];

    public function fetchEvents(array $fetchInfo): array
    {
        return Cita::query()
            ->where('fecha_cita', '>=', $fetchInfo['start'])
            ->where('fecha_cita', '<=', $fetchInfo['end'])
            ->with(['paciente', 'expediente'])
            ->get()
            ->map(function (Cita $cita) {
                return EventData::make()
                    ->id($cita->id)
                    ->title($cita->paciente?->nombre ?? 'Sin paciente')
                    ->start($cita->fecha_cita)
                    ->end($cita->fecha_cita->addMinutes(30))
                    ->extendedProps([
                        'expediente' => $cita->expediente?->numero_expediente,
                        'descripcion' => $cita->descripcion,
                    ]);
            })
            ->toArray();
    }

    // Reutilizar los campos del Resource
    public function getFormSchema(): array
    {
        return CitaResource::getFormFields();
    }

    protected function headerActions(): array
    {
        return [
            Actions\CreateAction::make()
                ->mountUsing(
                    function (Forms\Form $form, array $arguments) {
                        $form->fill([
                            'fecha_cita' => $arguments['start'] ?? now(),
                            'team_id' => Filament::getTenant()?->id, // Añadir el team_id del tenant actual
                        ]);
                    }
                )
                ->using(function (array $data, string $model): Model {
                    // Asegurar que el team_id esté presente al crear
                    $data['team_id'] = Filament::getTenant()?->id;
                    
                    return $model::create($data);
                })
                ->after(function () {
                    $this->refreshRecords();
                    $this->dispatch('refreshTable');
                }),
        ];
    }

    protected function modalActions(): array
    {
        return [
            Actions\EditAction::make()
                ->mountUsing(
                    function (Cita $record, Forms\Form $form, array $arguments) {
                        $form->fill([
                            ...$record->attributesToArray(),
                            'fecha_cita' => $arguments['event']['start'] ?? $record->fecha_cita,
                        ]);
                    }
                )
                ->after(function () {
                    $this->refreshRecords();
                    // Emitir evento para refrescar la tabla
                    $this->dispatch('refreshTable');
                }),
            Actions\DeleteAction::make()
                ->after(function () {
                    $this->refreshRecords();
                    // Emitir evento para refrescar la tabla
                    $this->dispatch('refreshTable');
                }),
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