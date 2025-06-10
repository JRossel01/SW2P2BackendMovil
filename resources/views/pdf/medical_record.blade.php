<!DOCTYPE html>
<html>

<head>
    <meta charset="utf-8">
    <title>Historial Médico</title>
    <style>
        body {
            font-family: 'DejaVu Sans', sans-serif;
            /* Mejor compatibilidad con DomPDF */
            font-size: 13px;
            line-height: 1.6;
            color: #333;
            padding: 20px;
        }

        h2,
        h3 {
            color: #2c3e50;
            margin-bottom: 10px;
        }

        .label {
            font-weight: bold;
            color: #000;
        }

        hr {
            border: 0;
            border-top: 1px solid #ccc;
            margin: 20px 0;
        }

        .consulta {
            padding: 10px;
            border: 1px solid #ddd;
            background-color: #f9f9f9;
            border-radius: 6px;
            margin-bottom: 20px;
        }
    </style>
</head>

<body>
    <h2>Historial Médico del Paciente</h2>
    <p><span class="label">Fecha de generación:</span> {{ $fecha }}</p>
    <p><span class="label">Nombre:</span> {{ $nombre }}</p>
    <p><span class="label">Correo:</span> {{ $email }}</p>
    <hr>

    @if (!empty($record))
        <p><span class="label">Alergias:</span> {{ $record['allergies'] }}</p>
        <p><span class="label">Condiciones Crónicas:</span> {{ $record['chronicConditions'] }}</p>
        <p><span class="label">Medicamentos:</span> {{ $record['medications'] }}</p>
        <p><span class="label">Tipo de Sangre:</span> {{ $record['bloodType'] }}</p>
        <p><span class="label">Antecedentes Familiares:</span> {{ $record['familyHistory'] }}</p>
        <p><span class="label">Altura:</span> {{ $record['height'] }} m</p>
        <p><span class="label">Peso:</span> {{ $record['weight'] }} kg</p>
        <p><span class="label">Vacunas:</span> {!! nl2br(e($record['vaccinationHistory'])) !!}</p>
    @else
        <p>No se encontró historial médico.</p>
    @endif

    <h3>Consultas:</h3>
    @if (!empty($consults))
        @foreach ($consults as $c)
            <div class="consulta">
                <p><span class="label">Fecha:</span> {{ $c['date'] }}</p>
                <p><span class="label">Diagnóstico:</span> {{ $c['diagnosis'] }}</p>
                <p><span class="label">Tratamiento:</span> {{ $c['treatment'] }}</p>
                <p><span class="label">Observaciones:</span> {{ $c['observations'] }}</p>
                <p><span class="label">Peso actual:</span> {{ $c['currentWeight'] }} kg</p>
                <p><span class="label">Altura actual:</span> {{ $c['currentHeight'] }} m</p>
                <p><span class="label">Tiempo de atención:</span> {{ $c['attentionTime'] }}</p>
                <hr>
            </div>
        @endforeach
    @else
        <p>No hay consultas registradas.</p>
    @endif

</body>

</html>
