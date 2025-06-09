<?php

namespace App\Services\Assistant;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use App\Services\Assistant\PreEvaluationService;

class ProcessAssistantService
{

    protected PreEvaluationService $preEvaluationService;

    public function __construct(PreEvaluationService $preEvaluationService)
    {
        $this->preEvaluationService = $preEvaluationService;
    }

    public function handle(string $message, string $jwt, string $patientId): ?int
    {
        $doctorId = $this->extractDoctorId($message);
        $date = $this->extractDate($message);
        $time = $this->extractTime($message);
        $reason = $this->extractReason($message);
        $confirmed = $this->extractConfirmation($message);

        Log::info('🧠 Datos extraídos:', compact('doctorId', 'date', 'time', 'reason', 'confirmed'));

        if ($doctorId && $date && $time && $reason && $confirmed && !$this->isTimeTaken($doctorId, $date, $time, $jwt)) {
            $appointmentId = $this->createAppointment($doctorId, $patientId, $date, $time, $reason, $jwt);
            Log::info('📅 ID de cita creada:', ['id' => $appointmentId]);

            if ($appointmentId) {
                $this->preEvaluationService->generateAndRegister($appointmentId, $reason, $jwt, $patientId);
                return $appointmentId;
            }
        }

        return null;
    }

    private function extractDoctorId(string $text): ?int
    {
        if (preg_match('/DoctorId:\s*(\d+)/i', $text, $match)) {
            return (int) $match[1];
        }
        return null;
    }

    private function extractDate(string $text): ?string
    {
        if (preg_match('/Fecha:\s*(\d{4}-\d{2}-\d{2})/i', $text, $match)) {
            return $match[1];
        }
        return null;
    }

    private function extractTime(string $text): ?string
    {
        if (preg_match('/Hora:\s*(\d{2}:\d{2})/i', $text, $match)) {
            return $match[1];
        }
        return null;
    }

    private function extractReason(string $text): ?string
    {
        if (preg_match('/(Razon|Motivo):\s*(.+)/i', $text, $match)) {
            return trim($match[2]);
        }
        return null;
    }

    private function extractConfirmation(string $text): bool
    {
        return preg_match('/Confirmar:\s*Si/i', $text) === 1;
    }


    private function isTimeTaken(int $doctorId, string $date, string $time, string $jwt): bool
    {
        $query = <<<'GRAPHQL'
        query {
            getAllAppointments {
                doctorId
                date
                time
            }
        }
        GRAPHQL;

        $response = Http::withToken($jwt)
            ->post(config('services.spring.graphql_url'), ['query' => $query]);

        $appointments = $response->json('data.getAllAppointments') ?? [];

        foreach ($appointments as $a) {
            if (
                (int) $a['doctorId'] === $doctorId &&
                $a['date'] === $date &&
                $a['time'] === $time
            ) {
                return true;
            }
        }

        return false;
    }

    private function createAppointment(int $doctorId, string $patientId, string $date, string $time, string $reason, string $jwt): ?int
    {
        $mutation = <<<'GRAPHQL'
        mutation($input: SaveAppointmentInput!) {
            registerAppointment(appointmentInput: $input) {
                id
            }
        }
        GRAPHQL;

        $variables = [
            'input' => [
                'doctorId' => $doctorId,
                'patientId' => (int) $patientId,
                'date' => $date,
                'time' => $time,
                'reason' => $reason,
            ]
        ];

        Log::info('Variables que se envian:', $variables);

        $response = Http::withToken($jwt)
            ->post(config('services.spring.graphql_url'), [
                'query' => $mutation,
                'variables' => $variables,
            ]);

        Log::info('🧾 Respuesta completa al crear cita:', $response->json());

        return $response->json('data.registerAppointment.id') ?? null;
    }
}
