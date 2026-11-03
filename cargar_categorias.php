
<?php

require_once __DIR__ . "/database.php";

try{
	$sql = "INSERT INTO categorias (nombre, descripcion )
		VALUES (:nombre, :descripcion)";

	$consulta = $db->prepare($sql);

	$categorias = [
		["Bebidas", "Gaseosas, agua y jugos"],
		["Golosinas", "Caramelos y Chocolates"],
		["Almacen", "Productos de consumo diario"]
	];

	foreach ($categorias as $categoria){
		$consulta->execute([
			":nombre" => $categoria[0],
			":descripcion"=> $categoria[1]
		]);
	}

	echo "Categoria cargados correctamente.\n";

} catch(PDOException $e) {
	die("Error al cargar categorias: " . $e->getMessage(). "\n");
}
