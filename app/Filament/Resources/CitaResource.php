<?php

namespace App\Filament\Resources;

use App\Filament\Resources\CitaResource\Pages;
use App\Filament\Resources\CitaResource\Pages\Calendar;
use App\Filament\Resources\CitaResource\RelationManagers;
use App\Filament\Resources\CitaResource\Widgets\CitaCalendarWidget;
use App\Models\Cita;
use App\Rules\NoOverlappingCitas;
use App\Rules\NoSolapamientoCitas;
use Filament\Forms;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class CitaResource extends Resource
{
    protected static ?string $model = Cita::class;

    protected static ?string $navigationIcon = 'heroicon-o-calendar';

    protected static ?string $tenantRelationshipName = 'citas';

    protected static ?int $navigationSort = 3;

    public static function form(Form $form): Form
    {
        return $form->schema(static::getFormFields());
    }

    public static function canCreate(): bool
    {
        return true;
    }

    public static function getFormFields(?int $excludeId = null, $operation = null): array
    {
        return [
            Select::make('paciente_id')
                ->label('Paciente (opcional)')
                ->relationship(
                    name: 'paciente', 
                    titleAttribute: 'nombre', 
                    modifyQueryUsing: fn (Builder $query) => $query->orderBy('created_at')->limit(10)
                )
                ->searchable()
                ->preload()
                ->default(request()->input('paciente_id'))
                ->nullable(),
            Select::make('expediente_id')
                ->label('Expediente (opcional)')
                ->relationship(
                    name: 'expediente', 
                    titleAttribute: 'numero_expediente', 
                    modifyQueryUsing: fn (Builder $query) => $query->orderBy('created_at')->limit(10)
                )
                ->searchable()
                ->preload()
                ->default(request()->input('expediente_id'))
                ->nullable(),
            DateTimePicker::make('fecha_cita')
                ->label('Fecha y Hora de la Cita')
                ->required()
                ->displayFormat('F j, Y H:i')
                ->firstDayOfWeek(1)
                ->minDate(now())
                ->default(now()->addDay()->setTime(9, 0))
                ->seconds(false)
                ->native(false)
                ->live(onBlur: true)
                ->rules([
                    function () {
                        return function (string $attribute, $value, \Closure $fail) {
                            if ($value) {
                                $date = \Carbon\Carbon::parse($value);
                                $minutes = $date->minute;
                                
                                if (!in_array($minutes, [0, 30])) {
                                    $fail('La hora debe terminar en :00 o :30 minutos.');
                                }
                            }
                        };
                    },
                    function ($get) use ($excludeId, $operation) {
                        // Si no se proporciona excludeId, intentar obtenerlo del contexto
                        $currentExcludeId = $excludeId;
                        if (!$currentExcludeId && $operation === 'edit') {
                            $currentExcludeId = request()->route('record') ?? null;
                        }
                        
                        return new NoSolapamientoCitas($currentExcludeId);
                    }
                ])
                ->validationMessages([
                    'required'       => 'La fecha y hora de la cita es obligatoria.',
                    'after_or_equal' => 'La fecha de la cita debe ser posterior a la fecha actual.',
                ]),
            \Filament\Forms\Components\Textarea::make('descripcion')
                ->label('Descripción (opcional)')
                ->maxLength(255)
                ->rows(3)
                ->nullable(),
        ];
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('fecha_cita')
                    ->dateTime('F j, Y H:i')
                    ->sortable(),
                TextColumn::make('paciente.nombre')
                    ->label('Paciente')
                    ->sortable()
                    ->searchable(),
                TextColumn::make('expediente.numero_expediente')
                    ->label('Expediente')
                    ->sortable()
                    ->searchable(),
                TextColumn::make('descripcion'),
            ])
            ->actions([
                \Filament\Tables\Actions\ViewAction::make(),
                \Filament\Tables\Actions\EditAction::make()
                    ->after(function ($livewire) {
                        $livewire->dispatch('refreshCalendar');
                    }),
                \Filament\Tables\Actions\DeleteAction::make()
                    ->after(function ($livewire) {
                        $livewire->dispatch('refreshCalendar');
                    }),
            ])
            ->bulkActions([
                \Filament\Tables\Actions\DeleteBulkAction::make()
                    ->after(function ($livewire) {
                        $livewire->dispatch('refreshCalendar');
                    }),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListCitas::route('/'),
            'create' => Pages\CreateCita::route('/create'),
            'edit' => Pages\EditCita::route('/{record}/edit'),
        ];
    }
}
