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
                    ->icon('heroicon-o-user')
                    ->schema([
                        Forms\Components\TextInput::make('numero_expediente')
                            ->label('Número de Expediente')
                            ->prefixIcon('heroicon-o-hashtag')
                            ->unique(ignoreRecord: true)
                            ->disabled()
                            ->dehydrated(false),
                        
                        Forms\Components\Grid::make(3)
                            ->schema([
                                Forms\Components\TextInput::make('nombre')
                                    ->label('Nombre(s)')
                                    ->prefixIcon('heroicon-o-user')
                                    ->required()
                                    ->maxLength(255),
                                
                                Forms\Components\TextInput::make('apellido_paterno')
                                    ->label('Apellido Paterno')
                                    ->prefixIcon('heroicon-o-user-group')
                                    ->required()
                                    ->maxLength(255),
                                
                                Forms\Components\TextInput::make('apellido_materno')
                                    ->label('Apellido Materno')
                                    ->prefixIcon('heroicon-o-user-group')
                                    ->maxLength(255),
                            ]),
                        
                        Forms\Components\Grid::make(3)
                            ->schema([
                                Forms\Components\DatePicker::make('fecha_nacimiento')
                                    ->label('Fecha de Nacimiento')
                                    ->hint('Fecha de nacimiento')
                                    ->hintIcon('heroicon-o-calendar-days')
                                    ->hintColor('primary')
                                    ->required()
                                    ->native(false)
                                    ->displayFormat('d/m/Y')
                                    ->maxDate(now()),
                                
                                Forms\Components\Select::make('sexo')
                                    ->label('Sexo')
                                    ->hint('Género del paciente')
                                    ->hintIcon('heroicon-o-identification')
                                    ->hintColor('primary')
                                    ->required()
                                    ->options([
                                        'masculino' => 'Masculino',
                                        'femenino' => 'Femenino',
                                        'otro' => 'Otro',
                                    ]),
                                
                                Forms\Components\FileUpload::make('fotografia')
                                    ->label('Fotografía')
                                    ->hint('Foto del paciente')
                                    ->hintIcon('heroicon-o-camera')
                                    ->hintColor('primary')
                                    ->image()
                                    ->directory('pacientes/fotos')
                                    ->visibility('private'),
                            ]),
                    ]),

                Forms\Components\Section::make('Información de Contacto')
                    ->icon('heroicon-o-phone')
                    ->schema([
                        Forms\Components\Grid::make(2)
                            ->schema([
                                Forms\Components\TextInput::make('telefono')
                                    ->label('Teléfono Principal')
                                    ->prefixIcon('heroicon-o-phone')
                                    ->prefixIconColor('success')
                                    ->required()
                                    ->tel()
                                    ->maxLength(20),
                                
                                Forms\Components\TextInput::make('telefono_secundario')
                                    ->label('Teléfono Secundario')
                                    ->prefixIcon('heroicon-o-device-phone-mobile')
                                    ->prefixIconColor('gray')
                                    ->tel()
                                    ->maxLength(20),
                            ]),
                        
                        Forms\Components\TextInput::make('email')
                            ->label('Correo Electrónico')
                            ->prefixIcon('heroicon-o-envelope')
                            ->prefixIconColor('primary')
                            ->email()
                            ->maxLength(255),
                        
                        Forms\Components\Textarea::make('direccion')
                            ->label('Dirección')
                            ->hint('Dirección completa del paciente')
                            ->hintIcon('heroicon-o-map-pin')
                            ->hintColor('primary')
                            ->required()
                            ->rows(2),
                        
                        Forms\Components\Grid::make(3)
                            ->schema([
                                Forms\Components\TextInput::make('ciudad')
                                    ->label('Ciudad')
                                    ->prefixIcon('heroicon-o-building-office-2')
                                    ->required()
                                    ->maxLength(100),
                                
                                Forms\Components\TextInput::make('estado')
                                    ->label('Estado')
                                    ->prefixIcon('heroicon-o-map')
                                    ->required()
                                    ->maxLength(100),
                                
                                Forms\Components\TextInput::make('codigo_postal')
                                    ->label('Código Postal')
                                    ->prefixIcon('heroicon-o-map-pin')
                                    ->prefixIconColor('warning')
                                    ->required()
                                    ->maxLength(10),
                            ]),
                    ]),

                Forms\Components\Section::make('Contacto de Emergencia')
                    ->icon('heroicon-o-exclamation-triangle')
                    ->iconColor('danger')
                    ->schema([
                        Forms\Components\Grid::make(3)
                            ->schema([
                                Forms\Components\TextInput::make('contacto_emergencia_nombre')
                                    ->label('Nombre del Contacto')
                                    ->prefixIcon('heroicon-o-user-plus')
                                    ->prefixIconColor('danger')
                                    ->maxLength(255),
                                
                                Forms\Components\TextInput::make('contacto_emergencia_telefono')
                                    ->label('Teléfono de Emergencia')
                                    ->prefixIcon('heroicon-o-phone-arrow-up-right')
                                    ->prefixIconColor('danger')
                                    ->tel()
                                    ->maxLength(20),
                                
                                Forms\Components\TextInput::make('contacto_emergencia_relacion')
                                    ->label('Relación')
                                    ->prefixIcon('heroicon-o-heart')
                                    ->prefixIconColor('danger')
                                    ->maxLength(100),
                            ]),
                    ]),

                Forms\Components\Section::make('Información Médica')
                    ->icon('heroicon-o-heart')
                    ->iconColor('danger')
                    ->schema([
                        Forms\Components\Textarea::make('alergias')
                            ->label('Alergias Conocidas')
                            ->hint('Alergias y reacciones adversas')
                            ->hintIcon('heroicon-o-exclamation-circle')
                            ->hintColor('danger')
                            ->placeholder('Ej: Penicilina, látex, anestésicos...')
                            ->rows(2),
                        
                        Forms\Components\Textarea::make('condiciones_medicas')
                            ->label('Condiciones Médicas')
                            ->hint('Enfermedades y condiciones')
                            ->hintIcon('heroicon-o-clipboard-document-list')
                            ->hintColor('warning')
                            ->placeholder('Ej: Hipertensión, diabetes, problemas cardíacos...')
                            ->rows(2),
                        
                        Forms\Components\Textarea::make('medicamentos_actuales')
                            ->label('Medicamentos Actuales')
                            ->hint('Medicamentos en uso')
                            ->hintIcon('heroicon-o-beaker')
                            ->hintColor('info')
                            ->placeholder('Ej: Metformina 500mg, Losartán...')
                            ->rows(2),
                    ]),

                Forms\Components\Section::make('Información del Seguro')
                    ->icon('heroicon-o-shield-check')
                    ->iconColor('success')
                    ->schema([
                        Forms\Components\Grid::make(3)
                            ->schema([
                                Forms\Components\TextInput::make('seguro_nombre')
                                    ->label('Nombre del Seguro')
                                    ->prefixIcon('heroicon-o-building-office')
                                    ->prefixIconColor('success')
                                    ->placeholder('Ej: IMSS, ISSTE...')
                                    ->maxLength(255),
                                
                                Forms\Components\TextInput::make('seguro_numero_poliza')
                                    ->label('Número de Póliza')
                                    ->prefixIcon('heroicon-o-credit-card')
                                    ->prefixIconColor('success')
                                    ->maxLength(100),
                                
                                Forms\Components\DatePicker::make('seguro_vigencia')
                                    ->label('Vigencia del Seguro')
                                    ->hint('Fecha de vencimiento')
                                    ->hintIcon('heroicon-o-calendar-days')
                                    ->hintColor('success')
                                    ->native(false)
                                    ->displayFormat('d/m/Y'),
                            ]),
                    ]),

                Forms\Components\Section::make('Notas Adicionales')
                    ->icon('heroicon-o-document-text')
                    ->schema([
                        Forms\Components\Textarea::make('notas_generales')
                            ->label('Notas Generales')
                            ->hint('Observaciones importantes')
                            ->hintIcon('heroicon-o-pencil-square')
                            ->hintColor('primary')
                            ->placeholder('Observaciones importantes del paciente...')
                            ->rows(3),
                        
                        Forms\Components\Toggle::make('activo')
                            ->label('Paciente Activo')
                            ->onIcon('heroicon-s-check-circle')
                            ->offIcon('heroicon-s-x-circle')
                            ->onColor('success')
                            ->offColor('danger')
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
                    ->icon('heroicon-o-hashtag')
                    ->sortable()
                    ->searchable(),
                
                Tables\Columns\ImageColumn::make('fotografia')
                    ->label('Foto')
                    ->circular()
                    ->defaultImageUrl(url('/images/default-avatar.png')),
                
                Tables\Columns\TextColumn::make('nombre_completo')
                    ->label('Nombre Completo')
                    ->icon('heroicon-o-user')
                    ->sortable(['nombre', 'apellido_paterno'])
                    ->searchable(['nombre', 'apellido_paterno', 'apellido_materno']),
                
                Tables\Columns\TextColumn::make('telefono')
                    ->label('Teléfono')
                    ->icon('heroicon-o-phone')
                    ->searchable(),
                
                Tables\Columns\TextColumn::make('edad')
                    ->label('Edad')
                    ->icon('heroicon-o-calendar-days')
                    ->suffix(' años')
                    ->sortable('fecha_nacimiento'),
                
                Tables\Columns\TextColumn::make('ultima_visita')
                    ->label('Última Visita')
                    ->icon('heroicon-o-clock')
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