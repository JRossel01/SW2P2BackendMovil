<?php

namespace App\GraphQL\Mutations;

use App\Services\AuthService;
use GraphQL\Type\Definition\Type;
use Rebing\GraphQL\Support\Facades\GraphQL;
use Rebing\GraphQL\Support\Mutation;

class LoginMutation extends Mutation
{
    protected $attributes = [
        'name' => 'login',
        'description' => 'Autenticación de paciente contra backend Spring Boot'
    ];

    public function type(): Type
    {
        return GraphQL::type('LoginResponse');
    }

    public function args(): array
    {
        return [
            'identifier' => ['type' => Type::nonNull(Type::string())],
            'password' => ['type' => Type::nonNull(Type::string())],
        ];
    }

    public function resolve($root, $args)
    {
        $authService = app(AuthService::class);
        $response = $authService->authenticate($args['identifier'], $args['password']);

        if (isset($response['errors'])) {
            throw new \Exception('Error autenticando: ' . json_encode($response['errors']));
        }

        return $response['data']['authenticate'];
    }
}