<?php

try {
    $db = require __DIR__ . "/config/database.php";

    $db->exec("
        CREATE TABLE IF NOT EXISTS categorias (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            nombre TEXT NOT NULL UNIQUE,
            descripcion TEXT
        );

        CREATE TABLE IF NOT EXISTS usuarios (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            nombre TEXT NOT NULL,
            usuario TEXT NOT NULL UNIQUE,
            password TEXT NOT NULL,
            rol TEXT NOT NULL
                CHECK (rol IN ('administrador', 'empleado'))
        );

        CREATE TABLE IF NOT EXISTS productos (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            nombre TEXT NOT NULL,
            precio REAL NOT NULL CHECK (precio >= 0),
            stock INTEGER NOT NULL DEFAULT 0 CHECK (stock >= 0),
            stock_minimo INTEGER NOT NULL DEFAULT 5
                CHECK (stock_minimo >= 0),
            categoria_id INTEGER NOT NULL,
            FOREIGN KEY (categoria_id) REFERENCES categorias(id)
        );

        CREATE TABLE IF NOT EXISTS movimientos (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            productos_id INTEGER NOT NULL,
            usuario_id INTEGER NOT NULL,
            tipo TEXT NOT NULL CHECK (tipo IN ('entrada', 'salida')),
            cantidad INTEGER NOT NULL CHECK (cantidad > 0),
            fecha TEXT NOT NULL DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (productos_id) REFERENCES productos(id),
            FOREIGN KEY (usuario_id) REFERENCES usuarios(id)
        );
    ");

    $consulta = $db->prepare("
        INSERT OR IGNORE INTO categorias (nombre, descripcion)
        VALUES (:nombre, :descripcion)
    ");

    $categorias = [
        ["Bebidas", "Gaseosas, agua y jugos"],
        ["Golosinas", "Caramelos y chocolates"],
        ["Almacen", "Productos de consumo diario"]
    ];

    foreach ($categorias as $categoria) {
        $consulta->execute([
            ":nombre" => $categoria[0],
            ":descripcion" => $categoria[1]
        ]);
    }

    echo "Base de datos inicializada correctamente.\n";

} catch (Throwable $e) {
    fwrite(STDERR, "Error al inicializar la base: " . $e->getMessage() . "\n");
    exit(1);
}
