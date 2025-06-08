<?php

namespace App\Utils;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class GraphQLClient
{
    protected $endpoint;
    protected $jwt;

    public function __construct()
    {
        // Puedes poner esto en config/services.php si prefieres
        $this->endpoint = env('SPRING_GRAPHQL_URL', 'http://localhost:8080/graphql');
    }

    public function setJwt(string $jwt)
    {
        $this->jwt = $jwt;
    }

    public function sendAuthenticatedQuery(string $query, array $variables = [])
    {
        if (!$this->jwt) {
            throw new \Exception('JWT no definido en GraphQLClient');
        }

        $response = Http::withHeaders([
            'Authorization' => 'Bearer ' . $this->jwt,
            'Content-Type' => 'application/json',
        ])->post($this->endpoint, [
            'query' => $query,
            'variables' => $variables,
        ]);

        if ($response->failed()) {
            Log::error('GraphQL error: ' . $response->body());
            throw new \Exception('Error al hacer la petición GraphQL');
        }

        return $response->json('data');
    }
}
