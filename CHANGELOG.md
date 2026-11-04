# Registro de cambios

Cada archivo que se entrega lleva su versión: `fan-deco-studio-X.Y.Z.zip` y `fan-deco-core-X.Y.Z.zip`.
La versión del tema también figura en Apariencia > Temas, en `style.css` y como comentario `<!-- Fan Deco Studio X.Y.Z -->` en el código fuente de cada página.

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
