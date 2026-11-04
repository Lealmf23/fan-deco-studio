# Migración de Fan Deco: de Blocksy + Elementor a Fan Deco Studio

Base: auditoría de solo lectura del 4/11/2026 (WordPress 7.1.4, PHP 8.3.36, tema Blocksy 2.1.55, 21 plugins). Los datos del inventario provienen de esa auditoría; **no pude consultar el sitio desde este entorno**.

## 0. Datos confirmados en el sitio (consulta posterior a la auditoría)
- WooCommerce 11.2.1, Blocksy 2.1.55, PHP 8.3.36.
- Páginas existentes (**no se tocan ni se renombran; sus URLs se conservan**): Home **838**, Productos **68**, Carrito **69**, Checkout **70**, Mi cuenta **71**, Contacto **547**, Tu mascota en 3D **1229**.
- Atributos globales de WooCommerce: `pa_base` (tipo *color*), `pa_color` (tipo *color*), `pa_tamano` (tipo *button*).

### Requisitos acordados y cómo se cumplen
| # | Requisito | Estado |
|---|---|---|
| 1 | Conservar páginas, productos, pedidos y URLs | El tema no escribe en la base de datos de contenido ni cambia permalinks. La Home actual (838) sigue mostrando su contenido de Elementor hasta que se decida cambiarla (ver abajo) |
| 2 | Selectores visuales de color y tamaño compatibles con variaciones | Implementado (`inc/swatches.php`, `src/js/swatches.js`). **Probado con una simulación**, falta probarlo con WooCommerce real |
| 3 | No depender de Blocksy para variaciones | El tipo del atributo (*color*/*button*) queda guardado en WooCommerce y el tema lo interpreta solo. Los colores salen de un campo propio (`fandeco_swatch_color`), de datos heredados de Blocksy si existen, o del nombre |
| 4 | Mantener AIOSEO en la primera migración | Fan Deco Core **no imprime nada** mientras AIOSEO esté activo; el tema también cede. No se importa ni desactiva nada |
| 5 | No modificar pagos, descuentos ni envíos | Nada en el tema/plugin toca precios, cargos, pasarelas ni envíos; los selectores solo cambian el valor del `<select>` y dejan que WooCommerce calcule |
| 6 | Snippets, plantillas Elementor y configuración comercial = **pendientes de validación** | Ver sección “Pendientes de validación” |
| 7 | No activar en producción hasta completar staging | Ver sección 3; el ZIP solo debe instalarse en staging primero |

### Cómo se cambia la Home sin riesgo (página 838)
No se modifica la página 838. En staging: crear una página nueva “Inicio (nuevo)”, insertar el patrón **Fan Deco: Portada completa**, previsualizar y probar; recién cuando staging esté aprobado, en Ajustes → Lectura apuntar la portada a la nueva página (la 838 queda intacta como respaldo y se puede volver atrás en un clic).

### Colores de los selectores (sobre `pa_color` y `pa_base`)
- Se resuelven en este orden: campo “Color del selector” del término (Productos → Atributos → *Configurar términos*) → color que haya dejado Blocksy (búsqueda tolerante en el meta `blocksy_taxonomy_meta_options`; **estructura no verificada**) → nombre conocido (negro, blanco, rojo, bordó, etc.) → si nada aplica, botón con el nombre (siempre usable).
- Después de instalar en staging: revisar cada término de `pa_color`/`pa_base`; los que no muestren el color correcto, fijarlos con el campo propio. Para saber qué guardó Blocksy, pedir por WPVibe el `get_term_meta` de esos términos.

### Pendientes de validación (no asumir que funcionan)
- Code Snippets activos (lógica de WooCommerce o hooks de Blocksy).
- Plantillas y páginas de Elementor (incluida **Tu mascota en 3D, 1229**: puede usar widgets/JS propios).
- Checkout, Mercado Pago, Andreani y descuentos por método de pago.

## 0b. Auditoría profunda (hallazgos y qué se hizo con cada uno)
**Checkout clásico confirmado:** Carrito (69) = `[woocommerce_cart]`, Checkout (70) = `[woocommerce_checkout]`, enlaces `/%postname%/`. **Decisión: mantener el checkout clásico en la primera versión** (no migrar a Bloques a la vez que se cambia de tema): Mercado Pago, Andreani y el plugin de descuentos dependen de él. El tema estiliza el flujo clásico sin tocar su estructura ni sus hooks. Nota: el Checkout (70) figura como página de Elementor; el tema **siempre** usa su contenedor normal en carrito/checkout/Mi cuenta (no la rama de ancho completo de Elementor). En staging conviene dejar esa página solo con el shortcode.

**Funciones propias que NO deben perderse (Code Snippets activos):**
| Fragmento | Qué es | Decisión | Compatibilidad con el nuevo tema |
|---|---|---|---|
| `configurador_chapita` (~32 KB) | Configurador por shortcode | **Conservar tal cual** | El tema ya **no usa el reinicio global de Tailwind** (Preflight) para no pisar su marcado/estilos; el estilo se acota a los componentes del tema. Probar en staging (JS, estilos, carrito) |
| `precio_con_cuotas` (~16 KB; 3 pagos, 10 % de referencia) | Cuotas + descuento por transferencia en precio | **Conservar** | Las tarjetas y la ficha de producto usan `get_price_html()` y los hooks estándar de WooCommerce, así que el fragmento debería seguir actuando; verificar posición y estilos. La regla real del descuento **no está confirmada** (es solo un valor de referencia) |
| `fandeco-product-gallery` | Comportamiento propio de galería | **Revisar para integrar** | El tema deja el marcado estándar de galería de WooCommerce (`.woocommerce-product-gallery`); si el fragmento depende de clases de Blocksy/Elementor, habrá que adaptarlo |
| `Fan Deco - Product Schema WooCommerce` | JSON-LD de productos | **Auditar duplicados** | WooCommerce ya emite schema de Producto; AIOSEO también puede. Usar `seo:snapshot`/`seo:diff` (detectan `Product x2`) antes de decidir cuál queda |

**Elementor (23 elementos: 13 páginas + 10 de biblioteca):** Home 838, Contacto 547, Servicios 751, Tu mascota en 3D 1229, Checkout 70, plantilla de producto «Chapitas Nombres» 1085, plantilla de producto general 1104 y sección «FanDeco - Título Editorial» 1134 (más borradores). **Elementor no se puede desactivar al instalar el tema.** Mientras esté activo, sus **plantillas de producto (1085, 1104) pueden seguir reemplazando la ficha de producto** según sus condiciones de visualización, y entonces los selectores y la ficha del tema no se verían en esos productos. Plan: (1) leer las condiciones de 1085/1104 (WPVibe), (2) en staging pasarlas a borrador una por vez y comprobar la ficha del tema, (3) migrar contenido de Home/Contacto/Servicios/1229 a bloques/páginas nuevas, (4) recién entonces retirar Elementor.

**SEO:** hay 237 registros en `wp_aioseo_posts` pero **no existe `wp_aioseo_terms`**: **no ejecutar el importador todavía**. Se endureció: nunca aplica sin una simulación previa (válida 1 h), no importa categorías si la tabla falta, y la pantalla lo advierte. Mientras tanto AIOSEO sigue activo y Fan Deco Core no imprime nada. Pendiente: averiguar dónde guarda AIOSEO 5.0.4 los datos de categorías/etiquetas (pegar `SHOW TABLES LIKE '%aioseo%'` y un `DESCRIBE`).

**Pagos y envíos (parcial):** habilitados Transferencia bancaria (`bacs`) y Mercado Pago Basic; existen ajustes guardados de otros métodos de Mercado Pago y de Andreani sin confirmar si están habilitados. Pendiente retomar cuando el servidor permita consultas (hubo un HTTP 429: **espaciar las consultas** de auditoría).

**Analítica:** hay configuración de Site Kit, MonsterInsights y PixelYourSite; sin confirmar duplicados. No desactivar ninguno sin comparar. `npm run seo:snapshot` ahora registra qué etiquetas se cargan (GA4 `gtag`, GTM, Meta Pixel, Pinterest) y avisa si una se carga más de una vez en una misma página.

### Prioridades
| Prioridad | Acción | Estado |
|---|---|---|
| Alta | Preservar configurador de chapitas y cuotas | Compatibilidad preparada (sin Preflight); **pendiente probar en staging** |
| Alta | Mantener checkout clásico; probar Mercado Pago y Andreani | Checkout clásico estilizado; **pendiente probar pagos/envíos** |
| Alta | No ejecutar la migración de AIOSEO todavía | Importador bloqueado sin simulación; AIOSEO sigue activo |
| Media | Migrar plantillas de producto de Elementor | Pendiente (necesita sus condiciones) |
| Media | Integrar galería personalizada y selectores de variaciones | Selectores hechos; galería pendiente de ver el código del fragmento |
| Media | Revisar etiquetas duplicadas de Analytics | Herramienta lista; falta correrla en staging |

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
1. **Variaciones con swatches (colores/tamaños):** hoy los aporta Blocksy Companion Pro. Fan Deco Studio trae los suyos (ver sección 0); falta confirmar en staging que los colores de `pa_color`/`pa_base` se vean bien.
2. **Páginas de Elementor:** siguen renderizando mientras Elementor esté activo (el tema las muestra a ancho completo y sin título), pero con tipografía y colores de Elementor. Header/footer hechos con el Theme Builder de PRO Elements **no** se usarán: el tema trae los suyos.
3. **Ajustes del Personalizador de Blocksy** (logo, colores, menús, widgets, CSS adicional) no se heredan. Logo y menús hay que reasignarlos; el CSS adicional hay que revisarlo.
4. **Code Snippets** que usen hooks `blocksy_*` dejarán de ejecutarse; los de WooCommerce siguen funcionando.
5. **Plantillas de WooCommerce sobrescritas** en el tema de respaldo (`blocksy-wpvibe-backup`) o en un hijo: revisar si existen (`/woocommerce/` dentro del tema).
6. **Carrito/Checkout:** el sitio usa el **checkout clásico** (confirmado). Los estilos del tema cubren ese flujo a fondo; los bloques de WooCommerce solo de forma básica. No migrar a bloques en este hito.
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
- [ ] **Swatches:** elegir color y tamaño; el precio/stock/imagen se actualizan; combinaciones inexistentes quedan tachadas; “Borrar” funciona; los colores de cada término son correctos; probar con teclado y en móvil.
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
