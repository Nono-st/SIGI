<?php

$archivoDB = __DIR__ . "/database/stock.db";

try {
    $db = new PDO("sqlite:" . $archivoDB);

    $db->setAttribute(
        PDO::ATTR_ERRMODE,
        PDO::ERRMODE_EXCEPTION
    );

    $db->exec("PRAGMA foreign_keys = ON");

    echo "Conexion con la base de datos exitosa.\n";

} catch (PDOException $e) {
    die("Error de conexion: " . $e->getMessage() . "\n");
}
