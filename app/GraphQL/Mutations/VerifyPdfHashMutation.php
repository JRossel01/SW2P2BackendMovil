<?php

namespace App\GraphQL\Mutations;

use GraphQL\Type\Definition\Type;
use Rebing\GraphQL\Support\Mutation;
use App\GraphQL\Types\PdfVerificationResultType;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;
use Rebing\GraphQL\Support\Facades\GraphQL;

class VerifyPdfHashMutation extends Mutation
{
    protected $attributes = [
        'name' => 'verifyPdfHash',
        'description' => 'Verifica si un PDF fue registrado en la blockchain a partir de su URL local',
    ];

    public function type(): Type
    {
        return GraphQL::type('PdfVerificationResult');
    }

    public function args(): array
    {
        return [
            'pdfUrl' => [
                'type' => Type::nonNull(Type::string()),
                'description' => 'URL pública del PDF (ej: http://localhost:8001/storage/pdf/archivo.pdf)',
            ],
        ];
    }

    public function resolve($root, $args)
    {
        $url = $args['pdfUrl'];

        $path = parse_url($url, PHP_URL_PATH);
        $relativePath = str_replace('/storage/', '', $path);
        Log::info("📄 Verificando PDF en: $relativePath");

        if (!Storage::disk('public')->exists($relativePath)) {
            Log::error("❌ El archivo no existe: $relativePath");
            throw new \Exception("El archivo PDF no existe en el almacenamiento local.");
        }

        $pdfContent = Storage::disk('public')->get($relativePath);
        $pdfHash = hash('sha256', $pdfContent);
        Log::info("🔑 Hash SHA256 generado: $pdfHash");

        // Ejecutar script de decodificación
        $output = [];
        $status = null;

        $scriptPath = base_path('scripts/decodeLog.cjs');
        $command = "node " . escapeshellarg($scriptPath) . " " . escapeshellarg($pdfHash);
        exec($command, $output, $status);


        if ($status !== 0 || empty($output)) {
            Log::error("❌ Error al ejecutar el script de verificación.");
            return [
                'pdfHash' => $pdfHash,
                'registradoEnBlockchain' => false,
                'transaccion' => null,
            ];
        }

        $result = json_decode(implode("", $output), true);
        if (!isset($result['found']) || !$result['found']) {
            Log::warning("❌ Hash no encontrado en blockchain.");
            return [
                'pdfHash' => $pdfHash,
                'registradoEnBlockchain' => false,
                'transaccion' => null,
            ];
        }

        Log::info("✅ Hash verificado en blockchain. Tx: " . $result['transactionHash']);

        return [
            'pdfHash' => $pdfHash,
            'registradoEnBlockchain' => true,
            'transaccion' => $result['transactionHash'],
        ];
    }
}
