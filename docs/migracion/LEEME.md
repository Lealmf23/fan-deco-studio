# Contenido para migrar de Elementor al tema Fan Deco Studio

Estos archivos usan **tus textos actuales** y los bloques del tema. Se pegan en una página nueva **en borrador**; las páginas originales no se tocan hasta que decidas reemplazarlas.

## Cómo usarlos
1. **Páginas > Añadir nueva**, título (por ejemplo "Portada nueva"). Guardá como **Borrador**.
2. Abrí el menú de tres puntos > **Editor de código** (Ctrl+Shift+Alt+M) y pegá el contenido del archivo.
3. Guardá y revisá con **Vista previa**.
4. Con el tema nuevo **activo** (o con el modo de solución de problemas) podés editar cada bloque con el panel derecho; el Editor de código es solo para pegar.

## Archivos
- `portada.html`: Home. **Pila de banners con el título principal a la izquierda** (en escritorio) (tus 4 imágenes: lámpara naranja 1152, calendario 1153, veladores 1150, mates de fútbol 1151), "Elegí tu próxima favorita", categorías, banner de mascotas y "Tu idea entra, sale realidad".
- `tu-mascota-en-3d.html`: página de mascotas con tus textos.

## Lo que hay que completar a mano
- **Fotos de las categorías:** Lámparas, Mascotas, Regalos y Decoración no tienen miniatura. Se cargan en **Productos > Categorías > Editar > Miniatura**. Las tarjetas de la portada las usan.
- **Banner de mascotas:** podés agregar `"imageId":NÚMERO` con la foto (el número está en la dirección de la imagen en Medios: `post=NÚMERO`).
- **Texto de los banners:** si el texto está dentro de la imagen, completá la "descripción" de cada banner (accesibilidad y buscadores). Hoy llevan solo el nombre del archivo.
- **"Algunas de nuestras mascotas":** la galería de fotos no está incluida; agregala con el bloque Galería de WordPress.
- **Franja de mensajes** ("DISEÑO ✦ COLOR ✦ PERSONALIDAD ✦ HECHO PARA VOS"): incluida en la portada con el bloque `fandeco/marquee`.
- **Títulos:** `**negrita**` y `*cursiva*` dentro del texto dan el estilo de dos tipografías (DM Sans + Georgia cursiva en oliva).

## Detalles de texto
- Corregí "WhatAppp" por "WhatsApp" en el botón de mascotas. El resto de los textos es el tuyo; "Señando con 50%" quedó tal cual.
- El precio "Desde $9.900" y el número de WhatsApp (5491136306519, tomado de tus páginas legales) son datos tuyos: verificá que sigan vigentes.

## Páginas que NO conviene migrar
- **Contacto (547)** y **Servicios (751)** tienen **texto de ejemplo del demo de Blocksy** (dirección "Dorchester Center, MA", correos `company.com`, lorem ipsum) y hoy están **publicadas**. La plantilla nueva de Contacto usa tus datos reales de Personalizar > Fan Deco > Contacto.
- **About Us (621)** es un borrador con el mismo texto de ejemplo.
