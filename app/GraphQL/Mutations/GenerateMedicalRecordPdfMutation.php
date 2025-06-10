<?php

namespace App\GraphQL\Mutations;

use App\Services\GenerateMedicalRecordPdfService;
use GraphQL\Type\Definition\Type;
use Rebing\GraphQL\Support\Mutation;
use Illuminate\Support\Facades\Request;


class GenerateMedicalRecordPdfMutation extends Mutation
{
    protected $attributes = [
        'name' => 'generateMedicalRecordPdf',
    ];

    public function type(): Type
    {
        return Type::string(); // URL del PDF
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
        return app(GenerateMedicalRecordPdfService::class)->handle($args['patientId'], $jwt);
    }
}
