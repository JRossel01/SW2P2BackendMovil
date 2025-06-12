<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <title>Verificar Historia Clínica PDF</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>

<body class="bg-gray-100 min-h-screen flex items-center justify-center px-4">
    <div class="bg-white shadow-lg rounded-lg p-8 w-full max-w-xl">
        <h1 class="text-2xl font-bold text-center text-blue-700 mb-6">Verificar Historia Clínica en Blockchain</h1>

        @if ($errors->any())
            <div class="bg-red-100 text-red-700 p-4 rounded mb-4">
                {{ $errors->first() }}
            </div>
        @endif

        <form action="/verificar" method="POST" enctype="multipart/form-data" class="space-y-4">
            @csrf
            <div>
                <label class="block font-semibold mb-1 text-gray-700">Selecciona un archivo PDF:</label>
                <input type="file" name="pdf" accept="application/pdf" required
                    class="block w-full text-sm text-gray-600 file:mr-4 file:py-2 file:px-4
                              file:rounded-md file:border-0 file:text-sm file:font-semibold
                              file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100" />
            </div>
            <button type="submit"
                class="w-full bg-blue-600 hover:bg-blue-700 text-white font-semibold py-2 px-4 rounded transition">
                Verificar
            </button>
        </form>

        @isset($result)
            <hr class="my-6 border-gray-300">
            <div class="space-y-2">
                <h2 class="text-xl font-bold text-gray-800">Resultado</h2>
                <p><strong>🔑 Hash SHA256:</strong> <code class="text-sm break-all text-gray-600">{{ $hash }}</code>
                </p>
                <p><strong>📦 Registrado en Blockchain:</strong>
                    @if ($result['found'])
                        <span class="text-green-600 font-bold">Sí ✅</span>
                    @else
                        <span class="text-red-600 font-bold">No ❌</span>
                    @endif
                </p>

                @if ($result['found'])
                    <p><strong>🧑‍⚕️ ID del Paciente:</strong> {{ $result['patientId'] }}</p>
                    <p><strong>⏰ Timestamp:</strong>
                        {{ \Carbon\Carbon::createFromTimestamp($result['timestamp'])->toDateTimeString() }}</p>
                    <p><strong>🔗 TxHash:</strong>
                        <a href="https://amoy.polygonscan.com/tx/{{ $result['transactionHash'] }}" target="_blank"
                            class="text-blue-600 underline break-all">
                            {{ $result['transactionHash'] }}
                        </a>
                    </p>
                @endif
            </div>
        @endisset
    </div>
</body>

</html>
