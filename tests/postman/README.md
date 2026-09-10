# Pruebas de la API de CalcuPost

## Qué servidor usar

- Trabajo diario y datos reales: `http://127.0.0.1:8000/` (base `calcupost`). Administración en `/login`.
- Pruebas de Postman: `http://127.0.0.1:8001/` (base `calcupost_test`). Sus consultas no son datos reales. Al preparar los fixtures se copia el catálogo tarifario vigente del 8000 y después se aseguran los tres precios controlados que verifica la colección.
- Ambos ejecutan el mismo código de `C:\Users\walte\CalcuPost`. Corregir el código beneficia a ambos; las bases permanecen separadas. Un despliegue público futuro requerirá un servidor y dominio, no estos enlaces locales.

## Carga de tarifarios

Los selectores usan Livewire. Sus archivos públicos deben coincidir con la versión instalada; `composer install`/`dump-autoload` ahora los publica automáticamente. Para republicarlos manualmente:

```powershell
.\.tools\php84\php.exe artisan vendor:publish --tag=livewire:assets --force
```

La caché del tarifario se identifica por servidor/base/categoría, de modo que el puerto 8000 no puede recibir filas modificadas en el 8001. `prepare-fixtures.php` vuelve a copiar las tarifas y servicios actuales desde `calcupost` hacia `calcupost_test`, sin modificar la base principal, y limpia únicamente la caché tarifaria del entorno de pruebas.

Regresión de selectores, caché aislada, edición e integridad de JavaScript:

```powershell
.\.tools\php84\php.exe vendor/phpunit/phpunit/phpunit --filter TariffDisplayTest
```

## Fechas y horas de registros

El almacenamiento permanece en UTC. Registros, informes PDF, filtros por día y gráficas muestran hora boliviana (`America/La_Paz`, UTC-04:00). Ejemplo: `02/09/2026 15:35:15 UTC` se muestra como `02/09/2026 11:35:15`. No se reescriben ni borran los registros históricos.

Para auditar ambas bases sin modificarlas:

```powershell
.\.tools\php84\php.exe tests/postman/inspect-record-times.php
```

## Resultado verificado — 3 de septiembre de 2026

Ejecutado con **Postman CLI oficial 1.52.0**, no Newman, contra `http://127.0.0.1:8001` y la base aislada `calcupost_test`:

- 35 peticiones, 156 comprobaciones, **0 fallos**.
- Cobertura positiva de las 15 variantes postales activas, además de reglas adicionales y casos inválidos.
- 19 consultas guardadas: 18 con tarifa y 1 sin tarifa, agrupadas en una sesión de visitante.
- La verificación SQL de los IDs devueltos confirma que el historial realmente se guardó.
- Pruebas complementarias PHP/Livewire: 27 pruebas, 272 comprobaciones correctas. Queda un aviso de esquema PHPUnit antiguo, no un fallo de pruebas.
- Datos de la aplicación conservados: 5 usuarios, 283 tarifas, 15 variantes de servicio y 4 consultas históricas.
- Promedio local: 406 ms; mínimo 281 ms, máximo 836 ms. No es una prueba de carga.

La captura de la aplicación gráfica de Postman falla y no expone controles utilizables. Con autorización del usuario se utilizó su CLI oficial. La colección se ejecutó desde archivo, sin inicio de sesión ni publicación de resultados en Postman Cloud. Los informes locales son `postman-results.html` y `postman-results.json` (excluidos de Git).

El antiguo `results.json` corresponde a Newman **antes de las correcciones**; no es el resultado actual.

## Fallos corregidos

| Caso | Comportamiento corregido |
| --- | --- |
| Destino inexistente | HTTP 422; no se intenta consultar una columna SQL arbitraria |
| Peso cero, negativo o inválido | HTTP 422, sin guardar consultas inválidas |
| Destino incompatible con nacional/internacional | HTTP 422 en ambos sentidos |
| Certificado EMS NAT | 17 Bs a 1 kg, igual que el formulario; no añade 2 Bs indebidamente |
| Tarifa cero | Se reconoce como válida, distinta de una tarifa inexistente |
| Consultas API ausentes del historial | Se guardan con sus valores y alimentan registros/estadísticas existentes |

`PostalCalculator` y `CalculatorHistory` son compartidos por la API y Livewire. No se cambió el formulario, su botón **Calcular**, ni el catálogo real.

Se mantuvo la regla comercial existente: suma de dimensiones mayor a 1.5 añade 30%. El formulario rotula centímetros; confirmar ese umbral con negocio antes de modificar importes. La prueba documenta la regla actual, no valida su conveniencia comercial.

## Repetir las pruebas

Desde PowerShell en la raíz del proyecto:

1. Preparar únicamente la base de pruebas (MySQL debe estar iniciado y `calcupost_test` debe existir):

```powershell
.\.tools\php84\php.exe tests/postman/prepare-fixtures.php
```

2. Iniciar el servidor aislado en otra terminal:

```powershell
powershell -NoProfile -ExecutionPolicy Bypass -File tests/postman/serve.ps1
```

No cambia `.env`. Si existe configuración cacheada, el script se detiene; ejecutar `artisan config:clear` antes de repetir. Detener el servidor de pruebas con Ctrl+C al terminar.

3. Ejecutar Postman CLI y comprobar el historial:

```powershell
npx --yes postman-cli@1.52.0 collection run tests/postman/CalcuPost.postman_collection.json --no-report-events --delay-request 200 --timeout-request 15000 --reporters 'cli,json,html' --reporter-json-export tests/postman/postman-results.json --reporter-html-export tests/postman/postman-results.html
.\.tools\php84\php.exe tests/postman/verify-history.php
```

No hace falta iniciar sesión en Postman. El aviso sobre credenciales solo afecta a publicar resultados en su nube. No se suprime el código de error de las pruebas. La colección utiliza el puerto 8001 y fixtures controlados de 17/228/10 Bs; **no apuntarla al servidor real 8000**, porque los cálculos ahora guardan historial. Las consultas de prueba permanecen únicamente en `calcupost_test`.

Pruebas complementarias (usan transacciones y rechazan cualquier otra base):

```powershell
.\.tools\php84\php.exe vendor/phpunit/phpunit/phpunit --filter 'Calculator(Api|History)Test'
```

## Ejecutar desde la aplicación Postman

1. Seleccionar **Import** y abrir `CalcuPost.postman_collection.json`.
2. Mantener `base_url = http://127.0.0.1:8001`, con el servidor aislado iniciado.
3. Seleccionar **Run collection** en **CalcuPost · Pruebas API completa** y ejecutar las 35 peticiones. La carpeta **Cobertura positiva · Todos los servicios** contiene los casos añadidos para cada variante postal.
4. Revisar **Test Results**. No requiere contraseñas ni tokens reales.

## Acceso web al servidor de pruebas

El puerto 8001 utiliza `calcupost_test`, no la base del puerto 8000. Para copiar explícitamente las cuentas y sus contraseñas actuales desde `calcupost`:

```powershell
.\.tools\php84\php.exe tests/postman/sync-test-users.php
.\.tools\php84\php.exe tests/postman/sync-test-users.php --apply
```

El primer comando solo revisa; el segundo sincroniza por correo y conserva permisos y estado. Solo escribe en `calcupost_test`, copia los hashes sin revelarlos y no copia tokens ni sesiones. No elimina cuentas exclusivas de pruebas. Es una copia puntual: futuros cambios de contraseña requieren repetir la sincronización. La preparación de tarifas no sincroniza credenciales automáticamente.

En el entorno `testing` se usa una cookie de sesión distinta para que los accesos de ambos puertos no se mezclen y se permite la cookie por HTTP local. Fuera de ese entorno se conserva la configuración HTTPS original.

### Evitar el error 419 del login local

El servidor de pruebas fuerza `SameSite=Lax`: los navegadores rechazan `SameSite=None` si la cookie no tiene `Secure`. En el `.env` local del servidor HTTP principal se usan `SESSION_SECURE_COOKIE=false` y `SESSION_SAME_SITE=lax`; estas opciones locales no deben reemplazar la configuración HTTPS de producción.

Un formulario de acceso caducado se rechaza y redirige a un formulario nuevo con un aviso. La contraseña nunca se conserva ni se reenvía automáticamente. Los demás formularios y solicitudes JSON mantienen la respuesta 419 y la validación CSRF sigue activa.

Prueba de regresión (activa explícitamente CSRF, que PHPUnit normalmente omite):

```powershell
.\.tools\php84\php.exe vendor/phpunit/phpunit/phpunit --filter LoginSessionTest
```

Verificado también en navegador: login de administrador en ambos puertos, recuperación del formulario anterior al cambio y persistencia de sesiones independientes.

## Contrato de la API

`POST /api/calcular` acepta `categoria`, `destino`, `peso` y los campos opcionales `largo`, `ancho`, `alto`, `certificado`, `espreso`, `recibo`. Se conserva el nombre histórico `espreso` para compatibilidad.

La respuesta añade `registro_id`, `registro_guardado` y `sesion_consulta`. El cliente puede enviar el UUID retornado como cabecera `X-Calculator-Session` en consultas posteriores para agruparlas como una sesión. Sin esa cabecera, cada llamada anónima crea una sesión nueva; esto no equivale a contar personas distintas.

Un token Sanctum válido identifica a la cuenta; un token inválido recibe 401. Los IDs de usuario enviados en el cuerpo no identifican al visitante. La API sigue siendo pública y conserva su límite de peticiones.

Datos válidos sin tarifa: HTTP 404 y registro `no_rate`. Fallo al guardar: HTTP 503 y `registro_guardado: false`, nunca una confirmación falsa de guardado.

Documentación oficial: [instalar Postman CLI](https://learning.postman.com/docs/postman-cli/postman-cli-installation/) y [ejecutar una colección local](https://learning.postman.com/docs/postman-cli/postman-cli-run-collection).
