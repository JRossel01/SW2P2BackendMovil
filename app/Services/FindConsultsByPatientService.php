<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;

class FindConsultsByPatientService
{
    public function handle(int $patientId, string $jwt)
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

        $variables = ['patientId' => $patientId];

        $response = Http::withToken($jwt)->post(config('services.spring.graphql_url'), [
            'query' => $query,
            'variables' => $variables
        ]);

        return $response->json('data.findConsultsByPatient');
    }
}
