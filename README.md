# Reservas de Alojamiento

Prueba técnica para la gestión de reservas de alojamiento. Aplicación web con backend en PHP nativo (sin frameworks), base de datos MySQL, vistas renderizadas con Twig y frontend interactivo con JavaScript.

## Tecnologías

- **Backend**: PHP 8.2 (nativo, sin frameworks)
- **Base de datos**: MySQL (XAMPP)
- **Vistas**: Twig 3.x
- **Frontend**: JavaScript (vanilla, `fetch` API)
- **Gestor de dependencias**: Composer

## Requisitos

- PHP 8.2 o superior
- MySQL (XAMPP, Laragon o similar)
- Composer

## Instalación y ejecución

1. **Clonar el repositorio**:
   ```bash
   git clone https://github.com/joseamdl95/Reservas-de-alojamiento.git
   cd Reservas-de-alojamiento
   ```

2. **Instalar dependencias**:
    ```bash
    composer install
    ```

3. **Configurar la base de datos**:

    - Abre phpMyAdmin (http://localhost/phpmyadmin).
    - Importa el archivo sql/db_reserva_alojamientos_schema.sql.
    - Esto creará la base de datos db_reserva_alojamientos con las tablas reserva y reserva_evento, además de algunos datos de ejemplo.

4. **Configurar credenciales**:

    - Copia config/config.example.php a config/config.php.
    - Ajusta los valores de conexión si es necesario (por defecto usa root sin contraseña, típico de XAMPP).

5. **Arrancar el servidor**:
    ```bash
    php -S localhost:8000 -t public
    ```

6. **Abrir en el navegador**:

    - Listado: http://localhost:8000/

    - API: http://localhost:8000/api/reservas

## 🌐 Despliegue en Producción (Apache)

El proyecto está diseñado para funcionar tanto con el servidor embebido de PHP (`php -S`) como con servidores tradicionales como Apache (XAMPP, InfinityFree, etc.).

Para desplegar en un servidor Apache, es necesario incluir un archivo `.htaccess` en la carpeta pública (`public/` o `htdocs/`) con las siguientes reglas:

```apache
RewriteEngine On
# Seguridad: bloquear acceso a carpetas internas
RedirectMatch 404 /(src|templates|config|vendor|sql)/
# Redirigir peticiones a index.php (Front Controller)
RewriteCond %{REQUEST_FILENAME} !-f
RewriteCond %{REQUEST_FILENAME} !-d
RewriteRule ^ index.php [QSA,L]
```

## Estructura del proyecto

```text
├── public/                 # DocumentRoot (único accesible por URL)
│   ├── index.php           # Front controller (router)
│   ├── css/style.css       # Estilos
│   └── js/app.js           # Lógica frontend (fetch, filtros, creación, PATCH)
├── src/
│   ├── Database.php        # Conexión PDO (singleton)
│   ├── TwigFactory.php     # Configuración de Twig (singleton)
│   ├── Model/
│   │   └── Reserva.php     # Entidad + validaciones de negocio
│   ├── Repository/
│   │   └── ReservaRepository.php # Consultas SQL (PDO) + transacciones
│   └── Controller/
│       └── ReservaController.php # Orquestación de la API (JSON)
├── templates/
│   ├── base.html.twig      # Plantilla base (herencia)
│   ├── lista_reservas.html.twig
│   ├── detalle_reserva.html.twig
│   └── partials/
│       └── form_reserva.html.twig # Formulario reutilizable (crear/editar)
├── config/
│   ├── config.example.php  # Configuración de ejemplo (va a Git)
│   └── config.php          # Configuración real (ignorada)
├── sql/
│   └── db_reserva_alojamientos_schema.sql
└── composer.json
```
## API endpoints
```
| Método | Ruta | Descripción |
|---|---|---|
| `GET` | `/api/reservas` | Lista reservas (filtros: `estado`, `nombre_huesped`, `nombre_alojamiento`, `fecha_desde`, `fecha_hasta`) |
| `GET` | `/api/reservas/{id}` | Detalle de una reserva |
| `POST` | `/api/reservas` | Crea una reserva (valida datos, devuelve `201` o `400`) |
| `PATCH` | `/api/reservas/{id}/estado` | Cambia el estado (`pendiente`, `confirmada`, `cancelada`) |
| `GET` | `/api/reservas/{id}/eventos` | Historial de eventos de una reserva |
```
## Decisiones de diseño y alternativas consideradas

- **Backend en capas**: Controller (HTTP) → Model (validaciones) → Repository (SQL). Cada capa tiene una única responsabilidad y no conoce las capas que no le corresponden.
- **PDO con sentencias preparadas**: todas las consultas usan prepare() + execute() con parámetros. Cero concatenación de valores en SQL. Esto previene inyecciones SQL.
- **Transacciones en escritura**: create() y updateEstado() agrupan el INSERT/UPDATE de la reserva y el registro del evento en una transacción. Si algo falla, rollBack(). La BD nunca queda en un estado inconsistente.
- **Un repositorio por agregado**: ReservaEvento es una entidad hija de Reserva. Sus operaciones siempre ocurren en el contexto de una reserva, así que viven en ReservaRepository como método privado (addEvento). Esto simplifica las transacciones y evita acoplar dos repositorios.
- **Twig con herencia**: base.html.twig define el esqueleto y los bloques. Las hijas (lista_reservas, detalle_reserva) solo rellenan {% block content %}. Cero HTML duplicado.
- **Autoescaping de Twig**: todas las variables se imprimen con {{ }}, que escapa HTML por defecto. No se usa |raw en ningún sitio. Esto previene XSS.
- **Frontend sin frameworks**: JavaScript vanilla con fetch. La tabla se actualiza sin recargar (filtros, creación, cambio de estado). Se usa delegación de eventos para los botones dinámicos.
- **Validación en servidor**: aunque el HTML tiene required y type="email", la validación real está en Reserva::validate(). El cliente puede saltarse el HTML, pero no el servidor.
- **Formulario reutilizable**: partials/form_reserva.html.twig acepta una variable reserva opcional. Sin ella, modo "crear". Con ella, modo "editar" (preparado para el futuro, no implementado porque no está en el alcance).
- **Carpeta `public/` como DocumentRoot (Seguridad)**: El proyecto está estructurado para que la carpeta `public/` sea la única accesible desde el navegador. Esto es una medida de seguridad fundamental: archivos sensibles como `config.php` (que contiene las credenciales de la base de datos) o la lógica de negocio en `src/` quedan fuera del alcance de cualquier petición HTTP directa.
- **Servidor embebido de PHP (`php -S`) vs. XAMPP/Apache**: Por defecto, XAMPP (Apache) sirve todo el contenido de la carpeta `htdocs/`, lo que en este caso expondría archivos internos. Para evitarlo, se utiliza el servidor embebido de PHP con la directiva `-t public`. Esto le dice al servidor que su raíz es únicamente la carpeta `public/`, replicando el comportamiento seguro de un entorno de producción moderno sin necesidad de configurar un Virtual Host en Apache.

## Limitaciones conocidas

- **Concurrencia y alto tráfico**: El sistema actual maneja las transacciones a nivel de base de datos (ACID), lo cual es suficiente para el alcance de esta prueba. Sin embargo, en un entorno de alta concurrencia (ej. muchos usuarios intentando reservar el mismo alojamiento exactamente al mismo tiempo), podrían ocurrir race conditions o bloqueos de tabla (lock contention). Para producción, se requeriría una estrategia de bloqueo más robusta (pesimista u optimista) y, en escenarios de altísimo tráfico, derivar la gestión de reservas a una arquitectura orientada a eventos con colas de mensajería (RabbitMQ, Kafka) o microservicios para encolar y procesar las solicitudes de forma asíncrona.
- **No hay autenticación**: cualquiera puede crear, ver o cambiar reservas. No está en el alcance del gist.
- **No hay paginación**: el listado devuelve todas las reservas. Para un volumen grande, habría que añadir LIMIT/OFFSET.
- **No hay edición de reservas**: el partial está preparado, pero falta el endpoint PUT y la vista.
- **Race condition teórica en updateEstado**: entre el findById y el UPDATE, otra petición podría borrar la reserva. Para producción, se resolvería con SELECT ... FOR UPDATE.
- **Caché de Twig desactivada**: en desarrollo ('cache' => false). En producción, se activaría para mejorar rendimiento.

## Qué haría con más tiempo
- Autenticación con sesiones o JWT.
- Paginación y ordenación configurable en el listado.
- Edición completa de reservas (reutilizando el partial).
- Tests unitarios del modelo (Reserva::validate) y de integración del repositorio.
- Despliegue en un hosting con HTTPS y variables de entorno para las credenciales.
- Internacionalización (i18n) de los mensajes de error.