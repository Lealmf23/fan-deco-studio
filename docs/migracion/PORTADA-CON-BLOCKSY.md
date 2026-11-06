# Portada nueva con Blocksy (sin Elementor)

Los bloques de Fan Deco viven en el plugin **Fan Deco Blocks** y funcionan sobre Blocksy. Instalarlo **no cambia nada** de tu sitio: sus estilos solo se cargan en las páginas que usan un bloque.

## 1. Instalar el plugin
1. **Plugins > Añadir nuevo > Subir plugin**: `fan-deco-blocks-X.Y.Z.zip` (en `release/`). Activalo.
2. Si algún día activás el tema Fan Deco Studio, el plugin se aparta solo (el tema ya trae los bloques).

## 2. Crear la página en borrador
1. **Páginas > Añadir nueva** (o reutilizá la página "inicio", ID 1663). Dejala en **Borrador**.
2. Menú de tres puntos > **Editor de código** y pegá `docs/migracion/portada.html`, o en el editor visual: **+ > Patrones > Fan Deco: Portada completa**.
3. Volvé al editor visual y tocá cada bloque para cambiar textos y fotos en el panel derecho.

## 3. Ajustes de Blocksy para esa página
En el panel derecho del editor, en los ajustes propios de Blocksy para la página (los nombres pueden variar según tu versión):
- **Título de la página:** desactivado (la portada ya tiene su H1 dentro del banner; si no, habría dos).
- **Estructura / ancho del contenido:** ancho completo.
- **Espaciado vertical:** ninguno arriba y abajo.
No es obligatorio para que se vea bien: los bloques se abren a todo el ancho por sí mismos, pero estos ajustes quitan los espacios sobrantes que agrega Blocksy.

## 4. Fotos y datos
- Banners: elegí tus fotos con el panel del bloque "Pila de banners" y completá la descripción de cada una.
- Categorías: la miniatura de cada categoría se carga en **Productos > Categorías**.
- Productos destacados: salen de los productos marcados con la estrella.

## 5. Cambiar colores y tipografía
Los bloques usan variables CSS. Para cambiarlas, en **Personalizar > CSS adicional**:

```css
:root {
  --color-fd-wine: #626545;      /* acento: botones, enlaces */
  --color-fd-olive: #74784e;     /* palabra en cursiva de los títulos */
  --color-fd-blue: #292824;      /* títulos y secciones oscuras */
  --color-fd-cream: #f5f2ed;     /* fondos claros */
  --color-fd-sand: #e9e6dc;      /* superficies suaves */
  --font-sans: "DM Sans Variable", system-ui, sans-serif;
  --font-serif: Georgia, serif;  /* cursiva de los títulos */
}
```

## 6. Publicar
1. Revisá la **Vista previa** (escritorio y móvil).
2. **Ajustes > Lectura > Una página estática > Página de inicio**: elegí la nueva. La portada de Elementor (838) queda como respaldo; para volver atrás, elegís otra vez la 838.
3. Con la portada ya en bloques y verificada, el plan de `PLAN-DESACTIVAR-ELEMENTOR.md` sigue igual (las demás páginas y plantillas).
