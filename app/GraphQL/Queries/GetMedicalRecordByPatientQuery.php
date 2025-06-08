<?php

namespace App\GraphQL\Queries;

use App\Services\GetMedicalRecordByPatientService;
use GraphQL\Type\Definition\Type;
use Rebing\GraphQL\Support\Facades\GraphQL;
use Rebing\GraphQL\Support\Query;
use Illuminate\Support\Facades\Request;

class GetMedicalRecordByPatientQuery extends Query
{
    protected $attributes = [
        'name' => 'getMedicalRecordByPatient',
    ];

    public function __construct(protected GetMedicalRecordByPatientService $service) {}

    public function type(): Type
    {
        return GraphQL::type('MedicalRecord');
    }

    public function args(): array
    {
        return [
            'patientId' => ['type' => Type::nonNull(Type::int())],
        ];
    }

    public function resolve($root, $args)
    {
        $jwt = Request::bearerToken();
        return $this->service->handle($args['patientId'], $jwt);
    }
}
