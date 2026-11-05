<?php

if (PHP_SAPI !== 'cli') {
    http_response_code(403);
    exit("Este archivo solo puede ejecutarse desde la terminal.\n");
}

try {
    $nombre = trim(getenv('ADMIN_NAME') ?: '');
    $usuario = trim(getenv('ADMIN_USER') ?: '');
    $password = getenv('ADMIN_PASSWORD') ?: '';

    if ($nombre === '' || $usuario === '' || $password === '') {
        throw new RuntimeException(
            'Faltan ADMIN_NAME, ADMIN_USER o ADMIN_PASSWORD.'
        );
    }

    if (strlen($password) < 8) {
        throw new RuntimeException(
            'La contraseña debe tener al menos 8 caracteres.'
        );
    }

    $db = require __DIR__ . '/config/database.php';

    $consulta = $db->prepare("
        INSERT INTO usuarios (nombre, usuario, password, rol)
        VALUES (:nombre, :usuario, :password, 'administrador')
    ");

    $consulta->execute([
        ':nombre' => $nombre,
        ':usuario' => $usuario,
        ':password' => password_hash($password, PASSWORD_DEFAULT)
    ]);

    echo "Administrador creado correctamente.\n";

} catch (Throwable $e) {
    fwrite(STDERR, "Error: " . $e->getMessage() . "\n");
    exit(1);
}
