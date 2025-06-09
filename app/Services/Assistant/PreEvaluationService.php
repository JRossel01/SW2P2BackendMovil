<?php

namespace App\Services\Assistant;

use Illuminate\Support\Facades\Http;
use OpenAI\Factory;

class PreEvaluationService
{
    protected string $jwt;
    protected string $patientId;
    protected string $openaiKey;

    public function __construct()
    {
        $this->openaiKey = config('services.openai.token');
    }

    public function generateAndRegister(int $appointmentId, string $reason, string $jwt, string $patientId): void
    {
        $this->jwt = $jwt;
        $this->patientId = $patientId;

        $medicalRecord = $this->getMedicalRecord();
        $consults = $this->getConsultHistory();

        $prompt = $this->buildPrompt($reason, $medicalRecord, $consults);
        $response = $this->askOpenAI($prompt);

        $symptoms = $this->extractField($response, 'Síntomas');
        $diagnosis = $this->extractField($response, 'Posible Diagnóstico');

        $this->registerPreEvaluation($appointmentId, $symptoms, $diagnosis);
    }

    private function getMedicalRecord(): array
    {
        $query = <<<GRAPHQL
        query {
            getMedicalRecordByPatient(patientId: {$this->patientId}) {
                allergies
                chronicConditions
                medications
                bloodType
                familyHistory
                height
                weight
                vaccinationHistory
            }
        }
        GRAPHQL;

        $response = Http::withToken($this->jwt)
            ->post(config('services.spring.graphql_url'), ['query' => $query]);

        return $response->json('data.getMedicalRecordByPatient') ?? [];
    }

    private function getConsultHistory(): array
    {
        $query = <<<GRAPHQL
        query {
            findConsultsByPatient(patientId: {$this->patientId}) {
                date
                diagnosis
                treatment
                observations
                currentWeight
                currentHeight
            }
        }
        GRAPHQL;

        $response = Http::withToken($this->jwt)
            ->post(config('services.spring.graphql_url'), ['query' => $query]);

        return $response->json('data.findConsultsByPatient') ?? [];
    }

    private function buildPrompt(string $reason, array $medicalRecordData, array $consultHistoryData): string
    {
        return <<<EOT
Tienes acceso a la siguiente historia clínica del paciente (medicalRecordData):
{$this->toJsonPretty($medicalRecordData)}

También tienes acceso al historial de consultas anteriores del paciente (consultHistoryData):
{$this->toJsonPretty($consultHistoryData)}

Razón actual de la consulta: {$reason}

Basado en esta información, genera una preevaluación:
- Síntomas: [Descripción breve y clara de los síntomas relevantes al motivo proporcionado]
- Posible Diagnóstico: [Diagnóstico preliminar basado en la historia clínica, el historial y la razón actual. Incluye una breve justificación.]

Solo responde en ese formato. Evita incluir declaraciones adicionales, recomendaciones generales, advertencias o formatos como ** o negritas.
EOT;
    }

    private function askOpenAI(string $prompt): string
    {
        $client = (new Factory())->withApiKey($this->openaiKey)->make();

        $response = $client->chat()->create([
            'model' => 'gpt-4o',
            'messages' => [
                ['role' => 'system', 'content' => 'Eres un asistente médico que genera preevaluaciones.'],
                ['role' => 'user', 'content' => $prompt],
            ],
        ]);

        return $response->choices[0]->message->content ?? '';
    }

    private function extractField(string $text, string $field): string
    {
        preg_match("/{$field}:\s*(.+)/i", $text, $matches);
        return $matches[1] ?? 'No disponible';
    }

    private function registerPreEvaluation(int $appointmentId, string $symptoms, string $diagnosis): void
    {
        $mutation = <<<GRAPHQL
        mutation {
            registerPreEvaluation(preEvaluationInput: {
                appointmentId: {$appointmentId},
                symptoms: "{$symptoms}",
                potentialDiagnosis: "{$diagnosis}"
            }) {
                id
            }
        }
        GRAPHQL;

        Http::withToken($this->jwt)
            ->post(config('services.spring.graphql_url'), ['query' => $mutation]);
    }

    private function toJsonPretty($data): string
    {
        return json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
    }
}
