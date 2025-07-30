<?php

namespace App\Filament\Resources;

use App\Filament\Resources\CitaResource\Pages;
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
                Select::make('patient_id')
                ->label('Paciente')
                ->relationship('patient', 'nombre')
                ->searchable()
                ->preload()
                ->nullable(),
                Select::make('expediente_id')
                    ->label('Expediente')
                    ->relationship('expediente', 'numero_expediente')
                    ->searchable()
                    ->preload()
                    ->nullable(),
                DateTimePicker::make('appointment_at')
                    ->label('Fecha y Hora de la Cita')
                    ->required()
                    ->displayFormat('F j, Y H:i')
                    ->firstDayOfWeek(1)
                    ->minDate(now()),
                Forms\Components\TextInput::make('descripcion')
                    ->maxLength(255)
                    ->nullable(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('appointment_at')
                    ->dateTime('F j, Y H:i')
                    ->sortable(),
                TextColumn::make('patient.name')
                    ->label('Paciente')
                    ->sortable()
                    ->searchable(),
                TextColumn::make('expediente.title')
                    ->label('Expediente')
                    ->sortable()
                    ->searchable(),
                TextColumn::make('description'),
            ])
            ->filters([
                SelectFilter::make('patient_id')
                    ->relationship('patient', 'name')
                    ->label('Filtrar por Paciente'),
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
