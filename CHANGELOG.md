# Registro de cambios

Cada archivo que se entrega lleva su versión: `fan-deco-studio-X.Y.Z.zip` y `fan-deco-core-X.Y.Z.zip`.
La versión del tema también figura en Apariencia > Temas, en `style.css` y como comentario `<!-- Fan Deco Studio X.Y.Z -->` en el código fuente de cada página.

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
