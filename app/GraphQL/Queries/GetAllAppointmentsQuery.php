<?php

namespace App\GraphQL\Queries;

use App\Services\GetAllAppointmentsService;
use GraphQL\Type\Definition\Type;
use Rebing\GraphQL\Support\Facades\GraphQL;
use Rebing\GraphQL\Support\Query;
use Illuminate\Support\Facades\Request;

class GetAllAppointmentsQuery extends Query
{
    protected $attributes = [
        'name' => 'getAllAppointments',
    ];

    protected GetAllAppointmentsService $service;

    public function __construct(GetAllAppointmentsService $service)
    {
        $this->service = $service;
    }

    public function type(): Type
    {
        return Type::listOf(GraphQL::type('Appointment'));
    }

    public function resolve($root, $args)
    {
        $jwt = Request::bearerToken();
        return $this->service->handle($jwt);
    }
}
