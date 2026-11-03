<?php

do {
    echo "\n";
    echo "====================================\n";
    echo "       GESTIONAR PRODUCTOS\n";
    echo "====================================\n";

    echo "1. Registrar producto\n";
    echo "2. Listar productos\n";
    echo "3. Modificar producto\n";
    echo "4. Eliminar producto\n";
    echo "0. Volver al menu principal\n";

    echo "====================================\n";
    echo "Seleccione una opcion: ";

    $opcion = trim(fgets(STDIN));

    switch ($opcion) {

    case "1":
       		require __DIR__ . "/cargar_producto.php";
        	break;

    case "2":
        	require __DIR__ . "/listar_productos.php";
        	break;

    case "3":
        	require __DIR__ . "/modificar_producto.php";
        	break;

    case "4":
        	require __DIR__ . "/eliminar_producto.php";
        	break;

    case "0":
        	echo "Volviendo al menu principal...\n";
        	break;

    default:
        	echo "Opcion no valida.\n";
        	break;
}

} while ($opcion != "0");
