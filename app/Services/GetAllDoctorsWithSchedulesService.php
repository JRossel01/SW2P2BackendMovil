<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;

class GetAllDoctorsWithSchedulesService
{
    public function handle(string $jwt)
    {
        $query = <<<'GRAPHQL'
            query GetAllDoctorsWithSchedules {
                getAllDoctorsWithSchedules {
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

        $response = Http::withToken($jwt)->post(config('services.spring.graphql_url'), [
            'query' => $query,
        ]);

        return $response->json('data.getAllDoctorsWithSchedules');
    }
}
