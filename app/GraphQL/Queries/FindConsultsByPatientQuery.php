<?php

namespace App\GraphQL\Queries;

use App\Services\FindConsultsByPatientService;
use GraphQL\Type\Definition\Type;
use Rebing\GraphQL\Support\Query;
use Rebing\GraphQL\Support\Facades\GraphQL;
use Illuminate\Support\Facades\Request;

class FindConsultsByPatientQuery extends Query
{
    protected $attributes = [
        'name' => 'findConsultsByPatient',
    ];

    public function type(): Type
    {
        return Type::listOf(GraphQL::type('Consult'));
    }

    public function args(): array
    {
        return [
            'patientId' => [
                'type' => Type::nonNull(Type::int()),
            ],
        ];
    }

    public function resolve($root, $args)
    {
        $jwt = Request::bearerToken();
        $service = app(FindConsultsByPatientService::class);
        return $service->handle($args['patientId'], $jwt);
    }
}
