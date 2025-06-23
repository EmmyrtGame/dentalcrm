<?php

namespace App\Filament\Resources;

use App\Filament\Resources\PacienteResource\Pages;
use App\Models\Paciente;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Forms\Get;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\Filter;
use Filament\Infolists\Infolist;
use Filament\Infolists\Components\TextEntry;
use Filament\Infolists\Components\Section;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Arr;

class PacienteResource extends Resource
{
    protected static ?string $model = Paciente::class;

    protected static ?string $navigationIcon = 'heroicon-o-users';

    protected static ?string $navigationLabel = 'Pacientes';

    protected static ?string $modelLabel = 'Paciente';

    protected static ?string $tenantRelationshipName = 'pacientes';

    protected static ?string $pluralModelLabel = 'Pacientes';

    protected static ?int $navigationSort = 1;

    /**
     * Formatea un número de teléfono agregando guiones
     */
    private static function formatearTelefono($state, callable $set, string $fieldName): void
    {
        if ($state) {
            $formatted = preg_replace('/[^0-9]/', '', $state);
            if (strlen($formatted) === 10) {
                $formatted = substr($formatted, 0, 3) . '-' . 
                            substr($formatted, 3, 3) . '-' . 
                            substr($formatted, 6);
                $set($fieldName, $formatted);
            }
        }
    }

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
                                    ->live(onBlur: true) // Actualiza al perder foco
                                    ->afterStateUpdated(function ($state, callable $set) {
                                        // Generar sugerencia de email
                                        if (!empty($state)) {
                                            $email = strtolower($state) . '@ejemplo.com';
                                            $set('email_sugerido', $email);
                                        }
                                    })
                                    ->maxLength(255),
                                
                                Forms\Components\TextInput::make('apellido_paterno')
                                    ->label('Apellido Paterno')
                                    ->prefixIcon('heroicon-o-user-group')
                                    ->required()
                                    ->live(onBlur: true)
                                    ->maxLength(255),
                                
                                Forms\Components\TextInput::make('apellido_materno')
                                    ->label('Apellido Materno')
                                    ->prefixIcon('heroicon-o-user-group')
                                    ->live(onBlur: true)
                                    ->maxLength(255),
                            ]),
                        
                        // Campo calculado dinámicamente
                        Forms\Components\Placeholder::make('nombre_completo_preview')
                            ->label('Vista Previa del Nombre')
                            ->content(function (Get $get): string {
                                $nombre = $get('nombre') ?? '';
                                $apellidoP = $get('apellido_paterno') ?? '';
                                $apellidoM = $get('apellido_materno') ?? '';
                                
                                return trim("$nombre $apellidoP $apellidoM") ?: 'Esperando datos...';
                            }),
                        
                        Forms\Components\Grid::make(3)
                            ->schema([
                                Forms\Components\DatePicker::make('fecha_nacimiento')
                                    ->label('Fecha de Nacimiento')
                                    ->hint('Fecha de nacimiento')
                                    ->hintIcon('heroicon-o-calendar-days')
                                    ->hintColor('primary')
                                    ->required()
                                    ->live() // Reactividad inmediata
                                    ->afterStateUpdated(function ($state, callable $set) {
                                        // Calcular edad automáticamente
                                        if ($state) {
                                            $edad = \Carbon\Carbon::parse($state)->age;
                                            $set('edad_calculada', $edad . ' años');
                                        }
                                    })
                                    ->native(false)
                                    ->displayFormat('d/m/Y')
                                    ->maxDate(now()),
                                
                                Forms\Components\Select::make('sexo')
                                    ->label('Sexo')
                                    ->hint('Género del paciente')
                                    ->hintIcon('heroicon-o-identification')
                                    ->hintColor('primary')
                                    ->required()
                                    ->live() // Cambio inmediato
                                    ->options([
                                        'masculino' => 'Masculino',
                                        'femenino' => 'Femenino',
                                        'otro' => 'Otro',
                                    ]),
                                
                                // Campo calculado que muestra la edad
                                Forms\Components\Placeholder::make('edad_calculada')
                                    ->label('Edad Calculada')
                                    ->content(function (Get $get): string {
                                        $fecha = $get('fecha_nacimiento');
                                        if ($fecha) {
                                            return \Carbon\Carbon::parse($fecha)->age . ' años';
                                        }
                                        return 'Selecciona fecha de nacimiento';
                                    }),
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
                                    ->live(onBlur: true)
                                    ->afterStateUpdated(fn ($state, callable $set) => 
                                        self::formatearTelefono($state, $set, 'telefono'))
                                    ->tel()
                                    ->rules(['regex:/^[\d\s-]{10,20}$/'])
                                    ->validationMessages([
                                        'regex' => 'El teléfono debe contener entre 10 y 20 dígitos, y puede incluir guiones o espacios.',
                                        'required' => 'El teléfono principal es obligatorio.'
                                    ])
                                    ->maxLength(20),
                                
                                Forms\Components\TextInput::make('telefono_secundario')
                                    ->label('Teléfono Secundario')
                                    ->prefixIcon('heroicon-o-device-phone-mobile')
                                    ->prefixIconColor('gray')
                                    ->live(onBlur: true)
                                    ->afterStateUpdated(fn ($state, callable $set) => 
                                        self::formatearTelefono($state, $set, 'telefono_secundario'))
                                    ->tel()
                                    ->rules(['nullable', 'regex:/^[\d\s-]{10,20}$/'])
                                    ->validationMessages([
                                        'regex' => 'El teléfono debe contener entre 10 y 20 dígitos, y puede incluir guiones o espacios.'
                                    ])
                                    ->maxLength(20),
                            ]),
                        
                        Forms\Components\TextInput::make('email')
                            ->label('Correo Electrónico')
                            ->prefixIcon('heroicon-o-envelope')
                            ->prefixIconColor('primary')
                            ->live(onBlur: true)
                            ->afterStateUpdated(function ($state, callable $set) {
                                // Validar email en tiempo real
                                if ($state && !filter_var($state, FILTER_VALIDATE_EMAIL)) {
                                    $set('email_valido', '❌ Email inválido');
                                } else {
                                    $set('email_valido', '✅ Email válido');
                                }
                            })
                            ->email()
                            ->maxLength(255),
                        
                        // Indicador visual de validación
                        Forms\Components\Placeholder::make('email_valido')
                            ->label('Estado del Email')
                            ->content(function (Get $get): string {
                                $email = $get('email');
                                if (!$email) return 'Esperando email...';
                                
                                return filter_var($email, FILTER_VALIDATE_EMAIL) 
                                    ? '✅ Email válido' 
                                    : '❌ Email inválido';
                            }),
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

                // Sección condicional que aparece solo si tiene seguro
                Forms\Components\Section::make('Información del Seguro')
                    ->icon('heroicon-o-shield-check')
                    ->iconColor('success')
                    ->schema([
                        Forms\Components\Toggle::make('tiene_seguro')
                            ->label('¿Tiene Seguro Médico?')
                            ->live()
                            ->afterStateHydrated(function (callable $set, ?\App\Models\Paciente $record) {
                                $set('tiene_seguro',
                                    filled($record?->seguro_nombre)
                                || filled($record?->seguro_numero_poliza)
                                || filled($record?->seguro_vigencia)
                                );
                            })
                            ->afterStateUpdated(function ($state, callable $set) {
                                // Limpia los campos en el formulario cuando el usuario apaga el toggle
                                if (! $state) {
                                    $set('seguro_nombre', null);
                                    $set('seguro_numero_poliza', null);
                                    $set('seguro_vigencia', null);
                                }
                            }),
                        
                        Forms\Components\Grid::make(3)
                            ->schema([
                                Forms\Components\TextInput::make('seguro_nombre')
                                    ->label('Nombre del Seguro')
                                    ->prefixIcon('heroicon-o-building-office')
                                    ->prefixIconColor('success')
                                    ->placeholder('Ej: IMSS, ISSTE...')
                                    ->hidden(fn (Get $get): bool => !$get('tiene_seguro')) // Se oculta dinámicamente
                                    ->maxLength(255),
                                
                                Forms\Components\TextInput::make('seguro_numero_poliza')
                                    ->label('Número de Póliza')
                                    ->prefixIcon('heroicon-o-credit-card')
                                    ->prefixIconColor('success')
                                    ->hidden(fn (Get $get): bool => !$get('tiene_seguro'))
                                    ->maxLength(100),
                                
                                Forms\Components\DatePicker::make('seguro_vigencia')
                                    ->label('Vigencia del Seguro')
                                    ->hint('Fecha de vencimiento')
                                    ->hintIcon('heroicon-o-calendar-days')
                                    ->hintColor('success')
                                    ->hidden(fn (Get $get): bool => !$get('tiene_seguro'))
                                    ->live()
                                    ->afterStateUpdated(function ($state, callable $set) {
                                        // Verificar si el seguro está por vencer
                                        if ($state) {
                                            $vencimiento = \Carbon\Carbon::parse($state);
                                            $diasRestantes = floor(now()->diffInDays($vencimiento, false));
                                            
                                            if ($diasRestantes < 30 && $diasRestantes > 0) {
                                                $set('alerta_seguro', "⚠️ Seguro vence en $diasRestantes días");
                                            } elseif ($diasRestantes <= 0) {
                                                $set('alerta_seguro', '🚨 Seguro vencido');
                                            } else {
                                                $set('alerta_seguro', '✅ Seguro vigente');
                                            }
                                        }
                                    })
                                    ->native(false)
                                    ->displayFormat('d/m/Y'),
                            ]),
                        
                        // Alerta dinámica del estado del seguro
                        Forms\Components\Placeholder::make('alerta_seguro')
                            ->label('Estado del Seguro')
                            ->hidden(fn (Get $get): bool => !$get('tiene_seguro'))
                            ->content(function (Get $get): string {
                                $vigencia = $get('seguro_vigencia');
                                if (!$vigencia) return 'Ingresa fecha de vigencia';
                                
                                $vencimiento = \Carbon\Carbon::parse($vigencia);
                                $diasRestantes = floor(now()->diffInDays($vencimiento, false));
                                
                                if ($diasRestantes < 30 && $diasRestantes > 0) {
                                    return "⚠️ Seguro vence en $diasRestantes días";
                                } elseif ($diasRestantes <= 0) {
                                    return '🚨 Seguro vencido';
                                }
                                
                                return '✅ Seguro vigente';
                            }),
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