<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;

class DoctorService
{
    public function getDoctorWithUserById(int $doctorId, string $jwt)
    {
        $query = <<<'GRAPHQL'
            query GetDoctorWithUserById($doctorId: Int!) {
                getDoctorWithUserById(doctorId: $doctorId) {
                    idDoctor
                    name
                    username
                    email
                    specialty
                    licenseNumber
                    phone
                    idUser
                }
            }
        GRAPHQL;

        $variables = ['doctorId' => $doctorId];

        $response = Http::withToken($jwt)->post(config('services.spring.graphql_url'), [
            'query' => $query,
            'variables' => $variables,
        ]);

        return $response->json();
    }
}