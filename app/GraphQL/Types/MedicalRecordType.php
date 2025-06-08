<?php

namespace App\GraphQL\Types;

use GraphQL\Type\Definition\Type;
use Rebing\GraphQL\Support\Type as GraphQLType;

class MedicalRecordType extends GraphQLType
{
    protected $attributes = [
        'name' => 'MedicalRecord',
        'description' => 'Historial médico del paciente',
    ];

    public function fields(): array
    {
        return [
            'id' => ['type' => Type::nonNull(Type::int())],
            'allergies' => ['type' => Type::string()],
            'chronicConditions' => ['type' => Type::string()],
            'medications' => ['type' => Type::string()],
            'bloodType' => ['type' => Type::string()],
            'familyHistory' => ['type' => Type::string()],
            'height' => ['type' => Type::float()],
            'weight' => ['type' => Type::float()],
            'vaccinationHistory' => ['type' => Type::string()],
            'patientId' => ['type' => Type::int()],
        ];
    }
}
