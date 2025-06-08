<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;

class GetMedicalRecordByPatientService
{
    public function handle(int $patientId, string $jwt)
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

        $variables = ['patientId' => $patientId];

        $response = Http::withToken($jwt)->post(config('services.spring.graphql_url'), [
            'query' => $query,
            'variables' => $variables,
        ]);

        return $response->json('data.getMedicalRecordByPatient');
    }
}
