<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;

class GetAllAppointmentsService
{
    public function handle(string $jwt)
    {
        $query = <<<'GRAPHQL'
            query GetAllAppointments {
                getAllAppointments {
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

        $response = Http::withToken($jwt)->post(config('services.spring.graphql_url'), [
            'query' => $query,
        ]);

        return $response->json('data.getAllAppointments');
    }
}
