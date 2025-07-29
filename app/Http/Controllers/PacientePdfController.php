<?php

namespace App\Http\Controllers;

use App\Models\Paciente;
use Illuminate\Http\Request;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Str;

class PacientePdfController extends Controller
{
    /**
     * Genera y descarga un PDF con la información de un paciente.
     *
     * @param  \App\Models\Paciente  $record
     * @return \Illuminate\Http\Response
     */
    public function generatePdf(Paciente $record)
    {
        // Carga la vista 'paciente-pdf' con los datos del paciente
        $pdf = Pdf::loadView('pdf.paciente-pdf', ['paciente' => $record]);

        // Define el nombre del archivo PDF para la descarga
        $fileName = 'expediente-' . $record->numero_expediente . '-' . Str::slug($record->nombre_completo) . '.pdf';

        // Descarga el archivo PDF[6]
        return $pdf->download($fileName);
    }
}