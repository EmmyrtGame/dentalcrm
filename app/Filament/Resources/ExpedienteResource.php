<?php

namespace App\Filament\Resources;

use App\Models\Expediente;
use App\Models\Paciente;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Filament\Facades\Filament;

class ExpedienteResource extends Resource
{
    protected static ?string $model = Expediente::class;

    protected static ?string $navigationIcon = 'heroicon-o-clipboard-document-list';

    protected static ?string $navigationLabel = 'Expedientes';

    protected static ?string $tenantRelationshipName = 'expedientes';

    protected static ?string $pluralModelLabel = 'Expedientes';

    protected static ?string $modelLabel = 'Expediente';

    protected static ?int $navigationSort = 2;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Datos Generales')
                    ->schema([
                        Forms\Components\Select::make('paciente_id')
                            ->label('Paciente')
                            ->relationship('paciente', 'nombre_completo')
                            ->searchable()
                            ->preload()
                            ->required()
                            ->getSearchResultsUsing(function (string $search): array {
                                $pacientes = Paciente::where('nombre', 'like', "%{$search}%")
                                    ->orWhere('apellido_paterno', 'like', "%{$search}%")
                                    ->orWhere('apellido_materno', 'like', "%{$search}%")
                                    ->orderBy('created_at', 'desc')
                                    ->limit(50)
                                    ->get();

                                return $pacientes->mapWithKeys(function ($paciente) {
                                    return [$paciente->getKey() => $paciente->nombre_completo];
                                })->toArray();
                            })
                            ->default(function () {
                                $ultimoPaciente = Paciente::latest()->first();
                                return $ultimoPaciente ? $ultimoPaciente->id : null;
                            })
                            ->getOptionLabelFromRecordUsing(function (Paciente $record): string {
                                return $record->nombre_completo;
                            }),

                        Forms\Components\Select::make('tipo_consulta')
                            ->label('Tipo de Consulta')
                            ->options([
                                'valoración' => 'Valoración inicial',
                                'limpieza'   => 'Limpieza',
                                'operatoria' => 'Operatoria',
                                'endodoncia' => 'Endodoncia',
                                'ortodoncia' => 'Ortodoncia',
                            ])
                            ->required(),

                        Forms\Components\Textarea::make('diagnostico')
                            ->label('Diagnóstico')
                            ->rows(3),

                        Forms\Components\Textarea::make('tratamiento')
                            ->label('Plan de Tratamiento')
                            ->rows(4),

                        Forms\Components\DatePicker::make('fecha_proxima_cita')
                            ->label('Próxima Cita')
                            ->minDate(now())
                            ->native(false),

                        Forms\Components\Toggle::make('completado')
                            ->label('Tratamiento Completado')
                            ->onColor('success')
                            ->offColor('gray'),
                    ])
                    ->columns(2),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('numero_expediente')
                    ->label('Num. Exp.')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('paciente.nombre_completo')
                    ->label('Paciente')
                    ->searchable(['nombre', 'apellido_paterno'])
                    ->sortable(),

                Tables\Columns\TextColumn::make('tipo_consulta')
                    ->label('Tipo')
                    ->badge()
                    ->sortable(),

                Tables\Columns\TextColumn::make('diagnostico')
                    ->limit(25)
                    ->tooltip(fn ($record) => $record->diagnostico),

                Tables\Columns\IconColumn::make('completado')
                    ->label('✓')
                    ->boolean()
                    ->trueColor('success')
                    ->falseColor('warning'),

                Tables\Columns\TextColumn::make('created_at')
                    ->label('Fecha')
                    ->date('d/m/Y')
                    ->sortable(),
            ])
            ->filters([
                SelectFilter::make('tipo_consulta')
                    ->label('Tipo de Consulta')
                    ->options([
                        'valoración' => 'Valoración',
                        'limpieza'   => 'Limpieza',
                        'operatoria' => 'Operatoria',
                        'endodoncia' => 'Endodoncia',
                        'ortodoncia' => 'Ortodoncia',
                    ]),

                Filter::make('completado')
                    ->label('Pendientes')
                    ->query(function ($query) {
                        return $query->where('completado', false);
                    }),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\Action::make('agendarCita')
                    ->label('Agendar Cita')
                    ->icon('heroicon-o-calendar')
                    ->url(fn ($record) => route('filament.admin.resources.citas.create', [
                        'tenant' => Filament::getTenant()->id,
                        'paciente_id' => $record->paciente_id,
                        'expediente_id' => $record->id,
                    ]))
                    ->openUrlInNewTab(false),
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\DeleteBulkAction::make(),
            ])
            ->defaultSort('created_at', 'desc');
    }

    public static function getPages(): array
    {
        return [
            'index' => ExpedienteResource\Pages\ListExpedientes::route('/'),
            'create' => ExpedienteResource\Pages\CreateExpediente::route('/create'),
            'view' => ExpedienteResource\Pages\ViewExpediente::route('/{record}'),
            'edit' => ExpedienteResource\Pages\EditExpediente::route('/{record}/edit'),
        ];
    }
}