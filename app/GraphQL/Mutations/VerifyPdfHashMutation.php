<?php

namespace App\GraphQL\Mutations;

use GraphQL\Type\Definition\Type;
use Rebing\GraphQL\Support\Mutation;
use App\GraphQL\Types\PdfVerificationResultType;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Http;

class VerifyPdfHashMutation extends Mutation
{
    protected $attributes = [
        'name' => 'verifyPdfHash',
        'description' => 'Verifica si un PDF fue registrado en la blockchain a partir de su URL local',
    ];

    public function type(): Type
    {
        return \GraphQL::type('PdfVerificationResult');
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

        // Extraer la ruta relativa desde la URL
        $path = parse_url($url, PHP_URL_PATH);
        $relativePath = str_replace('/storage/', '', $path);

        // Leer directamente desde el disco
        if (!Storage::disk('public')->exists($relativePath)) {
            throw new \Exception("El archivo PDF no existe en el almacenamiento local.");
        }

        $pdfContent = Storage::disk('public')->get($relativePath);
        $pdfHash = hash('sha256', $pdfContent);

        // Consultar logs en Polygonscan Amoy
        $contractAddress = "0x260B76B3557A846cbF0313Bb525880427FfF5833";
        $apiKey = env('POLYGONSCAN_API_KEY'); // opcional
        $polygonUrl = "https://api-amoy.polygonscan.com/api";

        $scanResponse = Http::get($polygonUrl, [
            'module' => 'logs',
            'action' => 'getLogs',
            'fromBlock' => '0',
            'toBlock' => 'latest',
            'address' => $contractAddress,
            'apikey' => $apiKey,
        ]);

        $logs = $scanResponse->json('result');
        if (!is_array($logs)) {
            \Log::error("Respuesta inválida de Polygonscan: " . json_encode($logs));
            return [
                'pdfHash' => $pdfHash,
                'registradoEnBlockchain' => false,
                'transaccion' => null,
            ];
        }

        $hashFound = false;
        $txHash = null;

        $hexHash = '0x' . strtolower($pdfHash);

        foreach ($logs as $log) {
            $rawData = strtolower($log['data']);

            if (str_contains($rawData, $hexHash)) {
                $hashFound = true;
                $txHash = $log['transactionHash'];
                break;
            }
        }


        return [
            'pdfHash' => $pdfHash,
            'registradoEnBlockchain' => $hashFound,
            'transaccion' => $txHash,
        ];
    }
}
