# PROMPT MAESTRO — F3: Registro con número ANP + Perfil de afiliado (BACKEND)

> Copia TODO este documento como primer mensaje del chat de implementación.
> Alcance: SOLO backend Laravel. La parte Flutter se hará en un prompt posterior, después de auditar este trabajo.

---

Eres un desarrollador senior de Laravel trabajando sobre una plantilla 6valley V16.3 adaptada para "ANPEC Red Trastienda". Trabaja de forma incremental, sin romper nada existente.

## 1. Contexto del proyecto

- **Stack:** Laravel 12 (`laravel/framework ^12.0`), PHP 8.2+, MySQL. Frontend admin: Blade + Bootstrap 4 + Vue 2 (legacy, ser conservador).
- **Repo:** `redtrastienda-admin` (Laravel en la raíz del repo).
- **Producto:** red de comerciantes afiliados (tenderos) y proveedores (Coca-Cola, Bimbo, etc.). En esta adaptación: Customer = **Afiliado**, Seller/Vendor = **Proveedor**. El admin ya está en español.
- **Deploy:** cPanel via `git pull` SIN terminal. Implicaciones duras:
  - **PROHIBIDO agregar paquetes composer** (`vendor/` va commiteado; no hay composer en el servidor). Usa solo paquetes ya instalados: `maatwebsite/excel` (import/export), `milon/barcode` (QR, para fase posterior), `barryvdh/laravel-dompdf`.
  - Las migraciones se corren en el servidor con un mecanismo aparte (no te ocupes de eso; solo entrega migraciones estándar).
- **Arquitectura obligatoria (ya existente en el repo):** Controller → Service → Repository → Model. Interfaces en `app/Contracts/Repositories/`, implementaciones en `app/Repositories/`, servicios en `app/Services/`. Existe scaffold: `php artisan generate:entity {name}`.
- **Rutas (no estándar):** admin en `routes/admin/routes.php`; API cliente en `routes/rest_api/v1/api.php`.
- **Módulo de referencia (SIGUE ESTE PATRÓN):** el módulo `opportunity_requests` ya existente:
  - `app/Models/OpportunityRequest.php`
  - `app/Contracts/Repositories/OpportunityRequestRepositoryInterface.php`
  - `app/Repositories/OpportunityRequestRepository.php`
  - `app/Http/Controllers/RestAPI/v1/OpportunityRequestController.php`
  - migración `2026_07_08_120000_create_opportunity_requests_table.php`
  - rutas agregadas al final de `routes/rest_api/v1/api.php`
- **Módulo admin de referencia:** grupo `customer` en `routes/admin/routes.php` (~línea 354, prefix `customer`, middleware `module:people`), controlador `app/Http/Controllers/Admin/Customer/CustomerController.php`, vistas en `resources/views/admin-views/customer/`.
- **Registro actual de clientes:** `POST /api/v1/auth/register` → `app/Http/Controllers/RestAPI/v1/auth/CustomerAPIAuthController.php::register`. También existe `registration-with-otp` y login social — revísalos, pero el flujo principal a extender es `register`.
- **Menú lateral admin:** `resources/views/layouts/admin/partials/v2/_side-bar.blade.php` (verifica cuál sidebar usa el layout activo; existe también `_side-bar.blade.php` sin v2). Agrega las entradas junto a las de Customer/Afiliados.
- **Traducciones:** en Blade TODO texto va con `translate('clave')`. Además, AGREGA las claves nuevas con su traducción al español directamente en `resources/lang/es/messages.php` (solo agregar entradas; jamás regenerar/reordenar ese archivo).

## 2. Objetivo de la fase

El número ANP es la llave de entrada del comerciante a Red Trastienda:

1. ANPEC genera/importa números ANP (admin).
2. El comerciante se registra con su número ANP.
3. El sistema valida que el ANP exista y esté disponible, lo consume, y crea el perfil de afiliado en estatus **pendiente**.
4. ANPEC aprueba/rechaza/bloquea afiliados desde el admin.

## 3. Modelo de datos (2 tablas nuevas; NO tocar tablas core)

### `numeros_anp`
| campo | tipo | notas |
|---|---|---|
| id | bigIncrements | |
| numero_anp | string(50) UNIQUE | alfanumérico libre |
| estatus | enum/string: `disponible`,`usado`,`bloqueado`,`cancelado` | default `disponible`, indexado |
| afiliado_asignado | FK nullable → users.id | quién lo usó |
| fecha_generacion | timestamp | default now |
| fecha_activacion | timestamp nullable | cuándo se registró el comerciante |
| operador | string nullable | admin que lo generó/importó |
| observaciones | text nullable | |
| timestamps | | |

### `affiliate_profiles`
| campo | tipo | notas |
|---|---|---|
| id | bigIncrements | |
| customer_id | FK UNIQUE → users.id | |
| numero_anp | string(50) UNIQUE | |
| nombre_negocio | string | |
| whatsapp | string nullable | |
| direccion | string nullable | |
| estado | string nullable | |
| municipio | string nullable | |
| colonia | string nullable | |
| foto_negocio | string nullable | path de imagen |
| estatus | `pendiente`,`activo`,`rechazado`,`bloqueado` | default `pendiente`, indexado |
| approved_at | timestamp nullable | |
| approved_by | string nullable | admin que aprobó |
| timestamps | | |

Los nombres de campos van en español tal cual (así lo pide el alcance oficial). Nombre y teléfono del comerciante NO se duplican: viven en `users`.

## 4. Backend API (afiliado)

Extiende `CustomerAPIAuthController::register` (y el flujo OTP si comparte lógica) SIN romper compatibilidad:

1. **Toggle de negocio:** nueva business setting `numero_anp_obligatorio` (tabla business_settings, patrón `updateOrInsert(type:..., value:...)` como `digital_product`). **Default `0`** — la app Flutter actual no envía ANP todavía; cuando el campo exista en la app, se activará.
2. **Comportamiento de `register`:**
   - Si llega `numero_anp` en el request (venga o no el toggle activo): validar contra `numeros_anp` → debe existir y estar `disponible`. Si no: error 403 con mensaje traducible claro (`numero_anp_invalido` / `numero_anp_no_disponible`).
   - Si el toggle está en `1` y NO llega `numero_anp`: rechazar con error de validación.
   - Si el ANP es válido: en **una transacción DB**: crear user → crear `affiliate_profiles` (estatus `pendiente`, con `nombre_negocio` si llega en request) → marcar el ANP `usado` (+`afiliado_asignado`, `fecha_activacion`).
   - Sin `numero_anp` y toggle en 0: registro funciona EXACTAMENTE igual que hoy (sin perfil de afiliado).
3. **Nuevo endpoint de pre-validación** (para UX del formulario): `POST /api/v1/auth/check-numero-anp` con `{numero_anp}` → responde si existe y está disponible. Sin auth.
4. **Nuevo endpoint de perfil:** `GET /api/v1/customer/affiliate-profile` (auth:api) → devuelve el perfil de afiliado del usuario logueado (o 404 si no tiene). Servirá para la Tarjeta Digital en la siguiente fase.
5. El login del afiliado NO se bloquea por estar `pendiente` (la aprobación gatea beneficios/tarjeta, no el acceso).

## 5. Módulos admin (2 secciones nuevas, middleware `module:people`)

### 5.1 "Números ANP" (`admin/numeros-anp/...`)
- **Listado** con búsqueda por número y filtro por estatus, paginado (patrón del listado de customers).
- **Generar lote:** form con cantidad N (1–1000) y prefijo opcional → genera N números únicos aleatorios (`PREFIJO-XXXXXX` alfanumérico), estatus `disponible`, `operador` = admin logueado.
- **Importar CSV/Excel** usando `maatwebsite/excel` (ya instalado): columna 1 = numero_anp, columna 2 opcional = observaciones. Duplicados se saltan; al final reporta importados/saltados.
- **Exportar** listado (Excel, patrón export de customers).
- **Acciones por fila:** bloquear/desbloquear, cancelar (solo si `disponible`), ver observaciones. NO se puede editar/borrar un número `usado`.

### 5.2 "Afiliados ANP" (`admin/afiliados/...`)
- **Listado** de `affiliate_profiles` con datos del user (nombre, teléfono), nombre_negocio, numero_anp, estatus; filtro por estatus; búsqueda.
- **Detalle** con todos los campos + foto_negocio.
- **Acciones:** aprobar (`activo`, guarda approved_at/by), rechazar, bloquear/desbloquear. Con confirmación (patrón de toggles existente en admin).
- En el **sidebar**, agrega ambas entradas en la sección de gente/Customers con `translate('numeros_ANP')` y `translate('afiliados_ANP')`.

## 6. Reglas duras (violarlas = trabajo rechazado)

1. **NO tocar:** `app/Providers/RouteServiceProvider.php`, `config/system-addons.php`, `.gitignore`, migraciones existentes, rutas existentes (solo AGREGAR), `resources/lang/es/messages.php` (solo AGREGAR claves al final), `resources/lang/es/new-messages.php`.
2. **NO** modificar la tabla `users` ni ninguna tabla core de 6valley. Solo las 2 tablas nuevas.
3. **NO** `composer require/update`. **NO** tocar `vendor/`.
4. **NO** borrar ni renombrar nada existente. El registro sin ANP debe seguir funcionando idéntico con el toggle en 0.
5. Convenciones del repo: argumentos nombrados en PHP (`func(param: $x)`), imports arriba, controllers delgados, lógica en Services, datos vía Repository con interface registrada (mira cómo se registran las interfaces existentes en `app/Providers/InterfaceServiceProvider.php` — registra ahí las nuevas).
6. Migraciones con prefijo de fecha `2026_07_09_...`.
7. Todo texto visible en Blade con `translate()`, y su español agregado a `resources/lang/es/messages.php`.

## 7. Entregables

1. Trabaja en una rama nueva: `f3-registro-anp` (NO tocar `main`, NO push a main).
2. Lista final de TODOS los archivos creados/modificados, agrupados por tipo.
3. Resumen de decisiones tomadas y cualquier duda/supuesto.
4. Plan de prueba manual: comandos curl para `check-numero-anp`, `register` (con ANP válido, inválido, usado, sin ANP), y pasos de prueba en el admin (generar lote → importar CSV → registrar → aprobar).
5. NO corras migraciones en ningún servidor; solo entrégalas.

## 8. Criterio de aceptación

- `php artisan migrate` limpio sobre la BD existente (2 tablas nuevas).
- Registro actual (sin ANP) intacto.
- Registro con ANP válido → user + perfil `pendiente` + ANP `usado`, atómico.
- ANP inválido/usado/bloqueado → error claro y NO se crea nada.
- Admin: generar lote, importar CSV, bloquear número, aprobar/rechazar afiliado — todo funcional y en español.
