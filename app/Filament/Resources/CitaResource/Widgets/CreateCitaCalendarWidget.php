<?php

namespace App\Filament\Resources\CitaResource\Widgets;

use App\Filament\Resources\CitaResource;
use App\Models\Cita;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Facades\Filament;
use Filament\Forms\Form;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\HtmlString;
use Illuminate\Support\Js;
use Saade\FilamentFullCalendar\Widgets\FullCalendarWidget;
use Saade\FilamentFullCalendar\Data\EventData;
use Saade\FilamentFullCalendar\FilamentFullCalendarPlugin;

class CreateCitaCalendarWidget extends FullCalendarWidget
{
    public Model | string | null $model = Cita::class;

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

    // Deshabilita acciones (no create/edit/delete)
    protected function headerActions(): array
    {
        return [];
    }

    public function config(): array
    {
        return [
            'firstDay' => 1,
            'locale' => 'es',
            'timeZone' => FilamentFullCalendarPlugin::make()->getTimezone(),
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
            'editable' => false,
            'nowIndicator' => true,
            'selectMirror' => true,
            'selectable' => true,
            'validRange' => [
                'start' => now()->toIso8601String(),
            ],
        ];
    }

    public function onEventClick(array $event): void
    {
        return;
    }

    public function onDateSelect(string $start, ?string $end, bool $allDay, ?array $view, ?array $resource): void
    {
        static $lastProcessedStart = null;
        [$start, $end] = $this->calculateTimezoneOffset($start, $end, $allDay);

        // Evita procesar el mismo evento múltiples veces comparando con la última fecha procesada
        if ($lastProcessedStart === $start->toIso8601String()) {
            return;
        }
        $lastProcessedStart = $start->toIso8601String();

        if ($start->lessThan(now())) {
            \Filament\Notifications\Notification::make()
                ->title('Fecha inválida')
                ->body('Solo se permiten fechas y horas futuras.')
                ->danger()
                ->send();
            $this->dispatch('unselect-calendar');
            return;
        }

        $this->dispatch('calendarDateSelected', ['date' => $start->toIso8601String()]);
    }

    public function getScriptsData(): array
    {
        return array_merge(parent::getScriptsData() ?? [], [
            'unselect' => Js::from('document.addEventListener("unselect-calendar", function() { calendar.unselect(); })'),
        ]);
    }
}