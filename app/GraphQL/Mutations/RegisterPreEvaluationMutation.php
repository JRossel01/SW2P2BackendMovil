<?php

namespace App\GraphQL\Mutations;

use App\Services\RegisterPreEvaluationService;
use GraphQL\Type\Definition\ResolveInfo;
use Illuminate\Support\Facades\Request;
use GraphQL\Type\Definition\Type;
use Rebing\GraphQL\Support\Mutation;
use Rebing\GraphQL\Support\Facades\GraphQL;

class RegisterPreEvaluationMutation extends Mutation
{
    protected $service;

    public function __construct(RegisterPreEvaluationService $service)
    {
        $this->service = $service;
    }

    protected $attributes = [
        'name' => 'registerPreEvaluation',
    ];

    public function type(): Type
    {
        return GraphQL::type('PreEvaluation');
    }

    public function args(): array
    {
        return [
            'preEvaluationInput' => [
                'name' => 'preEvaluationInput',
                'type' => GraphQL::type('SavePreEvaluationInput'),
                'rules' => ['required'],
            ],
        ];
    }

    public function resolve($root, $args, $context, ResolveInfo $resolveInfo)
    {
        $jwt = Request::bearerToken();
        return $this->service->handle($args['preEvaluationInput'], $jwt);
    }
}
