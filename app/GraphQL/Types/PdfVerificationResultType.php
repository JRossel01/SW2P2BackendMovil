<?php
namespace App\GraphQL\Types;

use GraphQL\Type\Definition\Type;
use Rebing\GraphQL\Support\Type as GraphQLType;

class PdfVerificationResultType extends GraphQLType
{
    protected $attributes = [
        'name' => 'PdfVerificationResult',
        'description' => 'Resultado de verificación de hash en blockchain',
    ];

    public function fields(): array
    {
        return [
            'pdfHash' => [
                'type' => Type::string(),
                'description' => 'Hash SHA256 del PDF',
            ],
            'registradoEnBlockchain' => [
                'type' => Type::boolean(),
                'description' => 'Indica si el hash ya fue registrado',
            ],
            'transaccion' => [
                'type' => Type::string(),
                'description' => 'Hash de la transacción si fue registrada',
            ],
        ];
    }
}
