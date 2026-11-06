# SIGI - Sistema de Gestión de Inventario

## Descripción

SIGI (Sistema de Gestión de Inventario) es una aplicación web desarrollada para facilitar la administración y el control de inventario.

El sistema permite centralizar la gestión de productos, organizar categorías, registrar movimientos de stock y consultar información mediante un panel de control y reportes.

Su objetivo es ofrecer una herramienta sencilla e intuitiva que facilite el seguimiento del inventario y ayude a mantener un control ordenado de las existencias.

## Tecnologías utilizadas

- **Frontend:** Vue 3, Vite y JavaScript.
- **Backend:** PHP 8.5.
- **Base de datos:** SQLite.
- **Acceso a datos:** PDO.
- **Contenedores:** Docker y Docker Compose.

## Funcionalidades implementadas

### Autenticación y usuarios
- Inicio y cierre de sesión.
- Control de acceso mediante sesiones.
- Gestión de usuarios del sistema.

### Dashboard
- Resumen general del inventario.
- Indicadores de productos registrados.
- Visualización del estado del stock.
- Alertas de productos con stock bajo.
- Información sobre movimientos recientes.

### Gestión de productos
- Alta de productos.
- Modificación de productos existentes.
- Eliminación de productos, con validación de referencias a movimientos.
- Búsqueda de productos.
- Filtros por categoría y estado del stock.
- Paginación del listado.
- Administración de precios y stock mínimo.

### Gestión de categorías
- Listado de categorías.
- Alta de nuevas categorías.
- Modificación de categorías existentes.
- Eliminación de categorías, según las validaciones correspondientes.

### Movimientos de inventario
- Registro de entradas de stock.
- Registro de salidas de stock.
- Actualización automática de las existencias.
- Registro del producto, cantidad, usuario y fecha del movimiento.
- Consulta del historial de movimientos.

### Reportes
- Resumen estadístico del inventario.
- Consulta de información sobre productos y movimientos.
- Filtro de información por período.
- Exportación de reportes en formato CSV.

## Requisitos

Para ejecutar el proyecto se necesita tener instalado:

- Docker.
- Docker Compose.

## Instalación y ejecución

### 1. Clonar el repositorio

git clone https://github.com/Nono-st/SIGI.git
cd SIGI

### 2. Construir e iniciar los contenedores


docker compose up --build -d

Este comando construye las imágenes e inicia los servicios del backend y frontend.

### 3. Inicializar la base de datos

docker compose exec backend php /var/www/html/api/init.php

Este paso crea las tablas necesarias y carga las categorías iniciales si todavía no existen.

### 4. Acceder a la aplicación

Abrir en el navegador:

**Frontend:** http://localhost:5173

**Backend:** http://localhost:8000

## Estructura del proyecto

```text
SIGI/
├── api/
│   ├── config/
│   │   ├── database.php
│   │   └── sesion.php
│   ├── auth.php
│   ├── categorias.php
│   ├── init.php
│   ├── movimientos.php
│   ├── productos.php
│   └── usuarios.php
│
├── database/
│   └── stock.db
│
├── frontend/
│   └── src/
│       ├── router/
│       ├── services/
│       ├── views/
│       └── ...
│
├── compose.yaml
├── Dockerfile
└── README.md
```

## API

El backend expone endpoints PHP para las operaciones principales del sistema:

| Archivo | Responsabilidad |
|---|---|
| `auth.php` | Autenticación de usuarios. |
| `usuarios.php` | Gestión de usuarios. |
| `productos.php` | Operaciones sobre productos. |
| `categorias.php` | Operaciones sobre categorías. |
| `movimientos.php` | Registro y consulta de movimientos. |

Las solicitudes se procesan mediante los métodos HTTP correspondientes y las respuestas se envían en formato JSON.

## Base de datos

El sistema utiliza SQLite como motor de base de datos y PDO para las operaciones de acceso a datos.

El archivo de base de datos se encuentra en:


database/stock.db


La carpeta `database` se monta como volumen en Docker para conservar los datos aunque se detengan o eliminen los contenedores.

### Tablas principales

- **categorias:** almacena las categorías de los productos.
- **usuarios:** almacena la información de los usuarios del sistema.
- **productos:** almacena los productos, sus precios y sus niveles de stock.
- **movimientos:** registra las entradas y salidas de productos, asociadas a un usuario y un producto.

## Detener y volver a iniciar el proyecto

Para detener los contenedores:

docker compose down


Para volver a iniciar el proyecto sin reconstruir las imágenes:

docker compose up -d


Si se realizaron cambios en el código del backend o en su Dockerfile, reconstruir el servicio:


docker compose up -d --build backend


Para reconstruir el frontend:


docker compose up -d --build frontend

## Estado del proyecto

El sistema cuenta con los módulos principales de gestión de inventario implementados e integrados: autenticación, usuarios, productos, categorías, movimientos de stock, dashboard y reportes.

SIGI permite administrar los productos, controlar las entradas y salidas de inventario y consultar información útil para el seguimiento y la gestión del stock.
