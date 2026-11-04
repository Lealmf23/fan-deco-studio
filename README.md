# Fan Deco Studio

Tema WordPress independiente (sin tema padre, sin Elementor) para la tienda Fan Deco. WooCommerce sigue siendo el motor comercial; el tema solo presenta y no modifica productos, pedidos, precios, descuentos ni configuración.

> **Estado: MVP (v0.1.0).** Ver [Qué está verificado](#qué-está-verificado-y-qué-no) antes de llevarlo a producción.

## Requisitos
- Desarrollo: Node 20+ y npm. Producción: WordPress 6.5+, PHP 8.0+ (objetivo 8.2; confirmar la versión real en Hostinger), WooCommerce 8+. **No** se necesita Node en el servidor.

## Instalación (sitio)
1. `npm install && npm run package` → genera `release/fan-deco-studio.zip` (también viene incluido en el repo).
2. En **staging**: Apariencia → Temas → Añadir nuevo → Subir tema → activar. No activar primero en producción. Blocksy y Elementor no se tocan.
3. Crear una página "Inicio", editarla con el editor, insertar el patrón **Fan Deco: Portada completa** (categoría "Fan Deco"), y en Ajustes → Lectura fijarla como página de inicio. Si no hay página asignada, `front-page.php` muestra el patrón por defecto.
4. Apariencia → Menús: asignar menús a *Menú principal*, *Footer: navegación* y *Footer: políticas legales*.
5. Apariencia → Personalizar → **Fan Deco Studio**: logo para fondos oscuros, barra superior, contacto, redes, medios de pago, envíos.
6. Subir el **logo oficial** (Identidad del sitio). Mientras no exista, el tema muestra una reserva tipográfica "fan / DECO" que no es el logo oficial.

## Desarrollo
| Comando | Qué hace |
|---|---|
| `npm install` | Instala dependencias |
| `npm run dev` | Vite con HMR (CSS/JS). Agregar `define( 'FANDECO_VITE_DEV', true );` en `wp-config.php` local |
| `npm run dev:blocks` | Compila bloques en modo watch |
| `npm run build` | Compila frontend (`dist/`) y bloques (`build/blocks/`) |
| `npm run package` | Build + ZIP instalable en `release/` (solo archivos de producción) |
| `npm run lint:php` / `npm test` | Sintaxis PHP / pruebas de humo con stubs (ESLint no está configurado: pendiente) |

`dist/` y `build/` están versionados para que el tema sea instalable directamente desde el repo; regenerarlos con `npm run build` tras cada cambio.

## Arquitectura
- **Frontend**: Vite + Tailwind CSS v4 (CSS-first; los tokens están en `src/css/tokens.css` con `@theme`). JS crítico ≈ 4.5 kB (menú, buscador, carrusel, feedback del carrito). **GSAP** está en un chunk aparte que solo se descarga si la página tiene elementos animables y el usuario no prefiere reducir movimiento. Fuentes autoalojadas (Inter, Fraunces; subset latino).
- **Render**: 100 % PHP del lado del servidor (SEO). React **no** se usa en el MVP: solo hay un punto de montaje (`[data-fd-mount="configurator"]`) en el bloque *Banner editorial*.
- **Bloques** (`src/blocks/*` → `build/blocks/*`): `fandeco/hero`, `category-grid`, `featured-products`, `editorial-banner`, `benefits`, `process-steps`, `section-heading`. Todos son dinámicos (`render.php`), con `block.json`, valores por defecto, saneamiento/lista blanca en el render, vista previa fiel en el editor (ServerSideRender) y controles en la barra lateral.
- **Contenido editable**: bloques (textos, imágenes, enlaces, orden), Customizer (footer/contacto/redes), menús, y categorías/productos de WooCommerce.
- **Decisión: dos herramientas de build.** Vite para el frontend; `@wordpress/scripts` para los bloques porque genera automáticamente las dependencias `wp-*` (`*.asset.php`) y es la vía más estable para el editor. Documentado a propósito; no se mezclan.
- **Decisión: Tailwind v4** (en lugar de `tailwind.config.js`): la configuración vive en CSS. Los componentes `fd-*` están escritos en CSS con tokens; las utilidades de Tailwind están disponibles para plantillas nuevas.
- **WooCommerce**: `add_theme_support( 'woocommerce' )` + galería (zoom/lightbox/slider), wrappers vía hooks oficiales, fragmentos del carrito (contador), plantilla `woocommerce.php` con `woocommerce_content()`. No se sobrescriben plantillas internas de WooCommerce.

## Qué está verificado y qué no
**Verificado aquí (sin WordPress real; el entorno no puede descargar WordPress/WooCommerce):**
- Sintaxis PHP de los 41 archivos (`php -l`, PHP 8.3).
- `npm run build` (Vite y wp-scripts) compila sin errores.
- Pruebas de humo con stubs (`npm test`): los `render.php` y `header.php`/`footer.php` reales generan el markup esperado, saneán entradas hostiles (XSS, `javascript:`, enums inválidos) y respetan el orden manual de productos.
- En Chromium sobre HTML estático generado por esos renders: sin desbordamiento horizontal a 375/820/1440 px, menú móvil abre/cierra (aria-expanded, Esc), reveals de GSAP funcionan y no dejan contenido oculto.

**NO verificado (requiere WordPress + WooCommerce en staging):**
- Activación del tema, carga del editor y registro real de bloques; vista previa ServerSideRender; el selector de categorías/productos del editor (usa REST `product_cat` y `/wp/v2/product`).
- Datos reales de WooCommerce en bloques, AJAX add-to-cart, fragmentos del contador del carrito.
- Estilos de tienda, producto, carrito, checkout y Mi cuenta (son una primera capa; el Carrito/Checkout por **bloques** de WooCommerce usa sus propios estilos).
- Mercado Pago, Andreani, variaciones, cupones, impuestos, stock: **no probados**; el tema no toca esa lógica, pero hay que testear en staging.
- Compatibilidad con AIOSEO (el tema usa `title-tag` y no imprime metadatos SEO propios), Core Web Vitals, accesibilidad con lectores de pantalla.
- Traducciones JS (`languages/` vacío).

## Contenido por defecto (revisar antes de publicar)
Los textos del hero/mascotas/proceso son los del brief. Los beneficios por defecto incluyen **“10% OFF con transferencia bancaria”** como texto editorial (no aplica ni modifica ningún descuento). No se incluyen productos, precios ni imágenes de demostración: sin imagen se muestra un degradé de marca.

## Pendientes del siguiente hito
1. Probar en staging con WooCommerce real (checklist de la sección anterior) y corregir.
2. Logo oficial (SVG claro/oscuro) y fotografías reales (hero, categorías, mascotas).
3. Plantillas WooCommerce refinadas (galería de producto, variaciones con swatches, mini-carrito/drawer, filtros del archivo).
4. Bloque de suscripción a novedades (hoy solo hay un comentario placeholder en el footer).
5. Configurador 3D: componente React + React Three Fiber montado en `[data-fd-mount]`, sincronizado con variaciones.
6. Traducciones (`.pot`), PHPCS (WordPress-Coding-Standards) y tests de bloques en CI; revisar `npm audit` de devDependencies.
7. Medir Lighthouse/CWV en staging.
