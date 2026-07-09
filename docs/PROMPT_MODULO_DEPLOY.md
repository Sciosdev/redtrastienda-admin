# PROMPT MAESTRO — Módulo admin "Actualizar desde GitHub" (botón de deploy)

> Copia TODO este documento como primer mensaje del chat. Trabajo en el repo `redtrastienda-admin` (Laravel).
> Puede correr EN PARALELO con F5/F7 (que son solo apps Flutter) → repos distintos, cero conflicto.
> ⚠️ Es una función con capacidad de ejecutar git + migraciones desde la web. La SEGURIDAD es requisito, no opcional.

---

Eres un desarrollador senior de Laravel. Vas a construir una página de administración que ejecuta el deploy del propio sitio (pull desde GitHub + migraciones + limpieza de caché) con un botón, para reemplazar el script temporal manual que hoy se crea/borra a mano. Conservador, siguiendo las convenciones del repo.

## 1. Contexto

- **Stack:** Laravel 12, PHP 8.3, MySQL. Admin en Blade + Bootstrap 4. Repo `redtrastienda-admin` (Laravel en la raíz).
- **Deploy actual (el que hay que automatizar):** cPanel SIN terminal, por `git pull`. Hoy se hace con un script web temporal (`public/_deploy_fix.php`) que corre vía `exec()` (habilitado; git 2.52 en PATH) estos pasos:
  1. Respaldar `config/system-addons.php` (estado de activación de licencia; hoy está gitignored, pero por seguridad se respalda).
  2. `git fetch origin`
  3. `git reset --hard origin/main`  *(el repo es dueño de los lang files runtime-mutables; por eso reset --hard, no merge — el botón de cPanel "Update from Remote" ABORTA por esos archivos).*
  4. Restaurar `config/system-addons.php` si hacía falta.
  5. Bootstrap Laravel y correr `migrate --force`, `package:discover`, `optimize:clear`.
- **Objetivo:** una página admin con un botón que haga exactamente eso y muestre la salida, con seguridad y registro.
- **Convenciones del repo:**
  - Controller → Service → Repository. Controllers admin extienden `App\Http\Controllers\BaseController`.
  - Rutas admin en `routes/admin/routes.php` (grupo `admin`, muchas bajo `system-setup`). Vistas en `resources/views/admin-views/`. Sidebar: `resources/views/layouts/admin/partials/v2/_side-bar.blade.php`.
  - Auto-bind de interfaces por convención (no tocar providers).
  - Textos en Blade con `translate('clave')` + su ES agregado al final de `resources/lang/es/messages.php`.

## 2. Qué construir

Una sección admin **"Actualizar sistema"** / **"Deploy desde GitHub"**:

1. **Página (GET):** muestra info del estado (rama actual, último commit local `git rev-parse --short HEAD`, y si hay commits nuevos en `origin/main` — `git fetch` + comparar), y un botón grande **"Actualizar desde GitHub"** con una **confirmación** (modal o "escribe ACTUALIZAR para confirmar").
2. **Acción (POST):** ejecuta los pasos del deploy (sección 1) vía `exec()` + Artisan bootstrap, y devuelve/streama la **salida completa** (stdout de cada comando + resultado de cada artisan) para mostrarla en la página (AJAX o render con la salida).
3. **Registro:** cada ejecución se guarda (quién = admin logueado, cuándo, y la salida) — en un archivo de log dedicado (`storage/logs/deploy.log`) o una tabla simple. Mínimo: log a archivo.
4. **Manejo de errores:** si un `exec` devuelve código ≠ 0 o un artisan lanza excepción, muéstralo claramente sin romper la página.

### Lógica de deploy (replica exacta de lo que ya funciona)
```
backup config/system-addons.php  → sys_get_temp_dir()
exec: git fetch origin
exec: git reset --hard origin/main
restore config/system-addons.php si el archivo desapareció
Artisan::call('migrate', ['--force' => true])
Artisan::call('package:discover')  // o el equivalente; regenera bootstrap/cache/services.php
Artisan::call('optimize:clear')
opcache_reset() si existe
```
Corre `chdir(base_path())` antes de los `exec` de git. Usa la MISMA instancia de PHP (Artisan::call in-process), no un binario CLI externo.

## 3. SEGURIDAD (requisitos duros — sin esto el trabajo se rechaza)

1. **Solo admin autenticado**, detrás del middleware `admin` (y el grupo admin existente). Además, **restringe a master admin** (`auth('admin')->id() == 1`) o a un permiso de módulo claro; un admin normal NO debe poder dispararlo.
2. La acción destructiva es **POST + CSRF** (nunca GET).
3. **Confirmación explícita** del usuario antes de ejecutar (modal / palabra clave).
4. **Interruptor de apagado:** gatea todo el módulo detrás de una env var, ej. `DEPLOY_PANEL_ENABLED` (si no está en `true`, la ruta responde 404/403). Así se puede desactivar sin quitar código.
5. **Registra** cada ejecución (admin, timestamp, salida) en `storage/logs/deploy.log`.
6. NO expongas secretos en la salida (filtra credenciales del `.env` si por alguna razón aparecieran; en principio no deberían).
7. No aceptes parámetros del usuario que se interpolen en comandos shell (los comandos son fijos; no `git checkout <input>` ni nada por el estilo).

## 4. Reglas duras del proyecto

1. **NO toques:** `app/Providers/RouteServiceProvider.php`, `config/system-addons.php`, `.gitignore`, rutas/migraciones existentes (solo AGREGA), `resources/lang/es/new-messages.php`. En `messages.php` y rutas/sidebar: **solo agrega**.
2. **NO** `composer require/update`; nada de paquetes nuevos (usa `Illuminate\Support\Facades\Artisan` y `exec()` nativo).
3. **Controllers admin que extiendan `BaseController` DEBEN declarar `index(?Request $request, ?string $type = null): View|...`** (firma de `App\Contracts\ControllerInterface`), NO `index(Request $request): View` — si no, error fatal de incompatibilidad. (Ya nos pasó; no lo repitas.)
4. Argumentos nombrados en PHP, imports arriba, controller delgado (lógica en un Service, ej. `DeployService`).
5. Migración con prefijo `2026_07_...` solo si decides tabla de log (opcional; el log a archivo es suficiente).
6. Trabaja en rama nueva `deploy-module` (NO en `main`).

## 5. Nota importante (huevo-gallina del primer deploy)

El módulo se despliega la PRIMERA vez con el script manual `_deploy_fix.php` (porque el botón aún no existe en el servidor). De ahí en adelante, el botón se sirve solo. El `git reset --hard origin/main` que corre el botón reseteará TODO el repo incluyendo el propio módulo — está bien, siempre que el módulo esté commiteado y pusheado antes. Menciónalo en tu entrega.

## 6. Entregables

1. Rama `deploy-module` con: ruta(s) admin, `DeployController` (extiende BaseController, firma correcta), `DeployService`, vista Blade, entrada en el sidebar, claves ES en `messages.php`, y la lógica de log.
2. Lista de archivos creados/modificados (una línea c/u).
3. Cómo se activa (la env var `DEPLOY_PANEL_ENABLED`) y cómo se restringe (master admin).
4. Explicación de la seguridad implementada (checklist de la sección 3).
5. Como NO hay PHP local para ejecutar: revisión estática + `php -l` mental. NO corras nada en el servidor. NO push (yo audito primero).

## 7. Criterio de aceptación

- Página admin protegida (master admin + env flag) con botón que, al confirmar, corre fetch + reset --hard origin/main + migrate --force + package:discover + optimize:clear vía exec/Artisan, muestra la salida y la registra en log.
- Seguridad de la sección 3 completa (POST+CSRF, confirmación, restricción, flag, log).
- Cero violaciones a las reglas duras (incluida la firma de `index`).
