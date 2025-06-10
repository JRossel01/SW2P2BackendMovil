<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <style>
        body { font-family: Arial, sans-serif; color: #333; }
        h1 { color: #007bff; }
        .panel {
            background-color: #f1f1f1;
            padding: 15px;
            border-left: 5px solid #007bff;
            margin-top: 20px;
        }
        .footer {
            margin-top: 30px;
            font-size: 14px;
        }
    </style>
</head>
<body>
    <h1>Historial Médico</h1>

    <p>Hola {{ $nombre }},</p>

    <p>Adjunto encontrarás tu historial médico completo con alergias, condiciones crónicas y vacunas registradas.</p>

    <div class="panel">
        Revisá este historial y mantenelo actualizado en tu próximo control médico.
    </div>

    <div class="footer">
        Gracias por confiar en nosotros,<br>
        <strong>Equipo Médico – {{ config('app.name') }}</strong>
    </div>
</body>
</html>
