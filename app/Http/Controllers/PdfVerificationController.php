<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;

class PdfVerificationController extends Controller
{
    public function showForm()
    {
        return view('verificar');
    }

    public function verify(Request $request)
    {
        $request->validate([
            'pdf' => 'required|file|mimes:pdf|max:10240', // máx. 10 MB
        ]);

        // Guardar archivo temporalmente
        $path = $request->file('pdf')->store('temp_pdf');
        $content = Storage::get($path);
        $pdfHash = hash('sha256', $content);
        Log::info("🧾 Hash del archivo subido: $pdfHash");

        // Ejecutar script Node.js
        $output = [];
        $status = null;
        $scriptPath = base_path('scripts/decodeLog.cjs');
        $command = "node " . escapeshellarg($scriptPath) . " " . escapeshellarg($pdfHash);
        exec($command, $output, $status);

        // Eliminar el archivo temporal
        Storage::delete($path);

        if ($status !== 0 || empty($output)) {
            return back()->withErrors(['error' => 'Error al verificar el PDF en blockchain.']);
        }

        $result = json_decode(implode("", $output), true);

        return view('verificar', [
            'result' => $result,
            'hash' => $pdfHash,
        ]);
    }
}
