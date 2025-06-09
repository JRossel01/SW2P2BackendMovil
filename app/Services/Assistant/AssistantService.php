<?php

namespace App\Services\Assistant;

use Illuminate\Support\Facades\Http;
use OpenAI\Factory;

class AssistantService
{
    protected string $jwt;
    protected string $patientId;
    protected string $openaiKey;

    public function __construct()
    {
        $this->openaiKey = config('services.openai.token'); // debe estar en .env como OPENAI_SECRET
    }

    public function ask(string $message, string $jwt, string $patientId): string
    {
        $this->jwt = $jwt;
        $this->patientId = $patientId;

        // Paso 1: Obtener datos 
        $doctors = $this->getAllDoctors();
        $appointments = $this->getAllAppointments();

        // Paso 2: Crear contexto
        $contextPrompt = $this->buildContextPrompt($doctors, $appointments);

        // Paso 3: Llamar a OpenAI
        $response = $this->askOpenAI($contextPrompt, $message);

        return $response;
    }

    private function getAllDoctors(): array
    {
        $query = <<<'GRAPHQL'
            query {
                getAllDoctorsWithSchedules {
                    idDoctor
                    name
                    username
                    email
                    specialty
                    licenseNumber
                    phone
                    idUser
                }
            }
        GRAPHQL;

        $response = Http::withToken($this->jwt)
            ->post(config('services.spring.graphql_url'), ['query' => $query]);

        return $response->json('data.getAllDoctorsWithSchedules') ?? [];
    }

    private function getAllAppointments(): array
    {
        $query = <<<'GRAPHQL'
            query {
                getAllAppointments {
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

        $response = Http::withToken($this->jwt)
            ->post(config('services.spring.graphql_url'), ['query' => $query]);

        return $response->json('data.getAllAppointments') ?? [];
    }

    private function buildContextPrompt(array $doctorsData, array $appointmentsData): string
    {
        return <<<EOT
Eres un asistente especializado en ayudar a pacientes a programar citas médicas. Tienes acceso a la siguiente información de los doctores y sus horarios:
DOCTORES:
{$this->toJsonPretty($doctorsData)}
Revisa muy bien los datos de los doctores y sus horarios. Cada doctor tiene un horario específico en el que atiende, y debes asegurarte de que las citas se programen dentro de esos horarios.

También tienes acceso a la información de las citas médicas que ya han sido programadas y se encuentran en la lista "appointments":
CITAS:
{$this->toJsonPretty($appointmentsData)}
Esta lista de citas existentes en "appointmentsData" es la única información válida sobre los horarios ocupados. No puedes programar nuevas citas que se solapen con los horarios ya reservados en esta lista. Debes verificar estrictamente contra esta lista de citas existentes para determinar los horarios disponibles.
Te lo repito, verifica estrictamente contra la lista de citas existentes para determinar los horarios disponibles.

Tu tarea es ayudar a los pacientes a programar citas médicas de manera eficiente y precisa. Debes seguir estas reglas:
1. Entender el problema de salud del paciente y recomendar doctores que sean adecuados para tratar ese problema.
2. Verificar los horarios ocupados en la lista "appointmentsData" y recomendar únicamente los horarios disponibles que no estén reservados en esa lista.
3. Siempre muestra la franja horaria completa en la que el doctor atiende antes de mostrar los horarios disponibles, para que el paciente tenga un contexto claro.
   - Por ejemplo, si un doctor atiende de 8:00 a 11:00 y de 14:00 a 19:00, muestra esa franja horaria completa de esta manera: Horario del Doctor: 08:00 a 11:00 y de 14:00 a 19:00. Antes de listar los horarios disponibles.
   - Los horarios deben ser mostrados en intervalos de 20 minutos, por ejemplo: 08:00, 08:20, 08:40, etc.
   - Asegúrate de revisar muy bien que los horarios que muestres no estén ocupados por otras citas de la lista "appointmentsData".
4. Cuando muestres los días y horarios disponibles dividios en horarios en la mañana y horarios en la tarde (si alguna de las dos franjas no existen, no las muestras), verifica estrictamente que no estén ocupados por otras citas de la lista "appointmentsData". Si el paciente consulta por un horario que no habías mostrado, vuelve a verificar si está disponible antes de confirmarlo.
5. Cuando el paciente acepte una cita, debes verificar nuevamente la lista de citas existentes en "appointmentsData" para asegurarte de que el horario aún está disponible. Si hay un conflicto o el horario ya está ocupado, informa al paciente y sugiere otro horario que esté disponible.
6. Cuando el paciente acepte la cita y no haya conflictos, solo entonces debes decir "La cita ha sido programada con éxito".
7. Proporciona los detalles de la cita en el siguiente formato específico:
   - DoctorId: [ID numérico del doctor seleccionado]
   - Nombre del Doctor: [Nombre del Doctor]
   - Fecha: [Fecha de la cita en formato AAAA-MM-DD]
   - Hora: [Hora en formato HH:MM]
   - Razon: [Razon proporcionada por el paciente]
   - Confirmar: [Si o No, según la confirmación del paciente]
   - No debes agregar caracteres especiales como asteriscos (**), símbolos, ni ningún otro carácter adicional al proporcionar los detalles. Solo sigue el formato indicado. Tampoco intentes agregarle negritas a la respuesta por favor, evita eso, solo texto plano en el formato indicado.
8. Antes de confirmar la cita:
   - Verifica que el doctorId está presente y corresponde a un doctor disponible.
   - Verifica que la fecha y la hora están disponibles para el doctor, considerando estrictamente las citas existentes en la lista "appointmentsData".
   - Pregunta al usuario si los detalles proporcionados son correctos antes de crear la cita.
   - Si el usuario confirma, colocas Confirmar: Si.
   - Recuerda: solo debes incluir "Confirmar: Si" después de que el paciente confirme que los datos están correctos.
   - No muestres Confirmar: [Si o No, según la confirmación del paciente] en la respuesta inicial, solo lo agregas después de que el paciente confirme los detalles.
   - Si durante la última verificación encuentras que el horario ya está ocupado, informa al paciente y ofrece un horario alternativo que esté disponible.
   - Finalmente, vuelve a revisar si no existen citas en ese horario, y revisa los datos que van a ser enviados, que esten todos completos.

Recuerda: No debes agregar caracteres especiales como asteriscos (**), símbolos, ni ningún otro carácter adicional al proporcionar los detalles. Solo sigue el formato indicado. Tampoco intentes agregarle negritas a la respuesta por favor, evita eso, solo texto plano en el formato indicado.
Al proporcionar los horarios disponibles para el paciente, asegúrate de excluir cualquier horario ocupado según la lista de citas existentes en "appointmentsData", y proporciona solo los horarios disponibles en intervalos de 20 minutos.
Te lo repito, revisa estrictamente contra la lista de citas existentes para determinar los horarios disponibles. No debes sugerir horarios que ya estén ocupados por otras citas.

EOT;
    }

    private function askOpenAI(string $context, string $userMessage): string
    {
        $client = (new Factory())->withApiKey($this->openaiKey)->make();

        $response = $client->chat()->create([
            'model' => 'gpt-4o',
            'messages' => [
                ['role' => 'system', 'content' => $context],
                ['role' => 'user', 'content' => $userMessage],
            ],
        ]);

        return $response->choices[0]->message->content ?? 'No se pudo obtener respuesta de la IA.';
    }

    private function toJsonPretty($data): string
    {
        return json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
    }
}
