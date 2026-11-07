# Contexto de Fan Deco Studio (para pegar en otro chat)

## Qué es
Tema WordPress propio para **fandeco.com.ar** (WooCommerce, Hostinger, ARS, Palermo), llamado **Fan Deco Studio**, más el plugin **Fan Deco Core** (SEO propio para reemplazar a AIOSEO a futuro, descuento por transferencia, cuotas, interruptor de luz, ficha ampliada).
- Código: repositorio `lealmf23/fan-deco-studio`, rama `claude/fan-deco-studio-theme-pxx306` (hay un PR en borrador).
- Paquetes listos para subir: `release/fan-deco-studio.zip` y `release/fan-deco-core.zip` (se regeneran con `npm run package`).
- Stack: Vite + Tailwind v4, GSAP, bloques Gutenberg `fandeco/*` (hero, benefits, section-heading, category-grid, featured-products, editorial-banner, process-steps). Sin Blocksy como padre, sin Elementor, sin React. Checkout **clásico** de WooCommerce.

## Reglas que NO se rompen
- No modificar la tienda de producción sin permiso explícito: nada de pedidos, clientes, productos, configuración de WooCommerce, pagos, envíos ni descuentos.
- No eliminar ni desactivar Blocksy, Elementor ni AIOSEO (AIOSEO queda activo en la primera migración).
- No instalar plugins sin aprobación. No activar el tema (ni `fan-deco-core`) en producción hasta terminar las pruebas.
- No inventar productos, precios ni stock. No usar imágenes de demostración como productos reales.
- No afirmar que algo funciona si no se probó. Sin cambios destructivos. Sin claves ni contraseñas en el chat.
- Antes de cualquier cambio en el sitio real: respaldo.

## Datos del sitio (de las auditorías)
- WordPress 7.1.4, PHP 8.3, tema activo Blocksy 2.1.55, 21 plugins. Páginas: Home 838, Productos 68, Carrito 69, Checkout 70, Mi cuenta 71, Contacto 547, Tu mascota en 3D 1229.
- Atributos: `pa_base` y `pa_color` (color), `pa_tamano` (botones).
- Pagos: Mercado Pago Basic y transferencia bancaria. Envíos: Andreani. AIOSEO con 237 registros.
- 4 fragmentos de código (Code Snippets): `configurador_chapita`, `precio_con_cuotas`, `fandeco-product-gallery`, `Fan Deco - Product Schema WooCommerce`. 23 elementos de Elementor.

## Qué está hecho (todo probado solo con simulaciones y capturas de maquetas, no en WordPress real)
Home de escritorio y móvil, tienda, ficha de producto (cuotas, descuento por transferencia, interruptor de luz, swatches), carrito, checkout, pedido recibido, Mi cuenta y acceso, menú móvil a pantalla completa, buscador en vivo, 404, página de Contacto, header y footer de escritorio, estados vacíos. SEO propio con importador desde AIOSEO.

## Dónde estamos ahora
- El usuario NO tiene staging en Hostinger ni pudo restaurar la copia en LocalWP (Duplicator dio error).
- Probó la **Vista previa en vivo** del tema en el sitio real (sin activarlo). Dio un error fatal: `inc/customizer.php` pasaba una función a un parámetro `string` (defecto mío). **Ya corregido** (commit `78f8d09`); falta volver a subir el zip y repetir la vista previa.
- Se agregó que las páginas con bloques `fandeco/*` se muestren a ancho completo, y `docs/portada-prueba.html` con el contenido de la portada para pegar en el Editor de código de una página en **borrador**. Las fotos se asignan por ID de medios (`desktopImageId`, `mobileImageId`, `imageLitId` en el hero; `imageId` en el banner de mascotas; las categorías usan la miniatura de cada categoría).
- Alternativa limpia a la vista previa: plugin Health Check, modo de solución de problemas (solo la sesión del administrador).

## Pendiente
- Volver a subir el zip y ver la portada en una página en borrador.
- Del usuario: fotos reales (hero apagado/encendido, categorías), reglas de envío de Andreani, calificación de Google, condiciones de las plantillas de Elementor 1085/1104.
- Probar con WooCommerce real: pedido por cada medio de pago, Mercado Pago, Andreani, descuento por transferencia, variaciones, editor de bloques.
- Blog: todavía no existe.
