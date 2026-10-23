# Puesta en producción sin pérdida de datos

Los cambios de código no ejecutan migraciones ni rotan secretos automáticamente. Antes de desplegar:

1. Haga un respaldo consistente de la base activa y compruebe que puede restaurarse en un entorno aislado. Conserve también una copia de `.env`, `.env.docker` y del volcado local en un almacén privado. No los suba a Git.
2. Compruebe en la base activa los rangos de `tarifario` que se superponen o dejan huecos. Mantenga los importes originales hasta contrastarlos con el tarifario oficial. El cálculo rechaza cotizaciones con importes contradictorios y registra el motivo.
   Puede ejecutar `php artisan tariffs:check`; el comando solo lee identificadores, categorías y límites de peso.
   En la revisión local del 23 de septiembre de 2026 aparecieron cinco casos: PLIEGOS NAT (160/161), SUPER DOC NAT (247/248), SUPER NAT (hueco 226/227) y SUPER PAQUE NAT (263/264 y 266/267). Vuelva a ejecutar el comando al desplegar, porque los datos pueden haber cambiado.
   Confirme también qué países pertenecen a las zonas internacionales F y G; la interfaz las identifica por zona mientras llega ese mapeo.
3. Despliegue el código y ejecute migraciones solo después del respaldo verificado. La migración nueva agrega índices; no convierte ni elimina registros. Revise el tiempo de bloqueo de índices en una copia con volumen comparable.
4. Cree una credencial nueva de MySQL con los privilegios mínimos necesarios. Actualice `.env.docker`, reinicie el servicio y verifique lectura, cálculo, historial y administración. Revoque la credencial expuesta únicamente después de esas comprobaciones. No reutilice la contraseña anterior.
5. Planifique por separado la rotación de `APP_KEY`: inventaríe datos persistentes cifrados, preserve la clave anterior de forma segura, programe el cierre de sesiones y genere una clave nueva. La clave actual **no se rota automáticamente**, porque hacerlo invalidaría sesiones y borradores.
6. Una vez rotados los secretos, coordine la retirada de la credencial y del volcado del historial Git con todos los clones y respaldos. Reescribir historia requiere un plan compartido y verificación de los datos conservados.

El archivo `calcupost.sql` permanece en este equipo, pero ya no está incluido en el índice de Git. El volcado puede contener datos personales; trátelo como confidencial.
