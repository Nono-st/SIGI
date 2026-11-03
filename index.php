<?php

do {

	echo "====================================\n";
	echo "    SISTEMA DE GESTION DE STOCK\n";
	echo "====================================\n";


	echo "1. Gestionar Productos \n";
	echo "2. Gestionar Categorias \n";
	echo "3. Registrar movimientos de stock \n";
	echo "4. Ver reportes \n";
	echo "0. Salir\n";

	echo "====================================\n";

	echo "Seleccione un opcion: ";

	$opcion = trim(fgets(STDIN));

	echo "\n";

	switch ($opcion){
		case "1":
			echo "Gestionar productos.\n";
			require __DIR__ ."/menu_productos.php";
			break;
		case "2":
            		echo "Gestion de categorias.\n";
            		break;

        	case "3":
            		echo "Movimientos de stock.\n";
            		break;

        	case "4":
            		echo "Reportes.\n";
            		break;

        	case "0":
            		echo "Hasta luego.\n";
            		break;

        	default:
            		echo "Opcion no valida.\n";
            		break;
	}
} while ($opcion !="0");
