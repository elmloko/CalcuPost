# Auditoría y correcciones de CalcuPost

Fecha: 23 de octubre de 2026. Revisión realizada por tres agentes y verificación integrada con PHP 8.4.25 y MySQL aislado en `calcupost_test`.

## Resultado

Se corrigieron problemas demostrables de seguridad, integridad de tarifas y operación. La suite pasó de **68 pruebas / 6.386 aserciones** a **101 pruebas / 6.533 aserciones**, todas aprobadas. Se conservaron los cambios que ya existían al comenzar; el diff total del repositorio incluye trabajo previo y no representa exclusivamente esta auditoría.

**La publicación sigue condicionada a validar Docker en ejecución, configurar el entorno real y resolver las inconsistencias del tarifario con una fuente autorizada.** No se ha desplegado el sistema ni se modificó la base principal. Una suite aprobada no certifica ausencia absoluta de errores, cobertura completa ni capacidad bajo carga real.

## Hallazgos corregidos

| Prioridad | Hallazgo y corrección | Evidencia actual |
| --- | --- | --- |
| Crítica | El seeder creaba una cuenta administrativa con contraseña fija y promovía por correo. Se eliminó; la migración conserva únicamente asignaciones de roles reales. | `database/seeders/DatabaseSeeder.php`, `database/migrations/2026_09_01_000002_replace_roles_with_admin_flag.php` |
| Alta | Alta inicial de administrador sin credenciales predeterminadas: comando interactivo, contraseña oculta y confirmada, mínimo 12 caracteres, sin promoción de cuentas preexistentes. | `app/Console/Commands/CreateAdministrator.php:17` |
| Alta | Sesiones previamente abiertas podían sobrevivir al cambio/restablecimiento de contraseña. Se incorporó `AuthenticateSession`, preservando la sesión actual al actualizar la contraseña. Se verificó el orden con CSRF. | `app/Http/Kernel.php:38`, controladores de contraseña, pruebas de autenticación |
| Alta | La edición de tarifas aceptaba campos ocultos no validados. Creación y edición ahora persisten únicamente columnas del perfil, con precisión y límites compatibles con DECIMAL. | `app/Livewire/Tarifario.php:444`, `:566` |
| Alta | Dos bajas simultáneas podían eliminar el último par de administradores. Los tres caminos de desactivación usan una transacción compartida y bloqueo ordenado de administradores activos. | `app/Models/User.php:63`, controladores de perfil/personal y Livewire Users |
| Media | La creación de EMS nacional omitía Provincia Dentro Departamento. Se unificó el mapeo de campos y se añadieron regresiones. | `app/Livewire/Tarifario.php:467` |
| Media | Crear una tarifa desde la pantalla sin selección podía romper la vista. Se sincronizan servicio, modalidad y categoría. | `app/Livewire/Tarifario.php`, pruebas de tarifas |
| Media | Rangos superpuestos podían introducir nuevas ambigüedades. La creación/edición comprueba superposición dentro de transacción con bloqueo del servicio. | `app/Livewire/Tarifario.php:444`, `:467` |
| Media | El precio visible podía quedar obsoleto tras editar datos. Se invalida en servidor y se oculta inmediatamente mediante `wire:dirty.remove`. | `app/Livewire/Calculadora.php:95`, `resources/views/livewire/calculadora.blade.php:108` |
| Media | Host arbitrario podía influir en enlaces de autenticación. Se activó TrustHosts; proxies HTTPS solo se aceptan desde IP/CIDR configurados. | `app/Http/Kernel.php:17`, `config/trustedproxy.php`, pruebas host/proxy |
| Media | Confirmación y recuperación de contraseña necesitaban validación de tipos y límites de frecuencia. Se añadieron ambos; entradas malformadas no producen errores internos. | `routes/auth.php`, controladores de autenticación |
| Media | Identificador de usuario manipulado desde Livewire podía producir error interno. Se valida como entero y se prueban autorizaciones y escalamiento por perfil. | `app/Livewire/Users.php`, pruebas de perfil |
| Baja | Mensajes de error de personal se imprimían como HTML sin escape. Se reemplazaron por Blade escapado y se probó contenido malicioso. | `resources/views/user/form.blade.php:7`, `tests/Feature/FrontendSafetyTest.php` |
| Operación | Despliegue dependía de montar fuentes e instalar/compilar en cada arranque. Se añadió imagen de producción con assets incorporados, sin Node, Composer ni dependencias PHP de desarrollo. | `Dockerfile`, `docker-compose.production.yml` |
| Operación | Arranque podía generar una nueva clave. Producción exige una clave persistente y fuerza debug desactivado. Cachés se preparan al iniciar; migraciones siguen siendo explícitas. | `docker-production-entrypoint.sh` |
| Pruebas | Una DATABASE_URL externa podía anular la base de pruebas. PHPUnit fuerza entorno/base y vacía la URL; se mantiene el guard de aislamiento. Se eliminó una suite vacía cuyo directorio no existe en un checkout limpio. | `phpunit.xml`, `tests/CreatesApplication.php` |

Las pruebas funcionales de bajas comprueban reglas y modelos precargados. No se ejecutó una simulación de concurrencia con procesos separados.

## Código muerto y dependencias

- Eliminados 12 archivos de andamiaje sin consumidores: AppLayout, su layout/navegación, componentes de dropdown, navegación/modal/botones y ambos archivos JavaScript de Breeze.
- Eliminados Axios y Alpine independientes. La calculadora conserva Alpine provisto por Livewire; los formularios invitados solo necesitan el CSS compilado. Se comprobó una sola carga del runtime Livewire.
- Eliminada una consulta paginada de usuarios cuyo resultado nunca usaba la vista Livewire.
- Sustituidas más de 200 líneas de mapeo redundante en creación de tarifas por el mapeo ya existente de columnas.
- Eliminados ejemplos JavaScript comentados, controles deshabilitados comentados y una variable sin uso de recuperación de contraseña.
- Actualizadas dependencias transitivas compatibles y Vite. Se corrigió `postcss-selector-parser` mediante override a 7.1.7; el CSS generado con parser 6.1.5 y 7.1.7 resultó idéntico byte a byte.

## Verificación

| Comprobación | Resultado |
| --- | --- |
| Suite inicial completa | 68 pruebas / 6.386 aserciones, aprobada |
| Suite final completa | 101 pruebas / 6.533 aserciones, aprobada |
| `npm ci` y `npm run build` | Aprobados; lock reproducible y CSS generado |
| `composer validate` y `composer audit --locked` | Aprobados; sin avisos de seguridad ni paquetes abandonados |
| `npm audit --omit=dev` | Sin avisos |
| Sintaxis PHP, Bash y configuración PHPUnit/Compose | 122 archivos PHP sin errores de sintaxis; Bash, PHPUnit y Compose aprobados; no equivalen a ejecutar la imagen |
| Navegador en servidor aislado | EMS nacional, La Paz, 1 kg: 17,00 Bs.; editar peso oculta precio anterior; EMS internacional, Alemania, 2 kg: 405,00 Bs.; sin errores/advertencias JavaScript registrados |
| CI | Workflow añadido para MySQL aislado, auditoría Composer, build y pruebas; aún no ejecutado en GitHub Actions |
| Imagen Docker completa | No ejecutada: motor Docker local no disponible |

Los ensayos de navegador guardaron consultas únicamente en la base de pruebas. El servidor temporal se detuvo al terminar.

## Pendientes antes de producción

1. **Tarifario autorizado:** `tariffs:check` devuelve seis incidencias heredadas: superposiciones en ECA INT, PLIEGOS NAT, SUPER DOC NAT y dos en SUPER PAQUE NAT; un hueco en SUPER NAT. No se inventaron precios ni rangos para ocultarlas. El cálculo rechaza precios contradictorios y comunica ausencia de tarifa. Obtener la corrección oficial y aplicarla antes de ofrecer cobertura completa.
2. **Herramientas de compilación:** quedan cinco alertas altas derivadas de `braces@3.0.3`, sin versión parcheada publicada durante esta revisión. Afectan el árbol de Tailwind/chokidar/micromatch/fast-glob, excluido de la imagen final. Compilar únicamente fuentes y patrones confiables; actualizar al publicarse una solución y repetir auditoría/build. No se afirma que todas las dependencias de desarrollo estén libres de vulnerabilidades.
3. **Entorno real:** construir/arrancar la imagen y probar HTTPS, cookies, SMTP de recuperación, edición de tarifas, permisos y PDFs. Configurar APP_URL, TRUSTED_PROXIES, APP_KEY persistente y credenciales privadas; consultar el procedimiento del README.
4. **Instalaciones heredadas:** revisar administradores existentes y cambiar cualquier contraseña predeterminada previamente creada. Los cambios del seeder y de una migración ya aplicada no reparan cuentas existentes automáticamente.
5. **Respaldo y restauración:** probar recuperación de MySQL y almacenamiento persistente. Varias migraciones de datos no admiten rollback; una migración histórica elimina `contract_tariffs`, acorde al comportamiento y pruebas existentes.
6. **Carga/observabilidad:** medir tráfico y volumen esperados en el servidor final, supervisar errores y recursos y definir retención de historial. No se midieron carga, disponibilidad ni cobertura porcentual.

## Referencias

- [Despliegue Laravel 12](https://laravel.com/framework/docs/12.x/deployment): debug, configuración y cachés.
- [Livewire 3: resolución de problemas](https://livewire.laravel.com/docs/3.x/troubleshooting): evitar múltiples instancias de Alpine.
- [Aviso de braces](https://github.com/advisories/GHSA-vfj7-8cjw-p6xm).
- [Cambios oficiales de postcss-selector-parser](https://github.com/postcss/postcss-selector-parser/blob/master/CHANGELOG.md).
