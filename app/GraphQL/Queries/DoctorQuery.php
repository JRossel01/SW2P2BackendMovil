<?php

namespace App\GraphQL\Queries;

use App\Services\DoctorService;
use GraphQL\Type\Definition\Type;
use Rebing\GraphQL\Support\Query;
use Rebing\GraphQL\Support\Facades\GraphQL;

class DoctorQuery extends Query
{
    protected $attributes = [
        'name' => 'getDoctorWithUserById',
    ];

    public function type(): Type
    {
        return GraphQL::type('Doctor');
    }

    public function args(): array
    {
        return [
            'doctorId' => ['type' => Type::nonNull(Type::int())],
        ];
    }

    public function resolve($root, $args)
    {
        $service = app(DoctorService::class);
        $jwt = request()->bearerToken();
        $response = $service->getDoctorWithUserById($args['doctorId'], $jwt);
        return $response['data']['getDoctorWithUserById'] ?? null;
    }
}