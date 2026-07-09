# PROMPT MAESTRO — Fix backend: producto se guarda con brand_id=0 (nace invisible)

> Copia TODO esto como primer mensaje. Repo `redtrastienda-admin` (Laravel). Fix chico y puntual.
> Corre EN PARALELO con el smoke test de F7 (que es en el repo de apps) → repos distintos, cero conflicto.

---

Eres un desarrollador senior de Laravel. Arreglas un bug puntual de creación de producto. Conservador, cambio mínimo.

## 1. El bug (diagnóstico ya hecho, con datos reales)

Los productos creados por proveedores nacen **invisibles** en la app de afiliados y en la web (`Product::active()` los excluye), aunque estén `status=1`, `request_status=1` y con seller aprobado.

**Causa raíz confirmada:** el producto se guarda con **`brand_id = 0`** en vez de `null`. El scope `App\Models\Product::scopeActive()` (en `app/Models/Product.php`), con `product_brand` (setting) activado y sin marcas activas que apliquen, exige que el producto tenga una **marca activa** O `brand_id IS NULL`. Un `brand_id = 0` no es ninguna de las dos → el producto queda oculto. (ANPEC no usa marcas, así que casi siempre no hay marca → debe quedar `null`.)

**Dónde se origina:** `app/Services/ProductService.php`, en la construcción de datos del producto (métodos `getAddProductData` y su equivalente de update), líneas ~546 y ~622:
```php
'brand_id' => $request['product_type'] == "physical" ? ( $request['brand_id'] ?? null) : null,
```
El `?? null` solo cubre null/ausente. La app manda `brand_id = "0"` (o cadena vacía), que NO es null → se persiste `0`.

## 2. El fix

En AMBAS ocurrencias (add y update, ~líneas 546 y 622 de `ProductService.php`), haz que un `brand_id` vacío / `0` / `"0"` se guarde como **`null`**, preservando los `brand_id` válidos (> 0). Opción mínima y clara, por ejemplo:
```php
'brand_id' => $request['product_type'] == "physical" ? ($request['brand_id'] ?: null) : null,
```
(`"0"` y `0` son *falsy* en PHP → quedan `null`; un id real como `"5"` se conserva.)

Verifica que no haya OTRO punto donde se persista `brand_id` en creación/edición de producto con el mismo patrón (busca `brand_id` en `ProductService.php` y en los controllers de producto admin/seller). Aplica el mismo criterio donde corresponda, SIN cambiar la lógica de marcas real.

## 3. Reglas duras
1. Cambio MÍNIMO: solo el saneo de `brand_id` a null. No refactorices la creación de producto ni toques el scope `Product::active()` (funciona bien; el bug es el dato que entra).
2. NO toques: `RouteServiceProvider.php`, `system-addons.php`, `.gitignore`, migraciones/rutas existentes, `resources/lang/es/new-messages.php`. No hace falta migración ni lang para esto.
3. Argumentos nombrados, imports arriba, convenciones del repo.
4. NO rompas productos que SÍ tienen marca (brand_id > 0 se conserva).
5. Sin PHP local → revisión estática + `php -l` mental. NO corras nada en el servidor, NO push, NO merge (yo audito).
6. Rama nueva `fix-brand-id` (NO en `main`).

## 4. Nota (datos existentes ya corregidos)
Los 2 productos de prueba que ya existían con `brand_id=0` ya se corrigieron a `null` con un script puntual. Tu fix es para que **los productos FUTUROS** nazcan con `brand_id` null cuando no llevan marca. NO necesitas tocar datos.

## 5. Entregable
1. Rama `fix-brand-id`.
2. Archivos/líneas modificadas con una línea de explicación cada una.
3. Confirmación de que un producto físico sin marca ahora guardaría `brand_id = null` (razónalo), y que los productos con marca válida no se afectan.
