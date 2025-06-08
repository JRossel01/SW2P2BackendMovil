<?php

namespace App\GraphQL\Queries;

use App\Services\GetAllDoctorsWithSchedulesService;
use GraphQL\Type\Definition\ResolveInfo;
use Rebing\GraphQL\Support\Query;
use GraphQL\Type\Definition\Type;
use Rebing\GraphQL\Support\Facades\GraphQL;
use Illuminate\Support\Facades\Request;

class GetAllDoctorsWithSchedulesQuery extends Query
{
    protected $attributes = [
        'name' => 'getAllDoctorsWithSchedules',
    ];

    protected $service;

    public function __construct(GetAllDoctorsWithSchedulesService $service)
    {
        $this->service = $service;
    }

    public function type(): Type
    {
        return Type::listOf(GraphQL::type('Doctor'));
    }

    public function args(): array
    {
        return [];
    }

    public function resolve($root, $args, $context, ResolveInfo $resolveInfo)
    {
        $jwt = Request::bearerToken();
        return $this->service->handle($jwt);
    }
}
