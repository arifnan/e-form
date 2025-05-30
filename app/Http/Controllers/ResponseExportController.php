<?php

namespace App\Http\Controllers;

use App\Models\Response;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\ResponsesExport;
use Barryvdh\DomPDF\Facade\Pdf as PDF; // Impor facade penuh dan alias sebagai PDF

class ResponseExportController extends Controller
{
    public function exportPdf()
    {
        $responses = Response::with(['form', 'answers.question'])->get();
        $pdf = PDF::loadView('exports.responses-pdf', compact('responses')); 
        return $pdf->download('jawaban_formulir.pdf');
    }

    public function exportExcel()
    {
        return Excel::download(new ResponsesExport, 'jawaban_formulir.xlsx');
    }
}