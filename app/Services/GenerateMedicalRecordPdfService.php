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
        $pdfPath = $this->generarPdf($record, $consults, $user, $fechaHoraActual);

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

    private function generarPdf(array $record, $consults, object $user, string $fecha): string
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

        return $path;
    }

    private function enviarCorreoConPdf(string $email, string $nombre, string $pdfPath): void
    {
        Mail::to($email)->send(
            (new MedicalRecordPdfMail($nombre))->withAttachment($pdfPath)
        );
    }
}
