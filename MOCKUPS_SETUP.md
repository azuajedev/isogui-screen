# Configuración de Mockups - Guía de Uso

## 📋 Descripción

El sistema de **Mockups** es una biblioteca compartida de imágenes prediseñadas que todos los usuarios pueden acceder e insertar en sus diseños. Es diferente de la Galería (que es personal por usuario) ya que estos mockups están disponibles globalmente.

## 🎯 Casos de Uso

- **Device Frames**: Marcos de dispositivos (iPhone, iPad, MacBook, etc.)
- **UI Elements**: Elementos de interfaz (botones, barras de navegación, iconos)
- **Backgrounds**: Fondos decorativos (gradientes, patrones, texturas)
- **Decorative Elements**: Elementos decorativos adicionales

## 📁 Estructura de Archivos

Los archivos de mockup se almacenan en:
```
storage/app/public/mockups/          ← Imágenes principales
storage/app/public/mockups/thumbnails/ ← Miniaturas (opcional)
```

## 🚀 Cómo Agregar Nuevos Mockups

### Opción 1: Manualmente (Recomendado para pocos archivos)

1. **Copiar archivos de imagen** a la carpeta de mockups:
   ```bash
   # Copiar imagen principal
   copy "C:\ruta\imagen.png" "storage\app\public\mockups\imagen.png"
   
   # Copiar thumbnail (opcional pero recomendado)
   copy "C:\ruta\imagen-thumb.png" "storage\app\public\mockups\thumbnails\imagen-thumb.png"
   ```

2. **Insertar registro en la base de datos**:
   ```sql
   INSERT INTO mockups (name, category, filename, thumbnail, width, height, is_active, created_at, updated_at)
   VALUES (
     'Nombre del Mockup',
     'device-frames',  -- o 'ui-elements', 'backgrounds', 'general'
     'imagen.png',
     'imagen-thumb.png',
     1179,  -- ancho en píxeles
     2556,  -- alto en píxeles
     1,     -- activo
     NOW(),
     NOW()
   );
   ```

### Opción 2: Usando el Seeder (Recomendado para múltiples archivos)

1. **Editar** `database/seeders/MockupSeeder.php`
2. **Agregar** nuevos mockups al array `$mockups`:
   ```php
   [
       'name' => 'Galaxy S23 Frame',
       'category' => 'device-frames',
       'filename' => 'galaxy-s23-frame.png',
       'thumbnail' => 'galaxy-s23-frame-thumb.png',
       'width' => 1080,
       'height' => 2340,
       'is_active' => true,
   ],
   ```

3. **Copiar archivos** a las carpetas correspondientes
4. **Ejecutar** el seeder:
   ```bash
   php artisan db:seed --class=MockupSeeder
   ```

### Opción 3: Crear un Comando de Importación (Para producción)

Puedes crear un comando Artisan para importar mockups desde una carpeta:

```bash
php artisan make:command ImportMockups
```

## 📊 Categorías Disponibles

Las categorías actuales son:

- `device-frames` - Marcos de dispositivos
- `ui-elements` - Elementos de interfaz
- `backgrounds` - Fondos
- `general` - General (por defecto)

Para agregar nuevas categorías, simplemente usa un nuevo valor en el campo `category`.

## 🖼️ Recomendaciones de Imágenes

### Formato
- **Formato**: PNG con transparencia (recomendado) o JPG
- **Calidad**: Alta resolución para mejor visualización

### Dimensiones
- **Device Frames**: Mantener proporciones reales del dispositivo
  - iPhone 14 Pro: 1179x2556px
  - iPad Pro 12.9": 2048x2732px
  - MacBook Pro 16": 3456x2234px
  
- **UI Elements**: Variable según el elemento
  - Botones: 200-400px de ancho
  - Navegación: 1920x80-120px
  
- **Backgrounds**: Estándar de pantalla
  - Full HD: 1920x1080px
  - 4K: 3840x2160px

### Thumbnails
- **Dimensiones recomendadas**: 200x200px o proporcional
- **Formato**: JPG para menor tamaño de archivo
- **Propósito**: Carga rápida en la interfaz

## 🔍 Verificar Mockups en la Base de Datos

```sql
-- Ver todos los mockups
SELECT * FROM mockups ORDER BY category, name;

-- Ver mockups por categoría
SELECT * FROM mockups WHERE category = 'device-frames';

-- Ver mockups más usados
SELECT * FROM mockups ORDER BY usage_count DESC LIMIT 10;
```

## 🔗 Enlaces Simbólicos

Asegúrate de que el enlace simbólico de storage está creado:

```bash
php artisan storage:link
```

Esto permite que las imágenes en `storage/app/public` sean accesibles desde `public/storage`.

## 📝 Ejemplo Completo

### 1. Preparar Archivos
```
- iphone-14-pro-frame.png (3 MB, 1179x2556px)
- iphone-14-pro-frame-thumb.png (50 KB, 200x434px)
```

### 2. Copiar a Storage
```powershell
copy "C:\Descargas\iphone-14-pro-frame.png" "storage\app\public\mockups\iphone-14-pro-frame.png"
copy "C:\Descargas\iphone-14-pro-frame-thumb.png" "storage\app\public\mockups\thumbnails\iphone-14-pro-frame-thumb.png"
```

### 3. Insertar en Base de Datos
```sql
INSERT INTO mockups (name, category, filename, thumbnail, width, height, is_active, created_at, updated_at)
VALUES (
  'iPhone 14 Pro Frame',
  'device-frames',
  'iphone-14-pro-frame.png',
  'iphone-14-pro-frame-thumb.png',
  1179,
  2556,
  1,
  NOW(),
  NOW()
);
```

### 4. Verificar en la Aplicación
- Abrir el editor de mockups
- Ir a la sección "Mockups" en el sidebar
- Filtrar por categoría "device-frames"
- El nuevo mockup debe aparecer en la lista

## 🚨 Solución de Problemas

### Las imágenes no se ven
1. Verificar enlace simbólico: `php artisan storage:link`
2. Verificar permisos de carpetas
3. Verificar que los archivos existen en `storage/app/public/mockups/`

### El mockup no aparece en la lista
1. Verificar que `is_active = 1`
2. Verificar que el archivo existe
3. Revisar categoría asignada

### Error al insertar en canvas
1. Verificar que las URLs son accesibles
2. Verificar dimensiones (width y height) en la BD

## 📦 Mockups Actuales (Ejemplo)

Los siguientes mockups están disponibles como ejemplo (sin imágenes reales):

| Nombre | Categoría | Dimensiones |
|--------|-----------|-------------|
| iPhone 14 Pro Frame | device-frames | 1179x2556 |
| MacBook Pro 16" Frame | device-frames | 3456x2234 |
| iPad Pro 12.9" Frame | device-frames | 2048x2732 |
| Gradient Background 1 | backgrounds | 1920x1080 |
| Gradient Background 2 | backgrounds | 1920x1080 |
| Button Set | ui-elements | 800x600 |
| Navigation Bar | ui-elements | 1920x80 |

**Nota**: Estos registros están en la base de datos pero necesitas agregar las imágenes reales.

## 🎨 Recursos Sugeridos

Para obtener mockups gratuitos:
- **Freepik**: https://www.freepik.com/free-photos-vectors/mockup
- **Mockup World**: https://www.mockupworld.co/
- **Figma Community**: Recursos gratuitos de mockups
- **Unsplash**: Imágenes de alta calidad

## 📞 Soporte

Si tienes problemas, verifica:
1. Logs de Laravel: `storage/logs/laravel.log`
2. Consola del navegador para errores JavaScript
3. Permisos de carpetas en `storage/`
