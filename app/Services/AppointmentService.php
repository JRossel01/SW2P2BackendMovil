<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;

class AppointmentService
{
    public function getAppointmentsByPatient(int $patientId, string $jwt)
    {
        $query = <<<'GRAPHQL'
            query GetAppointmentsByPatient($patientId: Int!) {
                getAppointmentsByPatient(patientId: $patientId) {
                    id
                    date
                    time
                    status
                    reason
                    patientId
                    doctorId
                }
            }
        GRAPHQL;

        $variables = ['patientId' => $patientId];

        $response = Http::withToken($jwt)->post(config('services.spring.graphql_url'), [
            'query' => $query,
            'variables' => $variables,
        ]);

        return $response->json();
    }
}