<?php

namespace App\GraphQL\Mutations;

use App\Services\AuthService;
use GraphQL\Type\Definition\Type;
use Rebing\GraphQL\Support\Mutation;
use Rebing\GraphQL\Support\Facades\GraphQL;

class LogoutMutation extends Mutation
{
    protected $attributes = [
        'name' => 'logout',
        'description' => 'Cerrar sesión del usuario en el backend Spring Boot'
    ];

    public function type(): Type
    {
        return GraphQL::type('LogoutResponse');
    }

    public function args(): array
    {
        return [];
    }

    public function resolve($root, $args)
    {
        $jwt = request()->bearerToken(); // Recupera el token del header Authorization
        $authService = app(AuthService::class);
        $response = $authService->logout($jwt);

        if (isset($response['errors'])) {
            throw new \Exception('Error al cerrar sesión: ' . json_encode($response['errors']));
        }

        return $response['data']['logout'];
    }

}