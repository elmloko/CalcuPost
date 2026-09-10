# CalcuPost

Calculadora de tarifas postales de la Agencia Boliviana de Correos. Incluye administración de usuarios y tarifas, historial de consultas, informes PDF, dashboard y métricas de rendimiento.

## Requisitos

- PHP 8.4 con las extensiones requeridas por Laravel y DomPDF.
- Composer 2.
- Node.js y npm.
- MySQL con las bases `calcupost` y, para pruebas, `calcupost_test`.

## Instalación

```powershell
composer install
npm ci
Copy-Item .env.example .env
php artisan key:generate
```

Configure la conexión MySQL en `.env`, importe `calcupost.sql` en la base principal y ejecute:

```powershell
php artisan migrate --force
npm run build
```

## Arranque principal

En este equipo puede utilizarse el PHP incluido en `.tools`:

```powershell
cd C:\Users\walte\CalcuPost
.\.tools\php84\php.exe artisan serve --host=127.0.0.1 --port=8000
```

Aplicación: `http://127.0.0.1:8000`

Acceso administrativo: `http://127.0.0.1:8000/login`

## Pruebas

Las pruebas automatizadas utilizan exclusivamente `calcupost_test` y no deben apuntar a la base principal.

```powershell
.\.tools\php84\php.exe tests\prepare-calculator-database.php
.\.tools\php84\php.exe vendor\phpunit\phpunit\phpunit --filter "Calculator(Api|History)Test|TariffDisplayTest|LoginSessionTest"
```

La colección y las instrucciones de Postman están en `tests/postman/README.md`.

## Licencia

MIT. Consulte `LICENSE`.
