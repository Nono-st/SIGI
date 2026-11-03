SIGI - Sistema de Gestión de invetario

Descripción

SIGI es una aplicación web con el objetivo de facilitar la gestión y el control de inventario mediante una interfaz web que permita administrar productos y consultar informacion relacionada con el stock.


EL proyecto se enctuentra en desarrollo.


Tencnologías utulizadas

Frontend: Vue 3 
Backend: PHP
Base de datos : SQLite
Acceso a datos: PDO 
Contenedores: Docker y Docker Compose

Requisitos

para ejecutar el proyecto se necesita:

Docker y Docker Compose

Instalcion y ejecución

1 Clonar el repositorio
git clone https://github.com/Nono-st/SIGI
cd SIGI

2 Construir e inciar los contenedores 
docker compose up --build -d 

3 inicializar la base de datos
docker compose exec backend php/var/www/html/api/init.php


4 abrir la aplicacion en el navegador :
http://localhost:5173 

Detener la aplicacion 

docker compose down

para volver a iniciar el proyecto

docker comopose up -d 

Base de datos

El sistema utiliza SQLite. el archivo de base de datos se encuentra en database/stock.db.

la carpeta database se monta como volumen en Docker para conservar los datos al detener los contenedores.


Estado actual del proyecto 

Actualmente se encuentra implementado:

Estructura inicial del frontend con Vue 3.

API desarrollada en PHP.

Conexión entre el backend y SQLite mediante PDO.

Consulta de productos desde la base de datos.

Visualización de productos en la interfaz.(algunos de los productos son ejemplos o pruebas. Ya que algunos de estos no existen en la bd)

Búsqueda, filtros y paginación en la vista de productos.

Configuración inicial de Docker y Docker Compose.

Otras funcionalidades del sistema continúan en desarrollo.

