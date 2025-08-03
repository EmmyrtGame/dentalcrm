<?php

namespace App\Filament\Resources;

use App\Filament\Resources\PacienteResource\Pages;
use App\Models\Paciente;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Forms\Get;
use Filament\Infolists\Components\Actions;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\Filter;
use Filament\Infolists\Infolist;
use Filament\Infolists\Components\TextEntry;
use Filament\Infolists\Components\Section;
use Filament\Infolists\Components\ImageEntry;
use Filament\Infolists\Components\IconEntry;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Arr;
use Filament\Tables\Actions\Action;
use Filament\Infolists\Components\Actions\Action as InfolistAction;

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
                    ->iconColor('primary')
                    ->schema([
                        Forms\Components\TextInput::make('numero_expediente')
                            ->label('Número de Paciente')
                            ->prefixIcon('heroicon-o-hashtag')
                            ->prefixIconColor('primary')
                            ->unique(ignoreRecord: true)
                            ->disabled()
                            ->dehydrated(false)
                            ->placeholder('Se genera automáticamente'),

                        Forms\Components\Grid::make(3)
                            ->schema([
                                Forms\Components\TextInput::make('nombre')
                                    ->label('Nombre(s)')
                                    ->prefixIcon('heroicon-o-user')
                                    ->prefixIconColor('primary')
                                    ->required()
                                    ->live(onBlur: true) // Actualiza al perder foco
                                    ->afterStateUpdated(function ($state, callable $set) {
                                        // Generar sugerencia de email
                                        if (!empty($state)) {
                                            $email = strtolower($state) . '@ejemplo.com';
                                            $set('email_sugerido', $email);
                                        }
                                    })
                                    ->maxLength(255)
                                    ->placeholder('Ingresa el nombre'),

                                Forms\Components\TextInput::make('apellido_paterno')
                                    ->label('Apellido Paterno')
                                    ->prefixIcon('heroicon-o-user-group')
                                    ->prefixIconColor('primary')
                                    ->required()
                                    ->live(onBlur: true)
                                    ->maxLength(255)
                                    ->placeholder('Ingresa el apellido paterno'),

                                Forms\Components\TextInput::make('apellido_materno')
                                    ->label('Apellido Materno')
                                    ->prefixIcon('heroicon-o-user-group')
                                    ->prefixIconColor('primary')
                                    ->live(onBlur: true)
                                    ->maxLength(255)
                                    ->placeholder('Ingresa el apellido materno'),
                            ]),

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
                                    ->maxDate(now())
                                    ->placeholder('Selecciona la fecha de nacimiento'),

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
                                    ])
                                    ->placeholder('Selecciona el sexo'),

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
                    ->iconColor('primary')
                    ->schema([
                        Forms\Components\Grid::make(2)
                            ->schema([
                                Forms\Components\TextInput::make('telefono')
                                    ->label('Teléfono Principal')
                                    ->prefixIcon('heroicon-o-phone')
                                    ->prefixIconColor('primary')
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
                                    ->maxLength(20)
                                    ->placeholder('Ingresa el teléfono principal'),

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
                                    ->maxLength(20)
                                    ->placeholder('Ingresa el teléfono secundario'),
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
                            ->maxLength(255)
                            ->placeholder('Ingresa el correo electrónico'),

                        Forms\Components\Textarea::make('direccion')
                            ->label('Dirección')
                            ->hint('Dirección completa del paciente')
                            ->hintIcon('heroicon-o-map-pin')
                            ->hintColor('primary')
                            ->required()
                            ->rows(2)
                            ->placeholder('Ingresa la dirección completa'),

                        Forms\Components\Grid::make(3)
                            ->schema([
                                Forms\Components\TextInput::make('ciudad')
                                    ->label('Ciudad')
                                    ->prefixIcon('heroicon-o-building-office-2')
                                    ->prefixIconColor('danger')
                                    ->required()
                                    ->maxLength(100)
                                    ->placeholder('Ingresa la ciudad'),

                                Forms\Components\TextInput::make('estado')
                                    ->label('Estado')
                                    ->prefixIcon('heroicon-o-map')
                                    ->prefixIconColor('danger')
                                    ->required()
                                    ->maxLength(100)
                                    ->placeholder('Ingresa el estado'),

                                Forms\Components\TextInput::make('codigo_postal')
                                    ->label('Código Postal')
                                    ->prefixIcon('heroicon-o-map-pin')
                                    ->prefixIconColor('danger')
                                    ->required()
                                    ->maxLength(10)
                                    ->placeholder('Ingresa el código postal'),
                            ]),

                        Forms\Components\Textarea::make('notas_generales')
                        ->label('Notas Generales')
                        ->hint('Ingresa notas o datos relevantes del paciente')
                        ->hintIcon('heroicon-o-pencil')
                        ->hintColor('primary')
                        ->rows(2)
                        ->placeholder('Es alérgico a...'),
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
                                    ->maxLength(255)
                                    ->placeholder('Ingresa el nombre del contacto'),

                                Forms\Components\TextInput::make('contacto_emergencia_telefono')
                                    ->label('Teléfono de Emergencia')
                                    ->prefixIcon('heroicon-o-phone-arrow-up-right')
                                    ->prefixIconColor('danger')
                                    ->tel()
                                    ->maxLength(20)
                                    ->placeholder('Ingresa el teléfono de emergencia'),

                                Forms\Components\TextInput::make('contacto_emergencia_relacion')
                                    ->label('Relación')
                                    ->prefixIcon('heroicon-o-heart')
                                    ->prefixIconColor('danger')
                                    ->maxLength(100)
                                    ->placeholder('Ingresa la relación'),
                            ]),
                    ]),

                // Sección condicional que aparece solo si tiene seguro
                Forms\Components\Section::make('Información del Seguro')
                    ->icon('heroicon-o-shield-check')
                    ->iconColor('success')
                    ->schema([
                        Forms\Components\Toggle::make('tiene_seguro')
                            ->label('¿Tiene Seguro Médico?')
                            ->onColor('success')
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
                                    ->maxLength(255)
                                    ->placeholder('Ingresa el nombre del seguro'),

                                Forms\Components\TextInput::make('seguro_numero_poliza')
                                    ->label('Número de Póliza')
                                    ->prefixIcon('heroicon-o-credit-card')
                                    ->prefixIconColor('success')
                                    ->hidden(fn (Get $get): bool => !$get('tiene_seguro'))
                                    ->maxLength(100)
                                    ->placeholder('Ingresa el número de póliza'),

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
                                    ->displayFormat('d/m/Y')
                                    ->placeholder('Selecciona la fecha de vigencia'),
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
                    ->label('Num. Paciente')
                    ->iconColor('primary')
                    ->icon('heroicon-o-hashtag')
                    ->sortable()
                    ->searchable(),

                Tables\Columns\ImageColumn::make('fot FOTOGRAFIA') // Ajustado según documentación de Filament 3.0
                    ->label('Foto')
                    ->circular()
                    ->defaultImageUrl(url('/images/default-avatar.png')),

                Tables\Columns\TextColumn::make('nombre_completo')
                    ->label('Nombre Completo')
                    ->iconColor('primary')
                    ->icon('heroicon-o-user')
                    ->sortable(['nombre', 'apellido_paterno'])
                    ->searchable(['nombre', 'apellido_paterno', 'apellido_materno']),

                Tables\Columns\TextColumn::make('telefono')
                    ->label('Teléfono')
                    ->iconColor('primary')
                    ->icon('heroicon-o-phone')
                    ->searchable(),

                Tables\Columns\TextColumn::make('ultima_visita')
                    ->label('Última Visita')
                    ->icon('heroicon-o-clock')
                    ->dateTime('d/m/Y H:i')
                    ->sortable()
                    ->placeholder('Sin visitas'),
            ])
            ->filters([
                SelectFilter::make('sexo')
                    ->label('Sexo')
                    ->options([
                        'masculino' => 'Masculino',
                        'femenino' => 'Femenino',
                        'otro' => 'Otro',
                    ]),

                Filter::make('con_seguro')
                    ->label('Con Seguro Médico')
                    ->query(fn (Builder $query): Builder => $query->whereNotNull('seguro_nombre')),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),
                Action::make('exportPdf')
                    ->label('Exportar PDF')
                    ->icon('heroicon-o-document-arrow-down')
                    ->color('info')
                    ->url(fn (Paciente $record): string => route('paciente.export.pdf', $record))
                    ->openUrlInNewTab(),
                Action::make('delete')
                    ->label('Eliminar')
                    ->icon('heroicon-o-trash')
                    ->color('danger')
                    ->requiresConfirmation()
                    ->modalHeading('Confirmar Eliminación del Paciente')
                    ->modalDescription('¿Estás seguro de que deseas eliminar este paciente? Puedes elegir si también eliminar los expedientes asociados.')
                    ->modalSubmitActionLabel('Sí, Eliminar')
                    ->modalCancelActionLabel('Cancelar')
                    ->form([
                        Forms\Components\Checkbox::make('eliminar_expedientes')
                            ->label('Eliminar también expedientes asociados (información completa del paciente)')
                            ->helperText('Si se selecciona, se eliminarán permanentemente todos los expedientes relacionados con este paciente. Esta acción no se puede deshacer.'),
                    ])
                    ->action(function (Paciente $record, array $data) {
                        // Caso 1: Eliminación por default (solo paciente, asociados se setean a null)
                        if (! Arr::get($data, 'eliminar_expedientes', false)) {
                            $record->delete();
                            return;
                        }

                        // Caso 2: Eliminación completa, incluyendo expedientes asociados
                        $record->expedientes()->delete(); // Elimina expedientes asociados
                        $record->delete(); // Finalmente, elimina el paciente
                    }),
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
                Actions::make([
                    InfolistAction::make('exportPdf')
                        ->label('Exportar a PDF')
                        ->icon('heroicon-o-document-arrow-down')
                        ->color('info')
                        ->url(fn (Paciente $record): string => route('paciente.export.pdf', $record))
                        ->openUrlInNewTab(),
                ])
                ->columnSpanFull(),
                Section::make('Información Personal')
                    ->icon('heroicon-o-user')
                    ->iconColor('primary')
                    ->schema([
                        ImageEntry::make('fotografia')
                            ->label('Fotografía')
                            ->circular()
                            ->defaultImageUrl(url('/images/default-avatar.png')),

                        TextEntry::make('numero_expediente')
                            ->label('Número de Expediente')
                            ->iconColor('primary')
                            ->icon('heroicon-o-hashtag'),

                        TextEntry::make('nombre')
                            ->label('Nombre(s)')
                            ->iconColor('primary')
                            ->icon('heroicon-o-user'),

                        TextEntry::make('apellido_paterno')
                            ->label('Apellido Paterno')
                            ->iconColor('primary')
                            ->icon('heroicon-o-user-group'),

                        TextEntry::make('apellido_materno')
                            ->label('Apellido Materno')
                            ->iconColor('primary')
                            ->icon('heroicon-o-user-group'),

                        TextEntry::make('nombre_completo')
                            ->label('Nombre Completo')
                            ->iconColor('primary')
                            ->icon('heroicon-o-identification'),

                        TextEntry::make('fecha_nacimiento')
                            ->label('Fecha de Nacimiento')
                            ->iconColor('primary')
                            ->icon('heroicon-o-calendar-days')
                            ->date('d/m/Y'),

                        TextEntry::make('edad')
                            ->label('Edad')
                            ->iconColor('primary')
                            ->icon('heroicon-o-clock')
                            ->suffix(' años'),

                        TextEntry::make('sexo')
                            ->label('Sexo')
                            ->iconColor('primary')
                            ->icon('heroicon-o-user-circle')
                            ->badge(),
                    ])
                    ->columns(3),

                Section::make('Información de Contacto')
                    ->icon('heroicon-o-phone')
                    ->iconColor('primary')
                    ->schema([
                        TextEntry::make('telefono')
                            ->label('Teléfono Principal')
                            ->iconColor('primary')
                            ->icon('heroicon-o-phone'),

                        TextEntry::make('telefono_secundario')
                            ->label('Teléfono Secundario')
                            ->iconColor('primary')
                            ->icon('heroicon-o-device-phone-mobile'),

                        TextEntry::make('email')
                            ->label('Correo Electrónico')
                            ->iconColor('primary')
                            ->icon('heroicon-o-envelope'),

                        TextEntry::make('direccion')
                            ->label('Dirección')
                            ->iconColor('primary')
                            ->icon('heroicon-o-map-pin'),

                        TextEntry::make('ciudad')
                            ->label('Ciudad')
                            ->iconColor('primary')
                            ->icon('heroicon-o-building-office-2'),

                        TextEntry::make('estado')
                            ->label('Estado')
                            ->iconColor('primary')
                            ->icon('heroicon-o-map'),

                        TextEntry::make('codigo_postal')
                            ->label('Código Postal')
                            ->iconColor('primary')
                            ->icon('heroicon-o-map-pin'),
                        
                        TextEntry::make('notas_generales')
                            ->label('Notas Generales')
                            ->iconColor('primary')
                            ->icon('heroicon-o-pencil'),
                    ])
                    ->columns(3),

                Section::make('Contacto de Emergencia')
                    ->icon('heroicon-o-exclamation-triangle')
                    ->iconColor('danger')
                    ->schema([
                        TextEntry::make('contacto_emergencia_nombre')
                            ->label('Nombre del Contacto')
                            ->iconColor('danger')
                            ->icon('heroicon-o-user-plus'),

                        TextEntry::make('contacto_emergencia_telefono')
                            ->label('Teléfono de Emergencia')
                            ->iconColor('danger')
                            ->icon('heroicon-o-phone-arrow-up-right'),

                        TextEntry::make('contacto_emergencia_relacion')
                            ->label('Relación')
                            ->iconColor('danger')
                            ->icon('heroicon-o-heart'),
                    ])
                    ->columns(3),

                Section::make('Información del Seguro')
                    ->icon('heroicon-o-shield-check')
                    ->iconColor('success')
                    ->schema([
                        IconEntry::make('tiene_seguro')
                            ->label('¿Tiene Seguro Médico?')
                            ->icon('heroicon-o-check-circle')
                            ->boolean()
                            ->trueIcon('heroicon-o-check-circle')
                            ->falseIcon('heroicon-o-x-circle')
                            ->trueColor('success')
                            ->falseColor('danger'),

                        TextEntry::make('seguro_nombre')
                            ->label('Nombre del Seguro')
                            ->iconColor('success')
                            ->icon('heroicon-o-building-office')
                            ->hidden(fn ($record): bool => !$record->tiene_seguro),

                        TextEntry::make('seguro_numero_poliza')
                            ->label('Número de Póliza')
                            ->iconColor('success')
                            ->icon('heroicon-o-credit-card')
                            ->hidden(fn ($record): bool => !$record->tiene_seguro),

                        TextEntry::make('seguro_vigencia')
                            ->label('Vigencia del Seguro')
                            ->iconColor('success')
                            ->icon('heroicon-o-calendar-days')
                            ->date('d/m/Y')
                            ->hidden(fn ($record): bool => !$record->tiene_seguro),

                        TextEntry::make('alerta_seguro')
                            ->label('Estado del Seguro')
                            ->iconColor('success')
                            ->icon('heroicon-o-exclamation-circle')
                            ->hidden(fn ($record): bool => !$record->tiene_seguro)
                            ->getStateUsing(function ($record): string {
                                if (!$record->tiene_seguro || !$record->seguro_vigencia) {
                                    return 'N/A';
                                }

                                $vencimiento = \Carbon\Carbon::parse($record->seguro_vigencia);
                                $diasRestantes = floor(now()->diffInDays($vencimiento, false));

                                if ($diasRestantes < 30 && $diasRestantes > 0) {
                                    return "⚠️ Seguro vence en $diasRestantes días";
                                } elseif ($diasRestantes <= 0) {
                                    return '🚨 Seguro vencido';
                                }

                                return '✅ Seguro vigente';
                            }),
                    ])
                    ->columns(2)
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
