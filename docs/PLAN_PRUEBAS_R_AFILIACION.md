# Plan de pruebas R-Afiliación (contra el servidor, tras deploy)

Como en F3: NO hay entorno PHP local. Estas pruebas se corren contra el servidor
después del `git pull` + `php artisan migrate` (la migración `2026_07_10_100000_update_affiliate_profiles_for_r_afiliacion` debe pasar limpia sobre la BD existente).

Variables: `BASE=https://<dominio>` — ajustar. Todos los curl de API llevan `-H "Content-Type: application/json" -H "Accept: application/json"`.

---

## A. Import de afiliados (panel admin, con sesión)

El import es un POST del panel (CSRF + sesión): probarlo desde la UI en
**Números ANP → Importar afiliados (Excel completo)** con `Usuarios.csv` real.

1. **Primera corrida**: subir `Usuarios.csv` (18,698 filas + encabezado).
   - Esperado: `Afiliados creados: ~18,532 | Actualizados: 0 | Sin cambios: 0 | Saltados (cuenta ya activada): 0 | Saltados (sin patrón ANP): ~166` (el resto de contadores en 0, salvo anomalías reales).
   - Medir el tiempo. Si el hosting corta la request (>~5 min): partir el CSV en 2–4 partes con el mismo encabezado y subirlas en orden — el import es idempotente.
2. **Segunda corrida del MISMO archivo**: `creados: 0`, todo cae en `Sin cambios` (o `Actualizados` si algún perfil tenía huecos). JAMÁS cambia email/contraseña de nadie.
3. **No pisa reclamadas**: activar una cuenta (sección C), volver a importar → esa fila cae en `Saltados (cuenta ya activada)` y su correo/contraseña NO cambian.
4. **No revive bloqueados**: bloquear un número `disponible` sin perfil desde el panel, importar una fila con ese número → cae en `Saltados (número bloqueado/cancelado)`.
5. **Alta manual**: crear un afiliado con número nuevo → aparece en Afiliados con estatus `activo`, sin activar, y el número queda `usado`.

## B. check-numero-anp extendido (compat + campos nuevos)

```bash
# Precargado sin activar (usar un username real del CSV, ej. anp12268):
curl -s -X POST $BASE/api/v1/auth/check-numero-anp \
  -H "Content-Type: application/json" \
  -d '{"numero_anp":"ANP12268"}'
# Esperado: {"existe":true,"disponible":false,"precargado":true,"reclamada":false,
#            "factor":"telefono"|"nombre"|"ninguno","message":...}
# Los campos existe/disponible/message NO cambian (compat con app vieja).

# Número inexistente:
curl -s -X POST $BASE/api/v1/auth/check-numero-anp -H "Content-Type: application/json" \
  -d '{"numero_anp":"ANP99999999"}'
# Esperado: existe:false, precargado:false, factor:null
```

## C. Activación (claim)

```bash
# 1. Identidad con teléfono CORRECTO (el de profile_field_phone de esa fila):
curl -s -X POST $BASE/api/v1/auth/anp/verificar-identidad \
  -H "Content-Type: application/json" \
  -d '{"numero_anp":"ANP12268","telefono":"6442278760"}'
# Esperado 200: {"claim_token":"...","expira_en_minutos":15,...}

# 2. Teléfono INCORRECTO:
curl -s -X POST $BASE/api/v1/auth/anp/verificar-identidad \
  -H "Content-Type: application/json" \
  -d '{"numero_anp":"ANP12268","telefono":"5500000000"}'
# Esperado 403: errors[0].code = "identidad_no_coincide"

# 3. Bloqueo por fallos: repetir el paso 2 diez veces → a partir del intento 11:
# Esperado 403: code = "intentos_bloqueados" ("contacta a ANPEC").
# NOTA: la ruta también lleva throttle:10,1 (10 req/min por IP) → 429 si se corre muy rápido; esperar 1 min entre tandas.

# 4. Celda con DOS teléfonos ("7443578099/7441822823"): probar AMBOS números
# → los dos deben dar claim_token (matching por secuencia contenida).

# 5. Fila sin teléfono (factor nombre): mandar el nombre con acentos/minúsculas
curl -s -X POST $BASE/api/v1/auth/anp/verificar-identidad \
  -H "Content-Type: application/json" \
  -d '{"numero_anp":"ANPXXXX","nombre":"rosario aidee ramirez"}'
# Esperado 200 con claim_token (matching tolerante, tokens >= 2).

# 6. Activar cuenta con correo NUEVO:
curl -s -X POST $BASE/api/v1/auth/anp/activar-cuenta \
  -H "Content-Type: application/json" \
  -d '{"claim_token":"<token>","correo_real":"prueba.activacion@test.com","password":"secreto1","password_confirmation":"secreto1"}'
# Esperado 200: {"token":"<passport>", "message":...}

# 7. Activar con correo YA REGISTRADO:
# Esperado 403: code = "correo_en_uso" ("ese correo ya tiene cuenta, inicia sesión").

# 8. claim_token reusado o vencido (>15 min):
# Esperado 403: code = "claim_token" ("la verificación expiró").
```

## D. Login

```bash
# Con número ANP (cuenta activada en C):
curl -s -X POST $BASE/api/v1/auth/login -H "Content-Type: application/json" \
  -d '{"email_or_phone":"ANP12268","password":"secreto1","type":"email"}'
# Esperado 200: {"token":...} — también probar "anp12268" en minúsculas.

# Con el correo real:
curl -s -X POST $BASE/api/v1/auth/login -H "Content-Type: application/json" \
  -d '{"email_or_phone":"prueba.activacion@test.com","password":"secreto1","type":"email"}'
# Esperado 200: token.

# Con ANP de cuenta SIN activar:
curl -s -X POST $BASE/api/v1/auth/login -H "Content-Type: application/json" \
  -d '{"email_or_phone":"ANP14873A","password":"loquesea","type":"email"}'
# Esperado 403: code = "cuenta_sin_activar".

# Con el email sintético de una cuenta sin activar + cualquier contraseña:
# Esperado: falla GENÉRICA (credentials_doesnt_match) — no revela nada.
```

## E. Lead (registro sin número)

```bash
# 1. Registro de lead:
curl -s -X POST $BASE/api/v1/auth/register -H "Content-Type: application/json" \
  -d '{"f_name":"Juan","l_name":"Prueba","email":"lead.prueba@test.com","phone":"+525599887766","password":"secreto1","es_lead":true,"nombre_negocio":"Tiendita Prueba"}'
# Esperado 200: {"lead":true,"message":"Tu solicitud quedó registrada..."} SIN token.
# Si el mail está configurado: llega aviso al correo de la empresa. Si no, NO truena.

# 2. Login del lead (bloqueado):
curl -s -X POST $BASE/api/v1/auth/login -H "Content-Type: application/json" \
  -d '{"email_or_phone":"lead.prueba@test.com","password":"secreto1","type":"email"}'
# Esperado 403: code = "lead_pendiente" ("tu afiliación está en proceso").
# Si social login está activo, verificar que Google/Apple con ese correo TAMPOCO emite token.

# 3. Panel: Afiliados → botón "Leads sin número" (badge con conteo) → ver detalle
#    → sección "Asignar número ANP" → asignar uno disponible o nuevo.

# 4. Login del lead DESPUÉS de asignarle número:
# (mismo curl del paso 2) Esperado 200: token. El perfil quedó estatus activo con su número.
```

## F. Perfil extendido y regresión F3

```bash
# Perfil con token de C.6:
curl -s $BASE/api/v1/customer/affiliate-profile -H "Authorization: Bearer <token>"
# Esperado: incluye "reclamada":true y "campos_faltantes":[...]; NO incluye datos_importacion.

# Regresión: registro legacy sin ANP (toggle numero_anp_obligatorio OFF) → 200 con token.
# Regresión: registro con ANP disponible (flujo F3) → 200, perfil pendiente, número usado.
# Regresión: check-numero-anp de un número "disponible" → disponible:true (igual que F3).
```

## G. Smoke test en device (A059P, id 00170155D001304)

1. Login: campo dice "Correo, teléfono o número ANP"; abajo los CTAs "Ya soy afiliado ANPEC — activa tu cuenta" y "¿Aún no eres afiliado? Quiero afiliarme".
2. Activar una cuenta precargada real del CSV: número → teléfono → correo + contraseña → sesión directa → bottom sheet "completa tu perfil" (no bloqueante) → home. Tarjeta Digital pinta el número.
3. Intentar login con un ANP sin activar → diálogo con CTA que abre el wizard.
4. Flujo lead completo: "Quiero afiliarme" → form → diálogo de éxito → home como invitado → (tras asignar número en admin) login OK con sus credenciales.
5. Login por ANP y por correo real desde la app.
