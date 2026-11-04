# Plan para desactivar Elementor y pasar al tema Fan Deco Studio

Basado en lo leído (solo lectura) del sitio real. **Nada de esto se ejecutó**: cada paso lo hace la persona dueña del sitio. Todo se puede deshacer reactivando Blocksy y Elementor.

## Antes de empezar
- [ ] Respaldo completo (Duplicator o copias de hPanel).
- [ ] Páginas nuevas listas en **borrador** (portada, mascotas, contacto) con los archivos de `docs/migracion/`.
- [ ] Miniaturas de las 4 categorías cargadas (Productos > Categorías).
- [ ] Decidido: **cuotas** en la ficha (activar `fan-deco-core` y apagar el fragmento `precio_con_cuotas`, o dejarlo como está) y qué pasa con el plugin **Checkout Fees** frente al descuento por transferencia.

## Configurador de chapitas: apagado temporal (decidido)
Hacerlo **a la vez** que se desactiva Elementor:
- [ ] Producto **1073 "Chapita personalizada"** ($4.000): pasar a **Sin stock** (recomendado) o a Borrador. Sin esto se podría comprar sin personalización.
- [ ] Página **"Elementor #1074"** (solo tiene `[configurador_chapita]`): pasar a **Borrador**.
- [ ] Fragmento **`configurador_chapita`**: desactivar (opcional; sin el shortcode no hace nada y los pedidos viejos conservan sus datos).
- La plantilla Elementor 1085 (categoría "Chapitas nombres") desaparece sola.

## El día del cambio
1. [ ] Desactivar juntos **Elementor**, **pro-elements** e **ht-slider-for-elementor** (desactivar, no borrar).
2. [ ] Activar el tema **Fan Deco Studio** (y, si se decidió, el plugin `fan-deco-core`).
3. [ ] **Logo:** Personalizar > Identidad del sitio (el tema nuevo arranca vacío, los ajustes son por tema).
4. [ ] **Menú:** asignar "Main Menu" a las posiciones del tema (principal y pie).
5. [ ] **Contacto, redes y footer:** Personalizar > Fan Deco.
6. [ ] **Portada:** Ajustes > Lectura > página de inicio = la portada nueva (la 838 queda de respaldo).
7. [ ] Páginas con texto de ejemplo de Blocksy: **Contacto (547)** reemplazar por la plantilla nueva y **Servicios (751)** y **About Us (621)** a borrador.

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
