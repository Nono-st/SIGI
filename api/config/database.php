<?php


$archivoDB = dirname(__DIR__, 2) . "/database/stock.db";

try{
	$db  = new PDO("sqlite:" . $archivoDB);

	$db-> setAttribute(
	PDO::ATTR_ERRMODE,
	PDO::ERRMODE_EXCEPTION
	);


	$db->setAttribute(
	PDO::ATTR_DEFAULT_FETCH_MODE,
	PDO::FETCH_ASSOC
	);

	$db->exec("PRAGMA foreign_keys = ON");

	return $db;

    } catch(PDOException $e){
	throw new RuntimeException(
		"No se pudo conectar la base de datos.",
		0,
		$e);
	}
