# SIGI - Sistema de Gestión de Inventario

## Descripción

SIGI (Sistema de Gestión de Inventario) es una aplicación web desarrollada para facilitar la administración y el control de inventario.

El sistema busca centralizar la gestión de productos y permitir consultar información relacionada con el stock mediante una interfaz web sencilla e intuitiva.

El proyecto se encuentra actualmente en desarrollo.

## Tecnologías utilizadas

- **Frontend:** Vue 3
- **Backend:** PHP 8.5
- **Base de datos:** SQLite
- **Acceso a datos:** PDO
- **Contenedores:** Docker y Docker Compose

## Requisitos

Para ejecutar el proyecto se necesita tener instalado:

- Docker
- Docker Compose

## Instalación y ejecución

### 1. Clonar el repositorio

```bash
git clone https://github.com/Nono-st/SIGI.git
cd SIGI
```

### 2. Construir e iniciar los contenedores

```bash
docker compose up --build -d
```

Este comando construye las imágenes e inicia los servicios del backend y frontend.

### 3. Inicializar la base de datos

```bash
docker compose exec backend php /var/www/html/api/init.php
```

Este paso crea las tablas necesarias y carga las categorías iniciales si todavía no existen.

### 4. Acceder a la aplicación

Abrir en el navegador:

[http://localhost:5173](http://localhost:5173)

El backend se encuentra disponible en:

[http://localhost:8000](http://localhost:8000)

## Detener y volver a iniciar el proyecto

Para detener los contenedores:

```bash
docker compose down
```

Para volver a iniciar los contenedores sin reconstruir las imágenes:

```bash
docker compose up -d
```

Si se realizaron cambios en el código del backend o en su Dockerfile, reconstruir el servicio:

```bash
docker compose up -d --build backend
```

## Base de datos

El sistema utiliza SQLite como motor de base de datos.

El archivo de base de datos se encuentra en:

```text
database/stock.db
```

La carpeta `database` se monta como volumen en Docker para conservar los datos aunque se detengan o eliminen los contenedores.

### Estructura de la base de datos

Actualmente se encuentran definidas las siguientes tablas:

- **categorias:** almacena las categorías de los productos.
- **usuarios:** almacena la información de los usuarios del sistema.
- **productos:** almacena los productos, sus precios y sus niveles de stock.
- **movimientos:** registra las entradas y salidas de productos, asociadas a un usuario y un producto.

## Funcionalidades implementadas

Actualmente el proyecto cuenta con:

- Estructura inicial del frontend desarrollada con Vue 3.
- Backend desarrollado en PHP.
- Conexión con SQLite mediante PDO.
- API para consultar productos.
- Alta de productos.
- Modificación de productos existentes.
- Eliminación de productos, con validación de referencias a movimientos.
- Búsqueda de productos.
- Filtros por categoría y estado del stock.
- Paginación del listado.
- Indicadores de stock disponible, bajo y sin stock.
- Configuración de Docker y Docker Compose.

## Estado del proyecto

SIGI continúa en desarrollo. Algunas funcionalidades y módulos todavía se encuentran pendientes de implementación e integración.

El objetivo es avanzar hacia un sistema que permita administrar integralmente los productos, controlar los movimientos de inventario y consultar información útil para la gestión del stock.

##
