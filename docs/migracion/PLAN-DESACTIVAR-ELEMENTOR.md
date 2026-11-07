# Plan para desactivar Elementor y pasar al tema Fan Deco Studio

Basado en lo leído (solo lectura) del sitio real. **Nada de esto se ejecutó**: cada paso lo hace la persona dueña del sitio. Todo se puede deshacer reactivando Blocksy y Elementor.

## Antes de empezar
- [ ] Respaldo completo (Duplicator o copias de hPanel).
- [ ] Páginas nuevas listas en **borrador** (portada, mascotas, contacto) con los archivos de `docs/migracion/`.
- [ ] Miniaturas de las 4 categorías cargadas (Productos > Categorías).
- [x] Decidido: **cuotas y descuento por transferencia con `fan-deco-core`** (se configuran en WooCommerce; ver abajo).
- [x] **Checkout Fees for WooCommerce** no tiene reglas guardadas (solo su número de versión), así que no aplica ningún descuento ni recargo hoy: no hay doble descuento. Se puede dejar instalado o desactivarlo.

## Configurador de chapitas: apagado temporal (decidido)
Hacerlo **a la vez** que se desactiva Elementor:
- [ ] Producto **1073 "Chapita personalizada"** ($4.000): pasar a **Sin stock** (recomendado) o a Borrador. Sin esto se podría comprar sin personalización.
- [ ] Página **"Elementor #1074"** (solo tiene `[configurador_chapita]`): pasar a **Borrador**.
- [ ] Fragmento **`configurador_chapita`**: desactivar (opcional; sin el shortcode no hace nada y los pedidos viejos conservan sus datos).
- La plantilla Elementor 1085 (categoría "Chapitas nombres") desaparece sola.

## Configurar `fan-deco-core` (cuotas y descuento por transferencia)
`fan-deco-core` viene con todo **apagado** y, mientras AIOSEO esté activo, **no toca el SEO** (se aparta solo). Después de activarlo:
1. [ ] **WooCommerce > Precio y cuotas:** tildar "Mostrar precio con cuotas", cargar la cantidad de cuotas sin interés (tu fragmento actual muestra 3 cuotas de Mercado Pago) y tildar "Listados / tarjetas de producto" si las querés en la tienda.
2. [ ] **Desactivar el fragmento `precio_con_cuotas`** en Fragmentos de código, para no mostrar las cuotas dos veces.
3. [ ] **WooCommerce > Descuento por transferencia:** tildar "Aplicar el descuento", poner el porcentaje (tu barra superior dice 10%), elegir si vale para todos los productos, algunas categorías o algunos productos. El descuento no incluye el envío.
4. [ ] Probar un pedido por **transferencia** (debe mostrar el descuento) y uno por **Mercado Pago** (no debe aplicarlo).
5. [ ] Tu barra superior anuncia "10% OFF pagando por transferencia": el porcentaje cargado tiene que coincidir.

## Dato a revisar de Transferencia bancaria
En WooCommerce > Ajustes > Pagos > Transferencia bancaria, la lista de **cuentas bancarias está vacía**. Si los datos para transferir (CBU, alias) solo están en el texto de la descripción, confirmá que se vean bien en el checkout y en la página de pedido recibido.

## El día del cambio
1. [ ] Desactivar juntos **Elementor**, **pro-elements** e **ht-slider-for-elementor** (desactivar, no borrar).
2. [ ] Activar el tema **Fan Deco Studio** (y, si se decidió, el plugin `fan-deco-core`).
3. [ ] **Logo:** Personalizar > Identidad del sitio (el tema nuevo arranca vacío, los ajustes son por tema).
4. [ ] **Menú:** asignar "Main Menu" a las posiciones del tema (principal y pie).
5. [ ] **Contacto, redes y footer:** Personalizar > Fan Deco.
6. [ ] **Portada:** Ajustes > Lectura > página de inicio = la portada nueva (la 838 queda de respaldo).
7. [ ] Páginas con texto de ejemplo de Blocksy: **Contacto (547)** reemplazar por la plantilla nueva y **Servicios (751)** y **About Us (621)** a borrador.

## Inventario completo de lo que tiene datos de Elementor (leído del sitio, solo lectura)
La etiqueta "— Elementor" en la lista de páginas aparece en toda página que se guardó con el editor de Elementor. Al desactivarlo, esas páginas **siguen existiendo y muestran la copia de texto que Elementor guarda en el contenido**, pero pierden su diseño.

**Páginas publicadas**
| ID | Página | Qué pasa sin Elementor |
|---|---|---|
| 838 | Home | Reemplazar por la home nueva (la de respaldo queda). |
| 70 | **Finalizar compra** | Tiene datos de Elementor muy chicos (333 caracteres). **Verificar antes** que el contenido sea el shortcode `[woocommerce_checkout]` y que el checkout funcione sin Elementor. |
| 547 | Contacto | Reemplazar por la plantilla nueva (tiene texto de ejemplo). |
| 751 | Servicios | Texto de ejemplo: pasar a borrador. |
| 1229 | Tu mascota en 3D | Reemplazar por `tu-mascota-en-3d.html`. |
| 1317, 1322, 1331, 1336, 1340, 1352 | Términos, Reembolsos, Privacidad, Envíos, Cancelación, Aviso legal | Muestran su texto sin el diseño de Elementor; revisar a ojo. |
| 1074 | Configurador de chapitas ("Elementor #1074") | Apagado temporal ya decidido (a borrador). |

**Borradores:** 621 About Us, "Elementor #10".

**Plantillas de Elementor (`elementor_library`)**: 983 template1, 1012 slide template, 1085 Chapitas Nombres, 1104 Single Product, 1134 Título Editorial, 1497 "En breve estaremos online..." (revisar que el modo mantenimiento de Elementor esté apagado), 9 Kit por defecto. Desaparecen solas sin Elementor.

**HT Slider (`htslider_slider`)**: 981, 1015, 1025, 1027 (Slide 1 a 4). Ver dónde se usan antes de desactivar el plugin.

## Verificación
- [ ] Portada, tienda, ficha de producto, carrito, checkout y Mi cuenta (escritorio y móvil).
- [ ] Páginas legales (se ven con el contenedor del tema).
- [ ] **Un pedido real chico** por cada medio de pago (transferencia y Mercado Pago) y el cálculo de Andreani.
- [ ] Descuento por transferencia sin duplicarse.
- [ ] Si algo falla: reactivar Blocksy y Elementor.

## Semanas después
- [ ] Desactivar **Blocksy Companion Pro** y luego **Blocksy**.
- [ ] Recién entonces evaluar borrar plugins y datos de Elementor.

## Hallazgos que conviene resolver
- Política de privacidad (1331): empieza en la sección 6 (faltan las secciones 1 a 5).
- Contacto (547) y Servicios (751) tienen texto de ejemplo publicado (dirección y correos falsos).
- El CSS adicional del Personalizador de Blocksy (estilos de títulos y banners) se pierde al desactivar Blocksy; el tema nuevo ya trae su propio estilo.
