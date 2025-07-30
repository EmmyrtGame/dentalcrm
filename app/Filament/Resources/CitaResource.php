<?php

namespace App\Filament\Resources;

use App\Filament\Resources\CitaResource\Pages;
use App\Filament\Resources\CitaResource\Pages\Calendar;
use App\Filament\Resources\CitaResource\RelationManagers;
use App\Models\Cita;
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

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    protected static ?string $tenantRelationshipName = 'citas';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Select::make('paciente_id')
                    ->label('Paciente')
                    ->relationship(
                        name: 'paciente', 
                        titleAttribute: 'nombre', 
                        modifyQueryUsing: fn (Builder $query) => $query->orderBy('created_at')->limit(10)
                    )
                    ->searchable()
                    ->preload()
                    ->nullable(),
                Select::make('expediente_id')
                    ->label('Expediente')
                    ->relationship(
                        name: 'expediente', 
                        titleAttribute: 'numero_expediente', 
                        modifyQueryUsing: fn (Builder $query) => $query->orderBy('created_at')->limit(10)
                    )
                    ->searchable()
                    ->preload()
                    ->nullable(),
                DateTimePicker::make('fecha_cita')
                    ->label('Fecha y Hora de la Cita')
                    ->required()
                    ->displayFormat('F j, Y H:i')
                    ->firstDayOfWeek(1)
                    ->minDate(now())
                    ->minutesStep(30)
                    ->seconds(false)
                    ->native(false),
                \Filament\Forms\Components\TextInput::make('descripcion')
                    ->maxLength(255)
                    ->nullable(),
            ]);
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
                \Filament\Tables\Actions\EditAction::make(),
                \Filament\Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                \Filament\Tables\Actions\DeleteBulkAction::make(),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
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
