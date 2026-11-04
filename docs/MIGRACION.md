# Migración de Fan Deco: de Blocksy + Elementor a Fan Deco Studio

Base: auditoría de solo lectura del 4/11/2026 (WordPress 7.1.4, PHP 8.3.36, tema Blocksy 2.1.55, 21 plugins). Los datos del inventario provienen de esa auditoría; **no pude consultar el sitio desde este entorno**.

## 1. Decisión por plugin

| Grupo | Plugin | Decisión | Nota |
|---|---|---|---|
| Comercio | WooCommerce 11.2.1, Mercado Pago, Andreani, Payment Gateway Based Fees and Discounts | **Conservar** | Son el motor comercial. El descuento por transferencia depende del último. El tema no toca precios ni cargos; hay que **probar** que el checkout los muestre y los recalcule al cambiar de método de pago |
| Diseño | Blocksy Companion Pro, Elementor, PRO Elements, HT Slider | **Retirar al final** | Solo después de migrar las páginas y confirmar que nada depende de ellos (ver riesgos) |
| Diseño | Code Snippets | **Auditar primero** | Puede tener lógica de tienda (descuentos, campos, envíos) o enganches a hooks de Blocksy. No actualizar a 4.0 hasta revisarlo |
| SEO | All in One SEO 5.0.4 | **Conservar hasta validar Fan Deco Core**; después reemplazar | Ver sección 4 |
| Marketing | Google for WooCommerce, Pinterest for WooCommerce | **Conservar** | Ya sincronizan catálogo: Fan Deco Core **no** replicará feeds |
| Marketing | MonsterInsights, Site Kit, PixelYourSite | **Consolidar** | Riesgo de GA4/eventos duplicados. Elegir un solo origen por medición. Fan Deco Core **no** agrega analítica |
| Marketing | OptinMonster (inactivo) | Evaluar eliminar | |
| Formularios | WPForms | **Conservar** | El tema trae estilos básicos para sus campos |
| Hostinger | Easy Onboarding, Reach, Tools | Revisar | Easy Onboarding probablemente sobra |
| IA | WPVibe | Conservar mientras dure la migración | |

## 2. Riesgos concretos al cambiar de tema (lo que **no** viene solo)
1. **Variaciones con swatches (colores/formas):** si hoy se muestran como bolitas/botones, probablemente lo da **Blocksy Companion Pro**. Con Fan Deco Studio quedan como selector desplegable estándar de WooCommerce hasta que construyamos swatches propios (pendiente; es importante para una tienda de lámparas por color).
2. **Páginas de Elementor:** siguen renderizando mientras Elementor esté activo (el tema las muestra a ancho completo y sin título), pero con tipografía y colores de Elementor. Header/footer hechos con el Theme Builder de PRO Elements **no** se usarán: el tema trae los suyos.
3. **Ajustes del Personalizador de Blocksy** (logo, colores, menús, widgets, CSS adicional) no se heredan. Logo y menús hay que reasignarlos; el CSS adicional hay que revisarlo.
4. **Code Snippets** que usen hooks `blocksy_*` dejarán de ejecutarse; los de WooCommerce siguen funcionando.
5. **Plantillas de WooCommerce sobrescritas** en el tema de respaldo (`blocksy-wpvibe-backup`) o en un hijo: revisar si existen (`/woocommerce/` dentro del tema).
6. **Carrito/Checkout:** WooCommerce 11 usa por defecto los **bloques** de Carrito/Checkout. Los estilos del tema cubren el checkout clásico a fondo y los bloques solo de forma básica. Verificar cuál usa el sitio y si *Payment Gateway Based Fees* y Andreani funcionan con ese tipo.
7. **Bloques en WordPress 7.x:** el editor usa ServerSideRender para la vista previa; confirmar que funciona en WP 7.1.4 al instalar.
8. Permalinks, productos, pedidos y clientes no cambian al cambiar de tema.

## 3. Orden de trabajo (siempre primero en staging)
1. Crear staging desde Hostinger y **copia de seguridad** completa.
2. **No mezclar cambios:** primero tema y plugin en staging con los plugins actuales tal cual; actualizar plugins (Mercado Pago, Andreani, Code Snippets…) como un paso separado, uno por vez.
3. Instalar `fan-deco-studio.zip` y `fan-deco-core.zip`; activar el tema; reasignar menús/logo; armar la portada con el patrón.
4. Correr el checklist de la sección 5.
5. Migrar páginas de Elementor una a una; recién cuando no quede ninguna, desactivar Elementor/PRO Elements/HT Slider/Blocksy Companion y observar.
6. SEO: seguir el procedimiento “Migración segura” del README (snapshot → importar → diff).
7. Producción solo con staging verde y ventana de bajo tráfico; Blocksy se deja instalado como respaldo para volver atrás.

## 4. SEO con AIOSEO 5.0.4
- El importador de Fan Deco Core asume tablas `aioseo_posts` / `aioseo_terms`. Con la versión 5.x **debe verificarse** que existan (la simulación avisa si no). Si el esquema difiere, se ajusta el importador con datos reales (pegar `DESCRIBE` de esas tablas).
- Revisar en AIOSEO: plantillas globales de título por tipo, ajustes de sitemap, redirecciones (si usan la función Pro), schema por tipo de contenido, y Local/Business info si existe. Todo lo que no esté en Fan Deco Core debe decidirse **antes** de desactivarlo.

## 5. Checklist de pruebas en staging
- [ ] Producto simple y variable: precio, variación, stock, agregar al carrito (AJAX y página).
- [ ] **Descuento por transferencia:** elegir transferencia en checkout → el descuento aparece; cambiar a tarjeta → desaparece (sin recargar a mano).
- [ ] **Mercado Pago:** pago de prueba (credenciales de prueba), retorno y estado del pedido.
- [ ] **Andreani:** cotización por código postal en carrito y checkout; costo y etiqueta.
- [ ] Cupones, impuestos, stock agotado, pedido como invitado y con cuenta.
- [ ] Mi cuenta: login, pedidos, direcciones.
- [ ] Emails de WooCommerce (los plantillas no cambian con el tema, pero comprobar logo).
- [ ] Móvil real (iPhone/Android): menú, carrito, checkout.
- [ ] Rich Results Test (producto, migas), Search Console, Lighthouse, GA4 sin duplicados (Tag Assistant).

## 6. Consultas de solo lectura para WPVibe (las 3 verificaciones + extras)
Copiá y pegá estas consultas en el asistente conectado por WPVibe, con la instrucción “solo lectura, no modifiques nada”:
1. “Listá **todas las páginas/entradas/productos hechos con Elementor** (meta `_elementor_edit_mode = builder`) con título, URL, estado y si son la portada, y las plantillas del Theme Builder (header, footer, single product, archive) con sus condiciones.”
2. “Listá los **Code Snippets activos**: título, ámbito (frontend/admin/todo) y **el código completo** de cada uno. Marcá los que toquen WooCommerce, precios, envíos, checkout o funciones/hooks de Blocksy.”
3. “Decime si el **Carrito y el Checkout** usan bloques o shortcodes (`[woocommerce_cart]`/`[woocommerce_checkout]`) y listá los métodos de pago activos con su configuración (sin credenciales).”
4. “Mostrame la configuración de **Payment Gateway Based Fees and Discounts** (reglas, % de descuento, métodos afectados, impuestos) y la de **Andreani** (zonas, métodos, valor declarado) sin claves ni tokens.”
5. “¿Qué **atributos de variación** usan los productos, y hay **swatches/visualización de variaciones** configurada con Blocksy Companion (por atributo)?”
6. “Listá los **ajustes del Personalizador de Blocksy** relevantes: logo (versión clara/oscura), menús asignados, widgets, CSS adicional y paleta.”
7. “Listá archivos en `wp-content/themes/blocksy-wpvibe-backup` y cualquier carpeta `woocommerce/` de plantillas sobrescritas en los temas.”
8. “De **All in One SEO**: plantillas de título por tipo de contenido, ajustes de sitemap, redirecciones, y `DESCRIBE` de las tablas `wp_aioseo_posts` y `wp_aioseo_terms` (nombres de columnas).”
9. “¿Qué plugins emiten **GA4 / píxel de Meta / etiquetas de Google** (MonsterInsights, Site Kit, PixelYourSite, Google for WooCommerce)? Listá los IDs configurados en cada uno para detectar duplicados.”
10. “Estructura de **permalinks**, redirecciones existentes y contenido de `robots.txt`.”

Pegá las respuestas acá y ajusto el tema/plugin con datos reales (swatches, compatibilidad de checkout, importador, migración de páginas).
