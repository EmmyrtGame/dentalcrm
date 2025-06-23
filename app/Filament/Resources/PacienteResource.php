<?php

namespace App\Filament\Resources;

use App\Filament\Resources\PacienteResource\Pages;
use App\Models\Paciente;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\Filter;
use Filament\Infolists\Infolist;
use Filament\Infolists\Components\TextEntry;
use Filament\Infolists\Components\Section;
use Illuminate\Database\Eloquent\Builder;

class PacienteResource extends Resource
{
    protected static ?string $model = Paciente::class;

    protected static ?string $navigationIcon = 'heroicon-o-users';

    protected static ?string $navigationLabel = 'Pacientes';

    protected static ?string $modelLabel = 'Paciente';

    protected static ?string $tenantRelationshipName = 'pacientes';

    protected static ?string $pluralModelLabel = 'Pacientes';

    protected static ?int $navigationSort = 1;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Información Personal')
                    ->schema([
                        Forms\Components\TextInput::make('numero_expediente')
                            ->label('Número de Expediente')
                            ->unique(ignoreRecord: true)
                            ->disabled()
                            ->dehydrated(false),
                        
                        Forms\Components\Grid::make(3)
                            ->schema([
                                Forms\Components\TextInput::make('nombre')
                                    ->label('Nombre(s)')
                                    ->required()
                                    ->maxLength(255),
                                
                                Forms\Components\TextInput::make('apellido_paterno')
                                    ->label('Apellido Paterno')
                                    ->required()
                                    ->maxLength(255),
                                
                                Forms\Components\TextInput::make('apellido_materno')
                                    ->label('Apellido Materno')
                                    ->maxLength(255),
                            ]),
                        
                        Forms\Components\Grid::make(3)
                            ->schema([
                                Forms\Components\DatePicker::make('fecha_nacimiento')
                                    ->label('Fecha de Nacimiento')
                                    ->required()
                                    ->native(false)
                                    ->displayFormat('d/m/Y')
                                    ->maxDate(now()),
                                
                                Forms\Components\Select::make('sexo')
                                    ->label('Sexo')
                                    ->required()
                                    ->options([
                                        'masculino' => 'Masculino',
                                        'femenino' => 'Femenino',
                                        'otro' => 'Otro',
                                    ]),
                                
                                Forms\Components\FileUpload::make('fotografia')
                                    ->label('Fotografía')
                                    ->image()
                                    ->directory('pacientes/fotos')
                                    ->visibility('private'),
                            ]),
                    ]),

                Forms\Components\Section::make('Información de Contacto')
                    ->schema([
                        Forms\Components\Grid::make(2)
                            ->schema([
                                Forms\Components\TextInput::make('telefono')
                                    ->label('Teléfono Principal')
                                    ->required()
                                    ->tel()
                                    ->maxLength(20),
                                
                                Forms\Components\TextInput::make('telefono_secundario')
                                    ->label('Teléfono Secundario')
                                    ->tel()
                                    ->maxLength(20),
                            ]),
                        
                        Forms\Components\TextInput::make('email')
                            ->label('Correo Electrónico')
                            ->email()
                            ->maxLength(255),
                        
                        Forms\Components\Textarea::make('direccion')
                            ->label('Dirección')
                            ->required()
                            ->rows(2),
                        
                        Forms\Components\Grid::make(3)
                            ->schema([
                                Forms\Components\TextInput::make('ciudad')
                                    ->label('Ciudad')
                                    ->required()
                                    ->maxLength(100),
                                
                                Forms\Components\TextInput::make('estado')
                                    ->label('Estado')
                                    ->required()
                                    ->maxLength(100),
                                
                                Forms\Components\TextInput::make('codigo_postal')
                                    ->label('Código Postal')
                                    ->required()
                                    ->maxLength(10),
                            ]),
                    ]),

                Forms\Components\Section::make('Contacto de Emergencia')
                    ->schema([
                        Forms\Components\Grid::make(3)
                            ->schema([
                                Forms\Components\TextInput::make('contacto_emergencia_nombre')
                                    ->label('Nombre del Contacto')
                                    ->maxLength(255),
                                
                                Forms\Components\TextInput::make('contacto_emergencia_telefono')
                                    ->label('Teléfono de Emergencia')
                                    ->tel()
                                    ->maxLength(20),
                                
                                Forms\Components\TextInput::make('contacto_emergencia_relacion')
                                    ->label('Relación')
                                    ->maxLength(100),
                            ]),
                    ]),

                Forms\Components\Section::make('Información Médica')
                    ->schema([
                        Forms\Components\Textarea::make('alergias')
                            ->label('Alergias Conocidas')
                            ->rows(2),
                        
                        Forms\Components\Textarea::make('condiciones_medicas')
                            ->label('Condiciones Médicas')
                            ->rows(2),
                        
                        Forms\Components\Textarea::make('medicamentos_actuales')
                            ->label('Medicamentos Actuales')
                            ->rows(2),
                    ]),

                Forms\Components\Section::make('Información del Seguro')
                    ->schema([
                        Forms\Components\Grid::make(3)
                            ->schema([
                                Forms\Components\TextInput::make('seguro_nombre')
                                    ->label('Nombre del Seguro')
                                    ->maxLength(255),
                                
                                Forms\Components\TextInput::make('seguro_numero_poliza')
                                    ->label('Número de Póliza')
                                    ->maxLength(100),
                                
                                Forms\Components\DatePicker::make('seguro_vigencia')
                                    ->label('Vigencia del Seguro')
                                    ->native(false)
                                    ->displayFormat('d/m/Y'),
                            ]),
                    ]),

                Forms\Components\Section::make('Notas Adicionales')
                    ->schema([
                        Forms\Components\Textarea::make('notas_generales')
                            ->label('Notas Generales')
                            ->rows(3),
                        
                        Forms\Components\Toggle::make('activo')
                            ->label('Paciente Activo')
                            ->default(true),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('numero_expediente')
                    ->label('ID')
                    ->sortable()
                    ->searchable(),
                
                Tables\Columns\ImageColumn::make('fotografia')
                    ->label('Foto')
                    ->circular()
                    ->defaultImageUrl(url('/images/default-avatar.png')),
                
                Tables\Columns\TextColumn::make('nombre_completo')
                    ->label('Nombre Completo')
                    ->sortable(['nombre', 'apellido_paterno'])
                    ->searchable(['nombre', 'apellido_paterno', 'apellido_materno']),
                
                Tables\Columns\TextColumn::make('telefono')
                    ->label('Teléfono')
                    ->searchable(),
                
                Tables\Columns\TextColumn::make('edad')
                    ->label('Edad')
                    ->suffix(' años')
                    ->sortable('fecha_nacimiento'),
                
                Tables\Columns\TextColumn::make('ultima_visita')
                    ->label('Última Visita')
                    ->dateTime('d/m/Y H:i')
                    ->sortable()
                    ->placeholder('Sin visitas'),
                
                Tables\Columns\IconColumn::make('activo')
                    ->label('Estado')
                    ->boolean()
                    ->trueIcon('heroicon-o-check-circle')
                    ->falseIcon('heroicon-o-x-circle')
                    ->trueColor('success')
                    ->falseColor('danger'),
            ])
            ->filters([
                SelectFilter::make('sexo')
                    ->label('Sexo')
                    ->options([
                        'masculino' => 'Masculino',
                        'femenino' => 'Femenino',
                        'otro' => 'Otro',
                    ]),
                
                SelectFilter::make('activo')
                    ->label('Estado')
                    ->options([
                        '1' => 'Activo',
                        '0' => 'Inactivo',
                    ]),
                
                Filter::make('con_seguro')
                    ->label('Con Seguro Médico')
                    ->query(fn (Builder $query): Builder => $query->whereNotNull('seguro_nombre')),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ])
            ->defaultSort('created_at', 'desc');
    }

    public static function infolist(Infolist $infolist): Infolist
    {
        return $infolist
            ->schema([
                Section::make('Información Personal')
                    ->schema([
                        TextEntry::make('numero_expediente')
                            ->label('Número de Expediente'),
                        TextEntry::make('nombre_completo')
                            ->label('Nombre Completo'),
                        TextEntry::make('edad')
                            ->label('Edad')
                            ->suffix(' años'),
                        TextEntry::make('sexo')
                            ->label('Sexo')
                            ->badge(),
                    ])
                    ->columns(2),
                
                Section::make('Contacto')
                    ->schema([
                        TextEntry::make('telefono')
                            ->label('Teléfono Principal'),
                        TextEntry::make('email')
                            ->label('Email'),
                        TextEntry::make('direccion')
                            ->label('Dirección'),
                    ])
                    ->columns(2),
                
                Section::make('Información Médica')
                    ->schema([
                        TextEntry::make('alergias')
                            ->label('Alergias'),
                        TextEntry::make('condiciones_medicas')
                            ->label('Condiciones Médicas'),
                        TextEntry::make('seguro_nombre')
                            ->label('Seguro Médico'),
                    ])
                    ->columns(1)
                    ->collapsible(),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListPacientes::route('/'),
            'create' => Pages\CreatePaciente::route('/create'),
            'view' => Pages\ViewPaciente::route('/{record}'),
            'edit' => Pages\EditPaciente::route('/{record}/edit'),
        ];
    }

    public static function getGlobalSearchEloquentQuery(): Builder
    {
        return parent::getGlobalSearchEloquentQuery()->with(['citas', 'facturas']);
    }

    public static function getGloballySearchableAttributes(): array
    {
        return ['nombre', 'apellido_paterno', 'telefono', 'numero_expediente'];
    }
}