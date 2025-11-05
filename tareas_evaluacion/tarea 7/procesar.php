<?php
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $archivo = "datos_clientes.json";

    if (!file_exists($archivo)) {
        file_put_contents($archivo, "[]");
    }

    $clientes = json_decode(file_get_contents($archivo), true);
    if (!is_array($clientes)) {
        $clientes = [];
    }

    $nuevo = [
        "nombre" => trim($_POST["nombre"] ?? ''),
        "apellidos" => trim($_POST["apellidos"] ?? ''),
        "email" => trim($_POST["email"] ?? ''),
        "direccion" => trim($_POST["direccion"] ?? ''),
        "ciudad" => trim($_POST["ciudad"] ?? ''),
        "telefono" => trim($_POST["telefono"] ?? '')
    ];

    if (array_filter($nuevo)) {
        $clientes[] = $nuevo;
        file_put_contents($archivo, json_encode($clientes, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
    }

    // Ahora mostramos un mensaje y redirigimos en 2 segundos:
    echo '<!DOCTYPE html>
    <html lang="es">
    <head>
        <meta charset="UTF-8">
        <meta http-equiv="refresh" content="2;url=clientes.html" />
        <title>Guardando cliente...</title>
    </head>
    <body>
        <p>Cliente guardado correctamente. Serás redirigido en 2 segundos...</p>
    </body>
    </html>';
    exit;
}
