# Operación y Deploy — ANPEC Red Trastienda (admin)

> Runbook operativo del admin (`redtrastienda-admin`). Léelo antes de desplegar o tocar el servidor.
> Contexto base: **cPanel/HostGator SIN terminal**, deploy por **git pull**, `exec()` de PHP SÍ habilitado, git 2.52 en PATH.

---

## 1. Cómo se despliega (regla de oro)

**NUNCA uses el botón "Update from Remote" de cPanel.** Hace un `git merge` que ABORTA porque el servidor tiene archivos que la app modifica en runtime (traducciones, activación). Además no corre migraciones ni limpia caché.

**En su lugar:** editar en local → commit/push a `main` (GitHub Desktop) → correr un **script web temporal** en el servidor que hace todo el deploy. Flujo:

1. Sube tus cambios a `main` en GitHub.
2. En cPanel File Manager, crea `public/_deploy_fix.php` con el contenido de la sección 2.
3. Ábrelo en el navegador: `https://adminapp.redtrastiendaanpec.com/_deploy_fix.php?t=anpec2026`
4. Revisa la salida (que no haya errores; que migraciones digan DONE).
5. **BORRA `public/_deploy_fix.php`** (seguridad — corre git/exec).

## 2. Script de deploy (copiar en `public/_deploy_fix.php`)

```php
<?php
if (($_GET['t'] ?? '') !== 'anpec2026') { http_response_code(403); exit('no'); }
header('Content-Type: text/plain; charset=utf-8');
$repo = realpath(__DIR__ . '/..');
chdir($repo);
function run($cmd){ $o=[]; $r=0; @exec($cmd.' 2>&1',$o,$r); echo "\$ $cmd\n".implode("\n",$o)."\n(exit $r)\n\n"; return $r; }
$sa  = $repo . '/config/system-addons.php';
$bak = sys_get_temp_dir() . '/anpec_system-addons.bak.php';
if (is_file($sa)) { copy($sa, $bak); echo "backup activacion OK\n\n"; }
run('git fetch origin');
run('git reset --hard origin/main');
run('git rev-parse --short HEAD');
if (is_file($bak)) { copy($bak, $sa); @unlink($bak); echo "activacion restaurada\n\n"; }
require $repo . '/vendor/autoload.php';
$app = require_once $repo . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();
$steps = [
    ['migrate', ['--force' => true]],   // quita esta línea si no hay migraciones nuevas
    ['package:discover', []],
    ['optimize:clear', []],
];
foreach ($steps as [$cmd, $params]) {
    try { $kernel->call($cmd, $params); echo "== $cmd ==\n" . trim($kernel->output()) . "\n\n"; }
    catch (\Throwable $e) { echo "== $cmd ERROR: " . $e->getMessage() . "\n\n"; }
}
echo "listo\n";
```

Qué hace: respalda/restaura la activación de licencia → `git reset --hard origin/main` (trae los cambios sin abortar por archivos sucios) → corre migraciones → reconstruye paquetes y limpia caché (registra rutas nuevas).

## 3. Trampas ya resueltas (NO repetir)

| Síntoma | Causa | Solución (ya aplicada) |
|---|---|---|
| Sitio arranca en el **instalador** de 6valley | `app/Providers/RouteServiceProvider.php` tenía commiteado el stub "solo instalador"; el instalador lo reemplaza en el server y `reset --hard` lo revertía | Versión COMPLETA commiteada en el repo. No revertir. |
| Redirige a **"Software Activation Check"** | `config/system-addons.php` (estado de licencia) estaba trackeado con datos dummy; `reset --hard` lo revertía | Se **gitignoreó**; el script lo respalda/restaura. Si aun así pide activar, dale "Check" (revalida con tu PURCHASE_CODE del .env). |
| `pull` aborta: "local changes would be overwritten" | Los lang files (`resources/lang/es/messages.php`, `new-messages.php`) los reescribe la app en runtime | El script usa `reset --hard` (el repo es dueño del español). No uses el botón de cPanel. |
| Rutas admin/API dan 404 tras limpiar caché | Se borró `bootstrap/cache/*.php` sin regenerar | El script corre `package:discover` (regenera). Nunca solo borrar; usar artisan. |

## 4. Datos del servidor

- Home: `/home3/redtrastiendaanp/`, repo en `/home3/redtrastiendaanp/redtrastienda-admin`, docroot → `.../public`.
- PHP 8.3, git 2.52, `exec()` habilitado. `.env` tiene `PURCHASE_CODE`, `APP_INSTALL=true`, credenciales BD.
- BD: `redtrast_webadmin` / usuario `redtrast_usuario` (pass en `.env`, gitignored). La BD NO está vacía → `migrate --force` es seguro (solo corre pendientes).
- Idioma admin: español, gestionado por archivo (`resources/lang/es/messages.php`, versionado). El "Traducir todo" del panel escribe en disco; si lo usas, captura el archivo al repo para no perderlo en el próximo `reset --hard`.

## 5. Verificar que un deploy quedó bien

```
https://adminapp.redtrastiendaanpec.com/                 -> 200 (storefront)
https://adminapp.redtrastiendaanpec.com/login/admin      -> 200 (login español)
https://adminapp.redtrastiendaanpec.com/api/v1/config    -> 200 (JSON ANPEC)
```
`/admin` y `/admin/dashboard` dan **404 para invitados** — es normal (el `AdminMiddleware` hace `abort(404)`). Logueado sí entran.

## 6. Idea a futuro: módulo de "Deploy" con botón

En vez de crear/borrar `_deploy_fix.php` a mano cada vez, se puede construir una página admin protegida ("Actualizar desde GitHub") con un botón que corra los mismos pasos (fetch + reset --hard + migrate + optimize:clear), mostrando la salida. Consideraciones de seguridad: solo detrás del login de admin + permiso de módulo; registrar cada ejecución; confirmación previa. Es un mini-feature con capacidad de ejecutar git/migraciones, así que hay que diseñarlo con cuidado. Ver `docs/` para el prompt cuando se decida hacerlo.
