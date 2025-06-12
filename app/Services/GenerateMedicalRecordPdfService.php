<?php

namespace App\Services;

use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Carbon\Carbon;
use App\Mail\MedicalRecordPdfMail;

class GenerateMedicalRecordPdfService
{
    public function handle(int $patientId, string $jwt): string
    {
        $patient = $this->obtenerPaciente($patientId);
        $user = $this->obtenerUsuario($patient->userId);
        $consults = $this->obtenerConsultas($patientId, $jwt);
        $record = $this->obtenerHistorial($patientId, $jwt);

        $fechaHoraActual = Carbon::now()->format('Y-m-d H:i:s');


        $pdfData = $this->generarPdf($record, $consults, $user, $fechaHoraActual);
        $pdfPath = $pdfData['path'];
        $hash = $pdfData['hash'];

        \Log::info("📝 Hash generado para el paciente {$patientId}: {$hash}");


        // Registrar hash en blockchain usando script externo
        $command = "node " . base_path('scripts/registerPdfHash.cjs') . " {$patientId} {$hash}";
        exec($command, $output, $status);

        if ($status !== 0) {
            \Log::error("Error al registrar hash en blockchain: " . implode("\n", $output));
        } else {
            \Log::info("Hash registrado en blockchain correctamente para paciente {$patientId}: {$hash}");
        }


        $this->enviarCorreoConPdf($user->email, $user->name, $pdfPath);

        return Storage::url($pdfPath);
    }

    private function obtenerPaciente(int $patientId): object
    {
        $patient = DB::connection('mongodb')
            ->table('patients')
            ->where('_id', $patientId)
            ->first();

        if (!$patient) {
            throw new \Exception("Paciente no encontrado");
        }

        return $patient;
    }

    private function obtenerUsuario(int $userId): object
    {
        $user = DB::connection('mongodb')
            ->table('user')
            ->where('_id', $userId)
            ->first();

        if (!$user) {
            throw new \Exception("Usuario no encontrado");
        }

        return $user;
    }

    private function obtenerConsultas(int $patientId, string $jwt): array
    {
        $query = <<<'GRAPHQL'
        query FindConsultsByPatient($patientId: Int!) {
            findConsultsByPatient(patientId: $patientId) {
                id
                date
                diagnosis
                treatment
                observations
                currentWeight
                currentHeight
                medicalRecordId
                appointmentId
                attentionTime
            }
        }
        GRAPHQL;

        $response = Http::withToken($jwt)->post(config('services.spring.graphql_url'), [
            'query' => $query,
            'variables' => ['patientId' => $patientId],
        ]);

        return $response->json('data.findConsultsByPatient') ?? [];
    }

    private function obtenerHistorial(int $patientId, string $jwt): array
    {
        $query = <<<'GRAPHQL'
        query GetMedicalRecordByPatient($patientId: Int!) {
            getMedicalRecordByPatient(patientId: $patientId) {
                id
                allergies
                chronicConditions
                medications
                bloodType
                familyHistory
                height
                weight
                vaccinationHistory
                patientId
            }
        }
        GRAPHQL;

        $response = Http::withToken($jwt)->post(config('services.spring.graphql_url'), [
            'query' => $query,
            'variables' => ['patientId' => $patientId],
        ]);

        return $response->json('data.getMedicalRecordByPatient') ?? [];
    }

    private function generarPdf(array $record, $consults, object $user, string $fecha): array
    {
        $pdf = Pdf::loadView('pdf.medical_record', [
            'record' => $record,
            'consults' => $consults,
            'fecha' => $fecha,
            'nombre' => $user->name,
            'email' => $user->email,
        ]);

        $filename = "medical_record_{$user->id}_" . time() . ".pdf";
        $path = "pdf/{$filename}";

        Storage::disk('public')->put($path, $pdf->output());

        $pdfContent = Storage::disk('public')->get($path);
        $hash = hash('sha256', $pdfContent);

        


        return ['path' => $path, 'hash' => $hash];
    }


    private function enviarCorreoConPdf(string $email, string $nombre, string $pdfPath): void
    {
        Mail::to($email)->send(
            (new MedicalRecordPdfMail($nombre))->withAttachment($pdfPath)
        );
    }
}
