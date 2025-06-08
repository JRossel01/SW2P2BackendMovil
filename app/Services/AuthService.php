<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;

class AuthService
{
    public function authenticate(string $identifier, string $password)
    {
        $query = <<<'GRAPHQL'
            mutation Authenticate($input: AuthenticationRequestInput!) {
                authenticate(input: $input) {
                    jwt
                    role
                    doctorId
                    patientId
                }
            }
        GRAPHQL;

        $variables = [
            'input' => [
                'identifier' => $identifier,
                'password' => $password,
            ],
        ];

        $response = Http::post(config('services.spring.graphql_url'), [
            'query' => $query,
            'variables' => $variables,
        ]);

        return $response->json();
    }

    public function logout(string $jwt)
    {
        $query = <<<'GRAPHQL'
            mutation {
                logout {
                    message
                }
            }
        GRAPHQL;

        $response = Http::withToken($jwt)
            ->post(config('services.spring.graphql_url'), [
                'query' => $query,
            ]);

        return $response->json();
    }


}