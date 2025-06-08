<?php

namespace App\Services;

use App\Utils\GraphQLClient;

class RegisterAppointmentService
{
    protected $client;

    public function __construct(GraphQLClient $client)
    {
        $this->client = $client;
    }

    public function handle(array $appointmentInput, string $jwt)
    {
        $this->client->setJwt($jwt);

        $mutation = <<<'GRAPHQL'
        mutation RegisterAppointment($appointmentInput: SaveAppointmentInput!) {
            registerAppointment(appointmentInput: $appointmentInput) {
                id
                date
                time
                status
                reason
                patientId
                doctorId
            }
        }
        GRAPHQL;

        $variables = [
            'appointmentInput' => $appointmentInput
        ];

        $data = $this->client->sendAuthenticatedQuery($mutation, $variables);

        return $data['registerAppointment'] ?? null;
    }
}
