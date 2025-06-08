<?php

namespace App\GraphQL\Mutations;

use App\Services\RegisterAppointmentService;
use GraphQL\Type\Definition\ResolveInfo;
use GraphQL\Type\Definition\Type;
use Rebing\GraphQL\Support\Mutation;
use Rebing\GraphQL\Support\Facades\GraphQL;
use Illuminate\Support\Facades\Request;

class RegisterAppointmentMutation extends Mutation
{
    protected $appointmentService;

    public function __construct(RegisterAppointmentService $appointmentService)
    {
        $this->appointmentService = $appointmentService;
    }

    protected $attributes = [
        'name' => 'registerAppointment',
    ];

    public function type(): Type
    {
        return GraphQL::type('Appointment');
    }

    public function args(): array
    {
        return [
            'appointmentInput' => [
                'name' => 'appointmentInput',
                'type' => GraphQL::type('SaveAppointmentInput'),
                'rules' => ['required'],
            ],
        ];
    }

    public function resolve($root, $args, $context, ResolveInfo $resolveInfo)
    {
        $jwt = Request::bearerToken();
        return $this->appointmentService->handle($args['appointmentInput'], $jwt);
    }


}
