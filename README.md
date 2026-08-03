# App Template

Plantilla mínima para desarrollar una aplicación PHP con Docker.

## Arquitectura

- Nginx 1.27 sirve las peticiones HTTP.
- PHP 8.3 FPM ejecuta una aplicación Symfony mínima.
- PostgreSQL 16 proporciona persistencia.
- Composer instala automáticamente las dependencias antes de iniciar PHP-FPM.

La página inicial ejecuta una consulta técnica `SELECT 1`. Una respuesta correcta confirma la comunicación completa entre Nginx, PHP-FPM y PostgreSQL.

## Requisitos

- Docker con Docker Compose.

## Inicio

```bash
docker compose up --build
```

La aplicación estará disponible en <http://localhost:8081>. PostgreSQL se expone en el puerto local `5433`; dentro de la red Docker continúa usando su puerto estándar `5432`.

Para iniciar en segundo plano:

```bash
make up
make health
```

## Comandos

```bash
make help
make ps
make logs
make shell
make down
```

Las variables de desarrollo están documentadas en `.env`. Los valores locales pueden sobrescribirse en `.env.local` para Symfony o mediante variables del entorno para Docker Compose.
