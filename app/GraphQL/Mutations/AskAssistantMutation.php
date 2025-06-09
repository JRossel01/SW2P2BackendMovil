<?php

namespace App\GraphQL\Mutations;

use App\Services\Assistant\AssistantService;
use App\Services\Assistant\ProcessAssistantService;
use GraphQL\Type\Definition\Type;
use Rebing\GraphQL\Support\Mutation;
use Rebing\GraphQL\Support\Facades\GraphQL;
use Illuminate\Support\Facades\Request;

class AskAssistantMutation extends Mutation
{
    protected AssistantService $assistantService;
    protected ProcessAssistantService $processService;

    public function __construct(AssistantService $assistantService,
        ProcessAssistantService $processService)
    {
        $this->assistantService = $assistantService;
        $this->processService = $processService;
    }

    protected $attributes = [
        'name' => 'askAssistant',
    ];

    public function type(): Type
    {
        return GraphQL::type('AssistantResponse');
    }

    public function args(): array
    {
        return [
            'input' => [
                'name' => 'input',
                'type' => Type::nonNull(Type::string()),
            ],
            'patientId' => [
                'name' => 'patientId',
                'type' => Type::nonNull(Type::string()),
            ],
        ];
    }

    public function resolve($root, $args)
    {
        $jwt = Request::bearerToken();

        $message = $this->assistantService->ask(
            $args['input'],
            $jwt,
            $args['patientId']
        );

        $appointmentId = $this->processService->handle($message, $jwt, $args['patientId']);

        return [
            'message' => $message,
            'appointmentId' => $appointmentId,
        ];
    }
}
