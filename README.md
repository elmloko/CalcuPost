# CalcuPost

Calculadora de tarifas postales de la Agencia Boliviana de Correos. Incluye administración de usuarios y tarifas, historial de consultas, informes PDF, dashboard y métricas de rendimiento.

## Requisitos

- PHP 8.4 con las extensiones requeridas por Laravel y DomPDF.
- Composer 2.
- Node.js 22 y npm.
- MySQL con las bases `calcupost` y, para pruebas, `calcupost_test`.

## Instalación

```powershell
composer install
npm ci
Copy-Item .env.example .env
php artisan key:generate
```

Configure la conexión MySQL en `.env` y cree una base vacía. Las migraciones crean el esquema y cargan las tarifas. Para una instalación existente, conserve sus datos y haga un respaldo antes de migrar. El volcado histórico contiene datos personales, se conserva fuera de Git y no es necesario para instalar desde cero.

```powershell
php artisan migrate --force
npm run build
php artisan calcupost:create-admin
```

El último comando solicita los datos y una contraseña oculta de al menos 12 caracteres. Solo permite crear el primer administrador activo y rechaza correos ya registrados. `db:seed` no crea cuentas ni contraseñas predeterminadas. Revise las cuentas administrativas heredadas; retirar el seeder inseguro no cambia cuentas ni contraseñas que ya existan.

## Tarifario vigente

La migración `2026_10_05_000001_apply_calcupost_main_tariffs` aplica las 283 tarifas de 15 categorías extraídas del respaldo de CalcuPost-main, reemplazando la propuesta comercial revisada. Los datos están en `database/data/calcupost_main_tariffs.json`; la carga conserva los servicios personalizados y guarda los valores reemplazados en `storage/app/tarifario_before_calcupost_main_*.json`.

Para comprobar los datos sin modificarlos, ejecute `.\.tools\php84\php.exe scripts/verify_imported_tariffs.php`. El respaldo original contiene cinco superposiciones y un hueco entre rangos; se conservan para mantener sus valores exactos y pueden revisarse con `php artisan tariffs:check`.

## Arranque principal

En este equipo puede utilizarse el PHP incluido en `.tools`:

```powershell
cd C:\Users\walte\CalcuPost
.\.tools\php84\php.exe artisan serve --host=127.0.0.1 --port=8000
```

Aplicación: `http://127.0.0.1:8000`

Acceso administrativo: `http://127.0.0.1:8000/login`

Para Docker, copie `.env.docker.example` a `.env.docker` y configure allí las credenciales. Ese archivo no se versiona ni entra en el contexto de construcción. Compose publica el servicio solo en `127.0.0.1:8104`; use un proxy HTTPS para acceso externo. Al desplegar, configure `APP_DEBUG=false` y una clave `APP_KEY` única en `.env`. Si ya hay datos cifrados, planifique la rotación de la clave y conserve una copia segura de la anterior antes de invalidar sesiones.
Si publica el servicio por HTTPS, configure también `SESSION_SECURE_COOKIE=true` en `.env` y valide el comportamiento de cookies detrás del proxy.

## Pruebas

Las pruebas automatizadas utilizan exclusivamente `calcupost_test` y no deben apuntar a la base principal.

```powershell
.\.tools\php84\php.exe tests\prepare-calculator-database.php
.\.tools\php84\php.exe vendor\phpunit\phpunit\phpunit
```

La colección y las instrucciones de Postman están en `tests/postman/README.md`.

GitHub Actions ejecuta la suite con MySQL aislado, auditoría Composer y compilación del frontend. Las pruebas sobre una misma base no deben ejecutarse en paralelo.

## Despliegue de producción

Use `docker-compose.production.yml`. El Compose principal está destinado al desarrollo. La imagen de producción contiene el código y CSS compilado, sin Node, Composer ni dependencias PHP de desarrollo. No monta el repositorio del equipo anfitrión.

1. Respalde la base existente y pruebe su restauración. Algunas migraciones de datos son irreversibles y una migración histórica elimina `contract_tariffs`.
2. Copie `.env.docker.example` a `.env.docker`. Configure base de datos, SMTP real, `APP_URL` con el dominio HTTPS y una `APP_KEY` persistente. Puede generar una clave con `php artisan key:generate --show`; guárdela únicamente en la configuración privada. No regenere la clave en cada despliegue.
3. Configure el proxy HTTPS hacia `127.0.0.1:8104` y preserve el encabezado `Host`. En `TRUSTED_PROXIES`, indique únicamente las IP o redes del proxy que conecta con Apache, para reconocer `X-Forwarded-Proto` y generar enlaces HTTPS. El valor vacío no confía en ningún proxy.
4. Construya y migre antes de admitir tráfico:

```sh
docker compose -f docker-compose.production.yml build
docker compose -f docker-compose.production.yml run --rm calcupost_app php artisan migrate --force
# Solo en una instalación nueva sin administrador activo:
docker compose -f docker-compose.production.yml run --rm calcupost_app php artisan calcupost:create-admin
docker compose -f docker-compose.production.yml up -d
```

El entrypoint exige `APP_KEY`, fuerza `APP_DEBUG=false` y prepara las cachés. Las migraciones se ejecutan explícitamente. El volumen `calcupost_storage` conserva sesiones, registros y respaldos; inclúyalo en la estrategia de copias de seguridad. La base de datos se configura externamente.

Antes de abrir tráfico, compruebe login/logout, recuperación de contraseña por correo, un cálculo nacional e internacional, edición de tarifas e informes PDF mediante el dominio HTTPS. La compilación y las pruebas locales no sustituyen esta validación en el servidor de destino. Consulte [la auditoría](AUDITORIA_PRODUCCION_2026-10-23.md) para resultados y límites pendientes.

## Licencia

MIT. Consulte `LICENSE`.
