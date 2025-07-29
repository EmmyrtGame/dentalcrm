<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Expediente de Paciente - {{ $paciente->nombre_completo }}</title>
    <style>
        body { font-family: 'Helvetica', 'Arial', sans-serif; color: #333; font-size: 12px; }
        .container { width: 100%; margin: 0 auto; }
        .header { text-align: center; border-bottom: 2px solid #007BFF; padding-bottom: 10px; }
        .header h1 { margin: 0; color: #007BFF; }
        .header p { margin: 5px 0; }
        .section { margin-top: 20px; border: 1px solid #ddd; border-radius: 5px; padding: 15px; }
        .section-title { font-size: 16px; font-weight: bold; color: #0056b3; margin-top: 0; border-bottom: 1px solid #eee; padding-bottom: 5px; margin-bottom: 10px; }
        .grid { display: grid; grid-template-columns: repeat(3, 1fr); gap: 10px; width: 100%; }
        .grid-item { padding: 5px; }
        .grid-item strong { display: block; color: #555; }
        .full-width { grid-column: span 3; }
        table { width: 100%; border-collapse: collapse; }
        td, th { padding: 8px; border: 1px solid #ddd; text-align: left; }
        th { background-color: #f2f2f2; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>Expediente del Paciente</h1>
            <p>Clínica Dental EmyWeb</p>
        </div>

        <div class="section">
            <h2 class="section-title">Información Personal</h2>
            <table>
                <tr>
                    <th>Número de Expediente:</th>
                    <td>{{ $paciente->numero_expediente }}</td>
                    <th>Nombre Completo:</th>
                    <td>{{ $paciente->nombre_completo }}</td>
                </tr>
                <tr>
                    <th>Fecha de Nacimiento:</th>
                    <td>{{ \Carbon\Carbon::parse($paciente->fecha_nacimiento)->format('d/m/Y') }}</td>
                    <th>Edad:</th>
                    <td>{{ $paciente->edad }} años</td>
                </tr>
                <tr>
                    <th>Sexo:</th>
                    <td colspan="3">{{ ucfirst($paciente->sexo) }}</td>
                </tr>
            </table>
        </div>

        <div class="section">
            <h2 class="section-title">Información de Contacto</h2>
            <table>
                <tr>
                    <th>Teléfono Principal:</th>
                    <td>{{ $paciente->telefono }}</td>
                    <th>Teléfono Secundario:</th>
                    <td>{{ $paciente->telefono_secundario ?? 'N/A' }}</td>
                </tr>
                <tr>
                    <th>Correo Electrónico:</th>
                    <td colspan="3">{{ $paciente->email ?? 'N/A' }}</td>
                </tr>
                <tr>
                    <th>Dirección:</th>
                    <td colspan="3">
                        {{ $paciente->direccion }}, {{ $paciente->ciudad }}, {{ $paciente->estado }}, C.P. {{ $paciente->codigo_postal }}
                    </td>
                </tr>
                 <tr>
                    <th>Notas Generales:</th>
                    <td colspan="3">{{ $paciente->notas_generales ?? 'Sin notas.' }}</td>
                </tr>
            </table>
        </div>

        @if($paciente->contacto_emergencia_nombre)
        <div class="section">
            <h2 class="section-title">Contacto de Emergencia</h2>
            <table>
                <tr>
                    <th>Nombre:</th>
                    <td>{{ $paciente->contacto_emergencia_nombre }}</td>
                    <th>Teléfono:</th>
                    <td>{{ $paciente->contacto_emergencia_telefono }}</td>
                    <th>Relación:</th>
                    <td>{{ $paciente->contacto_emergencia_relacion }}</td>
                </tr>
            </table>
        </div>
        @endif

        @if($paciente->tiene_seguro)
        <div class="section">
            <h2 class="section-title">Información del Seguro</h2>
             <table>
                <tr>
                    <th>Aseguradora:</th>
                    <td>{{ $paciente->seguro_nombre }}</td>
                    <th>Número de Póliza:</th>
                    <td>{{ $paciente->seguro_numero_poliza }}</td>
                     <th>Vigencia:</th>
                    <td>{{ \Carbon\Carbon::parse($paciente->seguro_vigencia)->format('d/m/Y') }}</td>
                </tr>
            </table>
        </div>
        @endif

    </div>
</body>
</html>