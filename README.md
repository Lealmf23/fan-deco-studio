# Fan Deco Studio

Tema WordPress independiente (sin tema padre, sin Elementor) para la tienda Fan Deco. WooCommerce sigue siendo el motor comercial; el tema solo presenta y no modifica productos, pedidos, precios, descuentos ni configuración.

> **Estado: MVP (v0.1.0).** Ver [Qué está verificado](#qué-está-verificado-y-qué-no) antes de llevarlo a producción.

## Requisitos
- Desarrollo: Node 20+ y npm. Producción: WordPress 6.5+, PHP 8.0+ (objetivo 8.2; confirmar la versión real en Hostinger), WooCommerce 8+. **No** se necesita Node en el servidor.

> Antes de instalar, leer **[docs/MIGRACION.md](docs/MIGRACION.md)** (auditoría del sitio actual, riesgos al salir de Blocksy/Elementor y checklist de pruebas).

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
- **Frontend**: Vite + Tailwind CSS v4 (CSS-first; los tokens están en `src/css/tokens.css` con `@theme`). JS crítico ≈ 4.5 kB (menú, buscador, carrusel, feedback del carrito). **GSAP** está en un chunk aparte que solo se descarga si la página tiene elementos animables y el usuario no prefiere reducir movimiento. Fuente autoalojada (Bricolage Grotesque; subset latino).
- **Render**: 100 % PHP del lado del servidor (SEO). React **no** se usa en el MVP: solo hay un punto de montaje (`[data-fd-mount="configurator"]`) en el bloque *Banner editorial*.
- **Bloques** (`src/blocks/*` → `build/blocks/*`): `fandeco/hero`, `category-grid`, `featured-products`, `editorial-banner`, `benefits`, `process-steps`, `section-heading`. Todos son dinámicos (`render.php`), con `block.json`, valores por defecto, saneamiento/lista blanca en el render, vista previa fiel en el editor (ServerSideRender) y controles en la barra lateral.
- **Contenido editable**: bloques (textos, imágenes, enlaces, orden), Customizer (footer/contacto/redes), menús, y categorías/productos de WooCommerce.
- **Decisión: dos herramientas de build.** Vite para el frontend; `@wordpress/scripts` para los bloques porque genera automáticamente las dependencias `wp-*` (`*.asset.php`) y es la vía más estable para el editor. Documentado a propósito; no se mezclan.
- **Decisión: Tailwind v4** (en lugar de `tailwind.config.js`): la configuración vive en CSS. Los componentes `fd-*` están escritos en CSS con tokens; las utilidades de Tailwind están disponibles para plantillas nuevas.
- **WooCommerce**: `add_theme_support( 'woocommerce' )` + galería (zoom/lightbox/slider), wrappers vía hooks oficiales, fragmentos del carrito (contador), plantilla `woocommerce.php` con `woocommerce_content()`. No se sobrescriben plantillas internas de WooCommerce.

## Plugin Fan Deco Core (reemplazo de AIOSEO)
El SEO vive en un **plugin propio** (`plugins/fan-deco-core/`, ZIP en `release/fan-deco-core.zip`) para que no dependa del tema. Instalarlo en staging junto al tema.
- **Incluye:** títulos por plantilla (`%title% %sep% %sitename%`…) y sobrescritura por contenido/categoría, meta descripción, robots (noindex/nofollow), canonical (también en categorías y paginados), Open Graph/Twitter, JSON-LD (Organization, WebSite+búsqueda, WebPage/CollectionPage, BreadcrumbList; el schema de **Producto** sigue siendo de WooCommerce), verificación de Search Console/Bing/Pinterest/Facebook, sitemap nativo de WordPress ajustado (excluye noindex, etiquetas y autores) y reglas de `robots.txt`, gestor de redirecciones 301/302/307/308 (solo destinos del propio sitio) e **importador de AIOSEO** (con simulación).
- **Convivencia:** mientras AIOSEO (o Yoast/Rank Math/SEOPress) esté activo, el plugin no imprime nada en el frontend; el tema también cede ante el plugin. Ajustes en *Ajustes → Fan Deco SEO*.
- **Migración segura (orden recomendado, en staging):**
  1. `npm run seo:snapshot -- https://fandeco.com.ar/sitemap.xml antes.json` (captura título, descripción, canonical, robots y h1 del sitio actual).
  2. Copia de la base de datos. Instalar tema + plugin. En *Fan Deco SEO* copiar a mano las plantillas globales de títulos de AIOSEO y la portada.
  3. *Importar desde AIOSEO → Simular*, revisar, luego *Importar ahora*.
  4. Desactivar AIOSEO **en staging**, y `npm run seo:snapshot -- https://staging…/sitemap.xml despues.json`.
  5. `npm run seo:diff -- antes.json despues.json` → debe quedar sin diferencias (o solo las deseadas). Recién entonces repetir en producción.
- **No verificado:** todo el plugin se probó solo con `php -l` y pruebas unitarias de la lógica pura (variables, conversión de etiquetas AIOSEO, reglas de redirección). El panel de ajustes, los metaboxes, las salidas en `<head>` y el importador **no se ejecutaron en WordPress real**; el esquema de tablas de AIOSEO se asumió (tablas `aioseo_posts`/`aioseo_terms`, columnas comprobadas en ejecución) y puede requerir ajustes.
- **Fuera de este hito (hoja de ruta):** migas visibles, schema de FAQ/LocalBusiness, monitor de 404, botón de WhatsApp, campos personalizados de producto. **No** se replicarán feeds ni analítica: Google for WooCommerce, Pinterest, MonsterInsights/Site Kit/PixelYourSite ya existen en el sitio (ver `docs/MIGRACION.md`, que además trae la decisión por plugin, riesgos y checklist de staging).

## SEO del tema (respaldo)
- **Convive con AIOSEO**: si AIOSEO, Yoast, Rank Math, SEOPress o TSF están activos, el tema no imprime ningún metadato propio (filtro `fandeco_seo_plugin_active` para forzarlo). Sin plugin SEO, `inc/seo.php` imprime meta description, Open Graph/Twitter y JSON-LD (Organization + WebSite con SearchAction en portada; BreadcrumbList en contenido).
- No duplica lo que ya generan WordPress/WooCommerce: `<title>` (`title-tag`), canonical, schema de **Producto** y migas de WooCommerce, robots/noindex de carrito/checkout/cuenta.
- HTML semántico (`header`/`nav`/`main`/`footer`, `article`, listas), un único `<h1>` por página (la portada agrega uno oculto si se quita el hero), jerarquía h1→h2→h3 en bloques, enlaces de salto, `lang` según el idioma del sitio (es-AR).
- Rendimiento: imagen del hero con `fetchpriority=high` y sin lazy; resto `loading=lazy` con `width/height` (sin saltos de diseño); fuentes autoalojadas con `font-display: swap` y preload de la fuente del cuerpo; JS crítico ≈ 4,5 kB; se quitan generator/emoji/wlwmanifest/rsd.
- Imágenes de producto sin alt reciben el nombre del producto como respaldo.
- No se tocan permalinks ni redirecciones. Sitemaps y robots.txt: los gestiona AIOSEO/WordPress.
- **No ejecutar el importador en el sitio real todavía** (falta la tabla `aioseo_terms`; ver `docs/MIGRACION.md`). **Pendiente validar** en staging: Rich Results Test (producto/migas), Search Console, Lighthouse y que AIOSEO no emita duplicados.

## Dirección visual de la tienda
- **Tipografía:** una sola familia, *Bricolage Grotesque* (variable, autoalojada), para títulos y texto; el "fan" manuscrito del logo queda como único contraste. Títulos grandes y apretados.
- **Listados:** sin cajas blancas ni sombras. Cada pieza va en una ventana de esquinas suaves con un resplandor cálido que se intensifica al pasar el mouse; texto alineado a la izquierda debajo; botón de compra circular con ícono (el texto queda para lectores de pantalla); columnas desfasadas en desktop. Filtros por categoría reales de WooCommerce.
- **Ficha:** miniaturas en columna lateral, imagen principal grande y fija, categoría y etiqueta "Oferta" sobre el título, cuotas y transferencia en una pieza con íconos, selectores de color más grandes.
- **Los listados usan la tarjeta del tema** mediante ganchos de WooCommerce; el botón de compra sale de `woocommerce_template_loop_add_to_cart()` para no romper plugins de seguimiento (PixelYourSite, Google for WooCommerce). Verificar en staging que los eventos *add_to_cart* de listados sigan llegando.
- Etiquetas en mayúsculas con tracking eliminadas; el color y el estilo editorial siguen la paleta del brief.

## Experiencia móvil (tipo app, hasta 767 px)
- **Barra inferior fija** (Inicio, Tienda, Carrito con contador, Cuenta) y **buscador de productos siempre visible**; el header queda con logo y menú. En la ficha de producto, el carrito vuelve al header y la barra se oculta; también se oculta en carrito y checkout.
- **Home:** hero en tarjeta (texto a la izquierda, imagen a la derecha), categorías en una fila horizontal con ícono, productos destacados en carrusel con botón “+” junto al precio.
- **Listados:** tarjetas compactas; los colores disponibles aparecen como puntos sobre la imagen.
- **Ficha:** foto a pantalla completa con miniaturas flotando a la derecha, y una hoja blanca con esquinas redondeadas con título, precio, cuotas, selectores y botón grande de compra.
- Detalles: el contador del carrito de la barra inferior usa su propio fragmento de WooCommerce (`a.fd-tab-cart`) para no interferir con el del header. Se verificó en Chromium con emulación móvil sobre markup de muestra; **falta probar en iPhone/Android reales y con WooCommerce real**.
- Corregido durante esta revisión: las animaciones de aparición dejaban los elementos desplazados 24 px de forma permanente (el CSS reaplicaba el estado inicial); y textos para lectores de pantalla dentro de carruseles ensanchaban la página en móvil.

## Descuento por transferencia (plugin Fan Deco Core)
WooCommerce → **Descuento por transferencia**. Vos definís: activar/desactivar, **porcentaje**, texto, **métodos de pago** que lo reciben (por defecto transferencia bancaria `bacs`) y **a qué productos aplica: todos, ciertas categorías (incluye subcategorías) o ciertos productos** (las variaciones cuentan por su producto padre).
- **Cómo se calcula:** porcentaje × suma de los productos elegibles **después de cupones y sin envío ni otros cargos**. Se registra como un cargo negativo estándar de WooCommerce: aparece en carrito/checkout, pedido, emails y reportes. Se recalcula al cambiar de método de pago. Solo aplica en el checkout (en el carrito todavía no hay método elegido).
- **Seguridad:** viene **desactivado**; si detecta activo el plugin “Payment Gateway Based Fees and Discounts” **no se aplica** hasta que lo desactives y lo confirmes (para no descontar dos veces). Porcentaje limitado a 0–99,99.
- **Impuestos:** por defecto el cargo es gravado cuando WooCommerce tiene impuestos activos (reduce también el IVA incluido en el precio). **Confirmar con el contador** y probar un pedido real.
- **Alcance:** checkout **clásico** (shortcode). No cubre el Checkout por bloques. Los productos en oferta entran en el descuento; los cupones se descuentan antes.
- **Verificado:** lógica de cálculo y gancho con carrito/sesión simulados (25 pruebas). **No verificado en WooCommerce real:** que el total recalculado y el pedido guardado coincidan con lo mostrado, el comportamiento con Mercado Pago/Andreani y con cupones reales, y la pantalla de ajustes.
- Las cuotas y el precio con transferencia de la ficha salen de *Precio y cuotas* y leen este mismo porcentaje, por lo que no pueden quedar desfasados (siempre que se use ese módulo en lugar del snippet `precio_con_cuotas`).

## Interruptor de luz para luminarias (plugin Fan Deco Core)
En la ficha de una lámpara, un interruptor **Luz apagada / encendida** alterna entre dos **fotos reales** (fundido suave, resplandor alrededor; respeta “reducir movimiento”; accesible por teclado, `role="switch"`).
- **Carga:** en el producto, pestaña *General* → **Foto encendida** (la foto principal es la apagada; usar el mismo encuadre y tamaño). Opcional por **variación** (p. ej. una por color) en cada variación. Sin foto encendida no aparece el interruptor: no se simula la luz.
- **Variaciones:** al elegir una variación se usa su foto encendida; si no tiene pero comparte la foto principal, se usa la del producto; si tiene foto propia y no tiene encendida, el interruptor se oculta (para no mostrar una foto equivocada).
- **Alcance:** usa los ganchos de la galería de WooCommerce (`woocommerce_single_product_image_thumbnail_html` y `woocommerce_product_thumbnails`); si una plantilla de producto de Elementor reemplaza la galería, el interruptor no aparece hasta migrar esa plantilla. El JS/CSS solo se cargan en productos que tienen foto encendida.
- **Verificado:** marcado, datos de variación y guardado (permisos, nonce, validación) con stubs, y comportamiento en navegador con una simulación de variaciones (11 pruebas). **No verificado en WooCommerce real:** convivencia con zoom/lightbox/slider de la galería, el selector de fotos del administrador (biblioteca de medios) y el guardado desde la pantalla de variaciones.

## Precio y cuotas (plugin Fan Deco Core)
WooCommerce → **Precio y cuotas**. Reemplaza al snippet `precio_con_cuotas` (que existe porque WooCommerce no muestra en la ficha el cálculo de las cuotas sin interés de Mercado Pago). Muestra **“N cuotas sin interés de $X”** (precio de la ficha ÷ N) y **“$Y con transferencia · 10% OFF”**, y se actualiza solo al elegir una variación (rango de precios con “Desde”).
- **Solo visual:** no cambia precios ni cobra; las cuotas reales las define Mercado Pago (la cantidad configurada acá debe coincidir con la de Mercado Pago). Importe mínimo opcional.
- **Una sola fuente del %:** el precio con transferencia usa el porcentaje y el alcance (todos/categorías/productos, incluso variaciones puntuales) del módulo *Descuento por transferencia*, y solo se muestra si ese descuento está activo. Con impuestos activos y cargo no gravado se oculta para no mostrar un precio distinto al cobrado.
- **Dónde:** debajo del precio en la ficha, opcionalmente en listados/tarjetas, y con el shortcode `[fandeco_cuotas]` para plantillas de Elementor.
- **Al activarlo, desactivar el snippet `precio_con_cuotas`** (si no, el cálculo aparece dos veces). Viene desactivado.
- **Verificado:** cálculo, HTML por escenario y comportamiento en navegador con una simulación de WooCommerce (24 pruebas). **No verificado en WooCommerce real:** posición/estilo en la ficha real y en las plantillas de Elementor, y coincidencia con lo que muestra Mercado Pago al pagar.

## Compatibilidad con funciones propias del sitio
- **Sin Preflight de Tailwind:** el reinicio de estilos está acotado a los componentes del tema (header, footer, secciones), para no alterar el marcado de shortcodes y snippets existentes (configurador de chapitas, cuotas) ni de WPForms/Elementor.
- **Checkout clásico** (shortcodes) estilizado sin cambiar su estructura; carrito/checkout/Mi cuenta usan siempre el contenedor del tema.
- Detalle de snippets, plantillas de Elementor y prioridades: **[docs/MIGRACION.md](docs/MIGRACION.md)**.

## Precio/cuotas y galería
El tema no calcula cuotas ni descuentos. Emite el evento **`fandeco:variation`** (detalle: `variationId`, `price`, `regularPrice`, `inStock`, `form`) para que un componente de precio reaccione a las variaciones. La galería de producto es la nativa de WooCommerce (zoom, lightbox, slider) con el estilo del tema. Detalle en `docs/MIGRACION.md`.

## Selectores visuales de variaciones
Atributos globales de tipo **color** (círculos) y **button** (botones con texto) se muestran como selectores visuales; el `<select>` original de WooCommerce se conserva oculto y es la fuente de verdad (precio, stock e imagen los sigue calculando WooCommerce). Accesibles (radiogroup, teclado), con combinaciones no disponibles tachadas. Color por término: campo propio → datos heredados de Blocksy → nombre conocido → botón con texto. No depende de Blocksy. Atributos personalizados (no globales) y de tipo *select* no se modifican.

## Estilos de WooCommerce
Hay estilos de marca para archivo de tienda/categorías, producto (galería, variaciones, pestañas, relacionados), carrito, checkout (2 columnas en desktop), pedido recibido y Mi cuenta (login/registro, direcciones, pedidos), más ajustes básicos para los bloques de Carrito/Checkout de WooCommerce. Se revisaron en Chromium sobre markup típico de WooCommerce (no sobre WooCommerce real) a 375 y 1280 px; falta confirmarlos con WooCommerce, pasarelas y plugins reales.

## Qué está verificado y qué no
**Verificado aquí (sin WordPress real; el entorno no puede descargar WordPress/WooCommerce):**
- Sintaxis PHP de los 41 archivos (`php -l`, PHP 8.3).
- `npm run build` (Vite y wp-scripts) compila sin errores.
- Pruebas de humo con stubs (`npm test`): los `render.php` y `header.php`/`footer.php` reales generan el markup esperado, saneán entradas hostiles (XSS, `javascript:`, enums inválidos) y respetan el orden manual de productos.
- En Chromium sobre HTML estático generado por esos renders: sin desbordamiento horizontal a 375/820/1440 px, menú móvil abre/cierra (aria-expanded, Esc), reveals de GSAP funcionan y no dejan contenido oculto.

**NO verificado (requiere WordPress + WooCommerce en staging):**
- Activación del tema, carga del editor y registro real de bloques; vista previa ServerSideRender; el selector de categorías/productos del editor (usa REST `product_cat` y `/wp/v2/product`).
- Datos reales de WooCommerce en bloques, AJAX add-to-cart, fragmentos del contador del carrito.
- Estilos de tienda/producto/carrito/checkout/cuenta con WooCommerce real (solo se probó con markup de muestra; el Carrito/Checkout por **bloques** usa sus propios estilos).
- Mercado Pago, Andreani, variaciones, cupones, impuestos, stock: **no probados**; el tema no toca esa lógica, pero hay que testear en staging.
- Compatibilidad con AIOSEO (el tema usa `title-tag` y no imprime metadatos SEO propios), Core Web Vitals, accesibilidad con lectores de pantalla.
- Traducciones JS (`languages/` vacío).
- Swatches de variaciones con WooCommerce real (implementados y probados solo con una simulación del comportamiento de WooCommerce).

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
