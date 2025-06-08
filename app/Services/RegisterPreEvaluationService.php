<?php

namespace App\Services;

use App\Utils\GraphQLClient;

class RegisterPreEvaluationService
{
    protected $client;

    public function __construct(GraphQLClient $client)
    {
        $this->client = $client;
    }

    public function handle(array $preEvaluationInput, string $jwt)
    {
        $this->client->setJwt($jwt);

        $mutation = <<<'GRAPHQL'
        mutation RegisterPreEvaluation($preEvaluationInput: SavePreEvaluationInput!) {
            registerPreEvaluation(preEvaluationInput: $preEvaluationInput) {
                id
                appointmentId
                symptoms
                potentialDiagnosis
            }
        }
        GRAPHQL;

        $variables = ['preEvaluationInput' => $preEvaluationInput];

        $response = $this->client->sendAuthenticatedQuery($mutation, $variables);

        return $response['registerPreEvaluation'] ?? null;
    }
}
