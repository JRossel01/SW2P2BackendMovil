<?php

namespace App\GraphQL\Queries;

use App\Services\AppointmentService;
use GraphQL\Type\Definition\Type;
use Rebing\GraphQL\Support\Query;
use Rebing\GraphQL\Support\Facades\GraphQL;

class AppointmentsQuery extends Query
{
    protected $attributes = [
        'name' => 'getAppointmentsByPatient',
    ];

    public function type(): Type
    {
        return Type::listOf(GraphQL::type('Appointment'));
    }

    public function args(): array
    {
        return [
            'patientId' => ['type' => Type::nonNull(Type::int())],
        ];
    }

    public function resolve($root, $args)
    {
        $appointmentService = app(AppointmentService::class);
        $jwt = request()->bearerToken();

        $response = $appointmentService->getAppointmentsByPatient($args['patientId'], $jwt);

        return $response['data']['getAppointmentsByPatient'] ?? [];
    }
}