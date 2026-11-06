# Registro de cambios

Cada archivo que se entrega lleva su versión: `fan-deco-studio-X.Y.Z.zip` y `fan-deco-core-X.Y.Z.zip`.
La versión del tema también figura en Apariencia > Temas, en `style.css` y como comentario `<!-- Fan Deco Studio X.Y.Z -->` en el código fuente de cada página.

## Tema 0.4.2 / Blocks 0.4.2 / Core 0.4.2
### Cambiado
- **Espacios blancos entre bloques**: las secciones que van directo en el contenido de la página ya no heredan el margen vertical del tema (Blocksy los separaba con una franja blanca). El margen que definas en el bloque sigue valiendo. El espacio blanco de **arriba del primer bloque** lo pone Blocksy en las opciones de la página ("Espaciado vertical del área de contenido": elegir "sin espaciado").
- **Franja de mensajes**: ya **no se detiene al pasar el mouse**. Solo se pausa cuando no está a la vista.
### Agregado
- **Franja de mensajes**: ajustes de **alto** (espacio de arriba y de abajo de las letras, px) y **tamaño de las letras** (px). El color de fondo ya se cambia en "Color > Fondo".

## Tema 0.4.1 / Blocks 0.4.1 / Core 0.4.1
### Agregado
- **Color de fondo en todos los bloques**: cada sección tiene ahora el panel "Color > Fondo" del editor (se usa el de WordPress, con paleta y selector libre).
- **Banner principal (pila de banners)**: espacio de arriba y de abajo ajustable (panel "Espacios", en px).
- **Encabezado de sección**: tamaño del título y de la descripción (px; en celulares el título se reduce solo) y espacio de arriba y de abajo (panel "Tamaños y espacios").
### Cambiado
- **Banner**: se quitaron las rayas de las fotos de atrás; ahora solo se ven opacas.
- **Tarjetas**: el botón usa un icono SVG propio de carrito (antes una máscara CSS que en tu sitio no se veía) y su color queda fijo; los puntos de color vuelven a estar siempre a la vista (antes solo al pasar el mouse).
### Sin verificar en el sitio real
- El color de fondo usa el soporte nativo de WordPress; probado solo en las pruebas automáticas y maqueta, no en el editor real.

## Tema 0.4.0 / Blocks 0.4.0 / Core 0.4.0
### Agregado
- **Bloque "Fan Deco: Carrusel de fotos"** (`fandeco/photo-carousel`): carrusel de solo fotos con flechas y deslizamiento táctil. Cada foto puede tener texto (aparece con fundido al pasar el mouse; siempre visible en pantallas táctiles) y enlace. Ajustes: fotos visibles en escritorio (2 a 5), proporción (3:4, 4:5, cuadrada, 16:9), flechas, textos y el efecto de zoom suave con leve elevación.
- **Bloque "Productos destacados"**: opción de proporción de la foto (4:5, 3:4, cuadrada).
### Cambiado
- **Efecto de las tarjetas al pasar el mouse** (inspirado en cómo se comporta una referencia que mostraste, con código propio y el estilo de Fan Deco): la tarjeta se eleva 4 px, la foto hace un zoom lento (1,2 s), la segunda foto aparece con fundido y los puntos de color se muestran recién al pasar el mouse (en pantallas táctiles quedan siempre visibles).
- **Rendimiento**: la segunda foto de cada tarjeta ya no se descarga hasta que el mouse pasa por encima; en celulares nunca se descarga.
### Sin verificar en el sitio real
- Probado con maqueta. Se respetan `prefers-reduced-motion` y el foco por teclado.

## Tema 0.3.13 / Blocks 0.3.13 / Core 0.3.13
### Cambiado
- Editor: la vista previa de los bloques ahora se pide al servidor **de a 2 por vez**, con **2 reintentos** y recordando lo ya dibujado. Antes, una página con 10 o más bloques disparaba todas las peticiones a la vez y en hostings compartidos varias fallaban ("No se pudo mostrar la vista previa") o quedaban cargando. Causa probable, no confirmada: sobrecarga por peticiones simultáneas (el servidor dibuja cada bloque bien por separado).

## Tema 0.3.12 / Blocks 0.3.12 / Core 0.3.12
### Cambiado
- Editor: si la vista previa de un bloque falla, ya no muestra "Error al cargar el bloque: [object Object]". Muestra un aviso de que el bloque se puede editar igual y el **detalle real del error** (código y mensaje), para poder diagnosticarlo.
### Diagnóstico
- Leyendo el sitio (solo lectura): el servidor dibuja bien los bloques por la API del editor (probé la franja de mensajes y productos destacados) y `debug.log` no tiene errores recientes; el fallo es del lado del navegador/la petición. Causa aún sin confirmar.

## Tema 0.3.11 / Blocks 0.3.11 / Core 0.3.11 (rendimiento en móvil)
### Cambiado
- En celulares y pantallas táctiles se quitan los efectos más pesados de dibujar: la mezcla de luz de las fotos de las tarjetas (`mix-blend-mode`), el desenfoque de fondo (`backdrop-filter`) de las pastillas y botones, y el filtro de saturación de la pila de banners (ahora las fotos de atrás se atenúan con opacidad).
- El autoplay de la pila de banners solo corre mientras el banner se ve en pantalla; la franja de mensajes se pausa cuando está fuera de pantalla.
### Sin verificar
- No pude medir el scroll en un celular real: son las causas más probables según el código, no un diagnóstico confirmado. Si sigue trabándose, hace falta saber en qué parte de la página pasa.

## Tema 0.3.10 / Blocks 0.3.10 / Core 0.3.10
### Agregado
- **Fan Deco Blocks: productos relacionados en la ficha** con el estilo de las tarjetas de Fan Deco (carrusel, puntos de color, foto al pasar el mouse, precio con transferencia, botón con carrito). Reemplaza la sección de relacionados del tema (deja vacía la lista de WooCommerce y dibuja la propia después del resumen del producto). Título "Productos relacionados" y hasta 8 productos.
- Ajustes por código: `fandeco_blocks_related` (apagarlo), `fandeco_blocks_related_count`, `fandeco_blocks_related_title`.
### Sin verificar en el sitio real
- Que Blocksy dispare `woocommerce_after_single_product_summary` en tu ficha y que el carrusel quede a buen ancho dentro de su contenedor; si en Blocksy está apagado el interruptor "Relacionados y ventas dirigidas", igual aparece el nuestro (se apaga con el filtro).

## Tema 0.3.9 / Blocks 0.3.9 / Core 0.3.9
### Corregido
- Icono del carrito: el dibujo quedaba ~1,5 px corrido a la izquierda dentro del círculo (la caja estaba centrada, pero el carrito tiene el manubrio de un solo lado). Se compensó en el dibujo; ahora queda centrado a la vista. También en las tarjetas de Blocksy.

## Tema 0.3.8 / Blocks 0.3.8 / Core 0.3.8
### Cambiado
- Tarjetas: el cambio a la foto de una variante (puntos de color) ahora es un **fundido suave** de 0,45 s sobre la foto principal, con precarga para que no parpadee; vuelve con fundido al salir. El cambio de foto al pasar el mouse ya era con fundido.
- Botón del carrito: más chico (2,6 rem), de vidrio claro con aro fino, icono bien centrado y se llena de oliva al pasar el mouse.
- Los puntos de color tienen su pastilla oscura translúcida (antes quedaban sueltos sobre la foto).

## Tema 0.3.7 / Blocks 0.3.7 / Core 0.3.7
### Cambiado
- El botón de las tarjetas (que lleva a la ficha) ahora usa el icono de **carrito de compras** en lugar de la flecha; también en las tarjetas de Blocksy con opciones.
- Móvil: el precio con transferencia ya no se parte en dos líneas.

## Tema 0.3.6 / Blocks 0.3.6 / Core 0.3.6 (tarjetas del bloque "Productos destacados")
### Cambiado
- **Puntos de color**: solo aparecen si el producto tiene variantes de color, uno por variante (hasta 8), arriba a la derecha. Al pasar el mouse (o tocar) se ve la foto de esa variante, si la variación tiene foto propia. Se quitaron los puntos de "foto 1 / foto 2".
- **Colores**: se toman del atributo de tipo "color" (Blocksy Variation Swatches). Como la clave exacta con que Blocksy guarda el color no está verificada, se revisan los metas del término cuyo nombre sugiere Blocksy, color o swatch y se toma el primer color hexadecimal; si no hay, se usa el campo "Color del selector" propio o el nombre del color. Sin verificar con tus datos reales.
- **Botón**: ahora es un círculo con flecha que **lleva a la ficha del producto**; ya no agrega al carrito.
- **Precios**: se ve el precio de lista y, si querés, también el precio con transferencia (nunca cuotas). Requiere el descuento por transferencia activo en Fan Deco Core.
- **Alineación**: todas las tarjetas de una fila quedan a la misma altura, con la foto alineada arriba y los precios alineados abajo (se quitó el escalonado que bajaba una de cada cuatro).
- **Título del producto**: ya no hereda el tamaño enorme de los títulos de Blocksy.
### Agregado
- En el bloque, panel **Tarjetas**: botón "Ver producto", puntos de color, cambio de foto al pasar el mouse, precio con transferencia, **letra del título** (la del sitio, DM Sans o Georgia cursiva), **tamaño del título** y **tamaño del precio** (px).
- Core: `fandeco_transfer_price_html()` (solo el precio con transferencia).
### Sin verificar en el sitio real
- Todo probado con maqueta (escritorio y móvil), no con tus productos.

## Tema 0.3.5 / Blocks 0.3.5 / Core 0.3.5
### Corregido
- Core con Blocksy: las cuotas y el precio con transferencia se agregan ahora con el filtro `blocksy:woocommerce:product-card:price`. Antes usaban el gancho `...price:after`, que Blocksy no dispara cuando la tarjeta es de **tipo 2** ("Añadir al carrito y precio" en una sola pieza, como en este sitio). Verificado leyendo el código de Blocksy; sin probar en el sitio real.

## Tema 0.3.4 / Blocks 0.3.4 / Core 0.3.4
### Cambiado
- Tarjetas de Blocksy: el botón "Seleccionar opciones" / "Añadir al carrito" pasa a ser un círculo oliva con icono (flecha en productos con opciones, "+" en los simples, tilde al agregar) abajo a la derecha de la tarjeta, junto a los precios. El texto sigue disponible para lectores de pantalla. Probado con maqueta.

## Tema 0.3.3 / Fan Deco Blocks 0.3.3 / Fan Deco Core 0.3.3 (tarjetas de producto con segunda foto)
Las versiones de los tres paquetes ahora suben juntas.
### Agregado
- **Tarjeta de Fan Deco (bloque "Productos destacados" y tema)**: al pasar el mouse cambia a una segunda foto, con una pastilla de puntos arriba a la derecha (el segundo punto es ámbar si es la foto "encendida"); en pantallas táctiles se tocan los puntos. La segunda foto es la "encendida" cargada en Fan Deco Core o, si no hay, la primera de la galería.
- **Core con Blocksy, tarjetas del listado y de relacionados** (`assets/loop-cards.css`, solo CSS): foto en ventana de esquinas suaves, título a la izquierda en la tipografía del sitio, precios alineados y botón discreto. Apagable con el filtro `fandeco_core_loop_cards_skin`.
- **Core con Blocksy**: la foto encendida pasa a ser la segunda imagen de la tarjeta, y el precio con transferencia / cuotas ahora se muestran también en sus tarjetas (usa el gancho propio de Blocksy `blocksy:woocommerce:product-card:price:after`, verificado en su código).
- Precio en tarjetas: el precio de lista queda chico arriba y, debajo, el precio con transferencia grande ("pagando con transferencia"). En móvil se ocultan las cuotas para que no se amontone.
- `core.css` ahora también se carga en tienda, relacionados y portada (antes solo ficha y checkout).
### Importante
- El cambio de foto al pasar el mouse en las tarjetas de **Blocksy** lo hace Blocksy: hay que activarlo en Personalizar > WooCommerce > Tarjeta de producto (efecto de la imagen al pasar el mouse: "cambiar imagen"; el nombre exacto del menú no está verificado). Core solo le entrega la foto encendida como segunda imagen.
### Sin verificar en el sitio real
- Los estilos de las tarjetas de Blocksy se escribieron a partir de su código PHP (no de su HTML final) y se probaron con una maqueta; pueden necesitar un ajuste al verlos reales.

## Fan Deco Core 0.3.2 (precio más claro en la ficha)
### Cambiado
- Cuotas y transferencia ya no van en una tarjeta con iconos: ahora el precio con transferencia es grande, con el descuento al lado y "pagando con transferencia" debajo; las cuotas quedan en una línea con un icono chico de tarjeta.
- Si hay precio con transferencia, el precio normal pasa a ser la referencia chica de arriba ("precio de lista"). Usa `:has()` (navegadores actuales); si no aplica, el precio queda como estaba.
- El texto "con transferencia" pasa a "pagando con transferencia" (y el descuento es un elemento aparte, `.fd-pay-badge`, sin fondo).
### Sin verificar en el sitio real
- Probado con una maqueta, no con el tema Blocksy: falta ver que el precio de Blocksy quede justo antes del bloque (si hay algo en el medio, el precio de lista no se achica).

## Fan Deco Core 0.3.1 (cotizador de Andreani integrado)
### Agregado
- `assets/andreani-skin.css` (solo CSS, no toca el HTML ni el JS de Andreani): el cotizador de envío de la ficha deja de verse como un widget rojo aparte. Cerrado es una fila más ("Calcular envío a tu código postal"); al tocarla se abre en el lugar, sin rebote ni tarjeta flotante, con el campo, el botón y los resultados en la paleta del sitio.
- Se carga solo en la ficha y solo si el plugin de Andreani está activo. Para apagarlo: `add_filter( 'fandeco_core_andreani_skin', '__return_false' );`. El modo "flotante" de Andreani solo se recolorea.
### Sin verificar en el sitio real
- Probado con una maqueta del HTML y CSS de Andreani (leídos del sitio, solo lectura) en escritorio y móvil; falta verla con el plugin real, también con la posición y el tema elegidos en sus ajustes.

## Fan Deco Core 0.3.0 (adaptado a Blocksy)
Verificado leyendo el código real de Blocksy instalado en el sitio (solo lectura).
### Agregado
- `assets/core.css` (8 kB, generado desde el CSS del tema): estilos de lo que el plugin agrega en la ficha y el checkout (cuotas, precio con transferencia, garantías, tarjetas, características y tabla de especificaciones). Sin esto, con Blocksy esas piezas salían sin diseño. Se carga solo en ficha y checkout, y no con el tema Fan Deco Studio.
- Foto encendida: con la galería de Blocksy, la foto va dentro de la imagen principal (antes, al final de toda la galería, tapando las miniaturas); el interruptor se ubica arriba y respeta las miniaturas a la izquierda.
### Confirmado sin cambios
- Las piezas de la ficha caen donde se diseñaron: Blocksy reubica lo que se engancha en `woocommerce_single_product_summary` según la prioridad (11 después del precio, 25 después de la descripción corta, 35 y 36 después del botón de compra).
- Cuotas y foto encendida usan eventos estándar de WooCommerce (`found_variation`, `reset_data`), que Blocksy conserva. El descuento por transferencia es independiente del tema.
### Sin verificar en el sitio real
- Aspecto de la foto encendida y del interruptor sobre la galería de Blocksy, y cuotas dentro de las tarjetas del listado.

## Fan Deco Blocks 0.1.0 (plugin nuevo) / tema 0.3.2
### Agregado
- **Plugin `fan-deco-blocks`**: los 9 bloques de diseño (pila de banners, franja de mensajes, hero con interruptor de luz, categorías, productos destacados, banner editorial, pasos, beneficios y encabezado de sección) como plugin, para usarlos sobre **Blocksy** (o cualquier tema) sin depender del tema Fan Deco Studio.
  - Estilos y scripts propios (41 kB de CSS), con DM Sans incluida; solo se cargan en las páginas que usan un bloque y no tocan el header, el footer ni el body del otro tema.
  - Las secciones se abren a todo el ancho aunque el tema las ponga en una columna angosta.
  - Tarjeta de producto propia, sin vista rápida (ya la ofrece Blocksy Pro) y patrón "Fan Deco: Portada completa".
  - Con el tema Fan Deco Studio activo, el plugin se aparta solo.
  - Se genera desde el mismo código del tema (`npm run build:plugin`): funciones, CSS y bloques salen de una única fuente.
- Guía `docs/migracion/PORTADA-CON-BLOCKSY.md`.
- Tema: la tarjeta de producto admite ocultar la vista rápida.

## 0.3.1
### Corregido
- Galería de la ficha de producto: las columnas ya no pueden ensancharse ni correrse por el contenido de la galería de WooCommerce (FlexSlider, zoom, miniaturas); se reforzó con `minmax(0, …)` y reglas de contención.

## 0.3.0
### Agregado
- **Personalización completa** (Apariencia > Personalizar > Fan Deco Studio): 51 ajustes en 5 secciones nuevas con vista previa en vivo y botón de restablecer en cada uno. Ver `docs/PERSONALIZAR.md`.
  - **Colores:** fondo, superficies, texto, títulos y secciones oscuras, líneas, acento (y su hover y versión clara), color de la palabra en cursiva, y colores propios de botones, header, barra superior y footer.
  - **Tipografía:** fuente del texto, de los títulos y de la palabra destacada (la incluida o fuentes del sistema), estilo y peso de la cursiva, tamaño base, interlineado, tamaño de títulos para escritorio y móvil, pesos, interlineado y espaciado de letras de títulos, sobretítulos.
  - **Diseño y espaciado:** ancho del contenido, margen lateral, espacio entre secciones, esquinas de tarjetas y fotos, forma y alto de botones.
  - **Header, menú y logo:** alto del logo (escritorio, móvil y footer), alto del header, tamaño, separación y peso del menú.
  - **Tarjetas de producto:** proporción de la foto y mostrar u ocultar vista rápida, etiquetas y puntos de color.
- Solo se imprime en la página lo que se cambió; sin cambios, el sitio se ve idéntico.
- Prueba de paridad que verifica que el CSS de PHP y el de la vista previa en vivo sean idénticos.

### Corregido
- Menú del header y del menú móvil mostraban solo "Inicio" y "Tienda": ahora usan los menús existentes si no se asignaron (ver 0.2.0).

## 0.2.0
Primera versión numerada de las entregas. Reúne todo lo construido desde 0.1.0.

### Agregado
- Identidad del sitio: paleta oliva/carbón/crema, DM Sans + Georgia cursiva y títulos con **negrita** y *cursiva*.
- Bloques nuevos: Pila de banners (texto a la izquierda en escritorio) y Franja de mensajes. Encabezado de sección admite H1.
- Vista rápida, panel "Agregado al carrito", compartir y barra de compra flotante en escritorio.
- Buscador en vivo; en móvil, ícono en el header que despliega el panel; carrito en el header móvil.
- Menú móvil a pantalla completa, 404, página de Contacto, estados vacíos, header y footer de escritorio con franja de invitación.
- Si no hay menús asignados a las posiciones del tema, usa un menú existente (principal, pie y legales) en vez del menú de reserva; al activar el tema los deja asignados.
- Versionado: nombre del zip con versión, `npm run bump`, este registro y la marca de versión en el HTML.

### Corregido
- Logo enorme en tienda y fichas (WooCommerce pisaba su altura).
- Bloques nuevos sin archivo del editor (no aparecían en el insertor).
- Error fatal del Personalizador (validador como función en parámetro `string`).
- Buscador en vivo que nunca se activaba; menú de reserva que ignoraba la clase móvil.
