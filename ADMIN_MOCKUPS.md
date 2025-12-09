# Sistema de Administración de Mockups

## 📋 Descripción

Sistema completo de gestión de mockups con roles de usuario. Permite a los administradores subir, editar, activar/desactivar y eliminar mockups compartidos.

## 👤 Usuario Administrador

### Credenciales por Defecto

El sistema ya cuenta con un usuario administrador creado:

```
Email: admin@idogui.com
Role: admin
```

**IMPORTANTE**: Si no conoces la contraseña, puedes cambiarla ejecutando:

```bash
php artisan tinker
```

Luego en Tinker:
```php
$admin = App\Models\User::where('email', 'admin@idogui.com')->first();
$admin->password = bcrypt('nueva_contraseña');
$admin->save();
exit
```

### Crear Nuevos Administradores

Para crear un nuevo usuario administrador:

```bash
php artisan tinker
```

```php
App\Models\User::create([
    'name' => 'Nuevo Admin',
    'email' => 'nuevo@admin.com',
    'password' => bcrypt('contraseña_segura'),
    'role' => 'admin',
    'plan' => 'enterprise',
    'email_verified_at' => now(),
]);
exit
```

## 🔐 Sistema de Permisos

### Roles Disponibles

- **user** (por defecto): Usuario normal
- **admin**: Administrador con acceso a gestión de mockups

### Verificación de Permisos

El middleware `EnsureUserIsAdmin` verifica que:
1. El usuario esté autenticado
2. El usuario tenga el rol 'admin'

Si no se cumple, redirige al dashboard (web) o devuelve error 403 (API).

## 🎨 Interfaz de Administración

### Acceso

URL: `http://tu-dominio.com/admin/mockups`

**Requisitos**: 
- Usuario autenticado
- Rol de administrador

### Funcionalidades

#### 1. **Estadísticas**
- Total de mockups
- Mockups activos
- Mockups inactivos
- Distribución por categoría
- Mockups más utilizados

#### 2. **Filtros**
- Por categoría (device-frames, ui-elements, backgrounds, general)
- Por estado (activos, inactivos, todos)
- Ordenar por (recientes, más usados, nombre)

#### 3. **Gestión de Mockups**

**Subir Nuevo Mockup**:
- Nombre
- Categoría
- Imagen principal (PNG, JPG - Max 10MB)
- Thumbnail opcional (Max 2MB)
- Dimensiones (auto-detectadas o manuales)

**Editar Mockup**:
- Cambiar nombre
- Cambiar categoría
- Ajustar dimensiones

**Activar/Desactivar**:
- Los mockups desactivados NO aparecen para usuarios normales
- Los mockups ya insertados en diseños NO se ven afectados

**Eliminar Permanentemente**:
- Elimina el registro de la BD
- Elimina los archivos del servidor (imagen y thumbnail)
- Los diseños que ya usan este mockup NO se ven afectados

## 🔌 API de Administración

### Endpoints Protegidos (Requieren autenticación + rol admin)

#### Listar todos los mockups (incluidos inactivos)
```http
GET /api/admin/mockups
Query params:
  - category (opcional): device-frames, ui-elements, backgrounds, general
  - is_active (opcional): 1 (activos), 0 (inactivos)
  - order_by (opcional): created_at, usage_count, name
  - order_direction (opcional): asc, desc
```

#### Subir mockup
```http
POST /api/admin/mockups
Content-Type: multipart/form-data

{
  "name": "iPhone 15 Pro Frame",
  "category": "device-frames",
  "file": <archivo>,
  "thumbnail": <archivo> (opcional),
  "width": 1179 (opcional),
  "height": 2556 (opcional)
}
```

#### Actualizar mockup
```http
PUT /api/admin/mockups/{id}
Content-Type: application/json

{
  "name": "Nuevo nombre",
  "category": "nueva-categoria",
  "width": 1200,
  "height": 2600
}
```

#### Activar/Desactivar mockup
```http
POST /api/admin/mockups/{id}/toggle-active
```

#### Eliminar mockup permanentemente
```http
DELETE /api/admin/mockups/{id}
```

#### Obtener estadísticas
```http
GET /api/admin/mockups/stats
```

### Respuestas de Error

**403 Forbidden** - No tiene permisos de administrador
```json
{
  "message": "Acceso denegado. Se requieren permisos de administrador."
}
```

**404 Not Found** - Mockup no encontrado
```json
{
  "message": "No query results for model [App\\Models\\Mockup] {id}"
}
```

**422 Unprocessable Entity** - Validación fallida
```json
{
  "message": "The given data was invalid.",
  "errors": {
    "name": ["El campo nombre es obligatorio."],
    "file": ["El archivo debe ser una imagen."]
  }
}
```

## 📁 Estructura de Archivos

```
storage/app/public/mockups/
├── imagen1.png
├── imagen2.png
└── thumbnails/
    ├── imagen1-thumb.png
    └── imagen2-thumb.png
```

**URLs públicas**:
- Imagen principal: `http://tu-dominio.com/storage/mockups/imagen1.png`
- Thumbnail: `http://tu-dominio.com/storage/mockups/thumbnails/imagen1-thumb.png`

## 🔄 Flujo de Trabajo

### Agregar Nuevo Mockup

1. **Login como admin**: `http://tu-dominio.com/login`
2. **Acceder a administración**: `http://tu-dominio.com/admin/mockups`
3. **Click en "Subir Mockup"**
4. **Completar formulario**:
   - Nombre descriptivo
   - Seleccionar categoría
   - Cargar imagen principal
   - (Opcional) Cargar thumbnail
   - (Opcional) Ajustar dimensiones
5. **Subir**: El mockup estará disponible inmediatamente para todos los usuarios

### Desactivar Mockup (Sin Eliminar)

1. **Buscar mockup** en la lista
2. **Click en botón de ojo** (toggle)
3. El mockup se marca como inactivo
4. **Los usuarios NO podrán ver ni insertar** este mockup
5. **Los diseños existentes NO se afectan**

### Eliminar Mockup Permanentemente

1. **Buscar mockup** en la lista
2. **Click en botón de papelera**
3. **Confirmar eliminación**
4. Se eliminan:
   - Registro de base de datos
   - Archivo de imagen principal
   - Archivo de thumbnail (si existe)
5. **Los diseños existentes NO se afectan** (mantienen su copia)

## 🛡️ Seguridad

### Validaciones Implementadas

**Subida de archivos**:
- Solo imágenes (jpeg, png, jpg, gif)
- Tamaño máximo imagen: 10MB
- Tamaño máximo thumbnail: 2MB

**Middleware**:
- Verificación de autenticación
- Verificación de rol admin
- Protección CSRF para rutas web

**Autorización**:
- Solo administradores pueden acceder a `/admin/*`
- Solo administradores pueden usar endpoints `/api/admin/*`

### Recomendaciones

1. **Cambiar contraseña de admin por defecto** en producción
2. **Usar HTTPS** para proteger credenciales
3. **Limitar intentos de login** (rate limiting)
4. **Backup regular** de la carpeta `storage/app/public/mockups/`
5. **Monitorear uso de espacio** en disco

## 🧪 Testing

### Verificar Permisos

**Como usuario normal**:
```bash
# Intentar acceder (debe redirigir al dashboard)
curl -X GET http://localhost/admin/mockups \
  -H "Cookie: laravel_session=..."
```

**Como admin**:
```bash
# Debe mostrar la interfaz de administración
curl -X GET http://localhost/admin/mockups \
  -H "Cookie: laravel_session=..."
```

### Probar API

**Sin autenticación** (debe fallar):
```bash
curl -X GET http://localhost/api/admin/mockups
```

**Con autenticación de usuario normal** (debe fallar):
```bash
curl -X GET http://localhost/api/admin/mockups \
  -H "Cookie: laravel_session=..."
```

**Con autenticación de admin** (debe funcionar):
```bash
curl -X GET http://localhost/api/admin/mockups \
  -H "Cookie: laravel_session=..."
```

## 📊 Monitoreo

### Consultas SQL Útiles

**Ver mockups más usados**:
```sql
SELECT name, category, usage_count 
FROM mockups 
ORDER BY usage_count DESC 
LIMIT 10;
```

**Ver mockups inactivos**:
```sql
SELECT name, category, created_at 
FROM mockups 
WHERE is_active = 0;
```

**Espacio usado por categoría**:
```bash
# Linux/Mac
du -sh storage/app/public/mockups/*

# Windows PowerShell
Get-ChildItem storage\app\public\mockups -Recurse | 
  Measure-Object -Property Length -Sum | 
  Select-Object @{Name="Size(MB)";Expression={$_.Sum / 1MB}}
```

## 🔧 Mantenimiento

### Limpiar Mockups Huérfanos

Si tienes archivos sin registro en BD:

```bash
php artisan tinker
```

```php
$files = Storage::files('public/mockups');
$mockups = App\Models\Mockup::pluck('filename')->toArray();

foreach ($files as $file) {
    $filename = basename($file);
    if (!in_array($filename, $mockups)) {
        echo "Archivo huérfano: $filename\n";
        // Storage::delete($file); // Descomentar para eliminar
    }
}
```

### Regenerar Thumbnails

Si necesitas regenerar thumbnails automáticamente, puedes crear un comando Artisan personalizado.

## 🚀 Deploy en Producción

### Checklist

- [ ] Cambiar contraseña de admin por defecto
- [ ] Configurar permisos de carpetas `storage/`
- [ ] Ejecutar `php artisan storage:link`
- [ ] Configurar backup automático de mockups
- [ ] Implementar rate limiting
- [ ] Configurar logs de auditoría
- [ ] Usar HTTPS
- [ ] Optimizar imágenes antes de subir

### Comandos Post-Deploy

```bash
# Migrar base de datos
php artisan migrate --force

# Crear enlace simbólico
php artisan storage:link

# Limpiar cachés
php artisan config:cache
php artisan route:cache
php artisan view:cache

# Crear admin si no existe
php artisan db:seed --class=AdminUserSeeder
```

## 📞 Soporte

### Logs

- Laravel: `storage/logs/laravel.log`
- Consola del navegador (F12): Errores JavaScript
- Network tab: Errores de API

### Problemas Comunes

**No puedo acceder a /admin/mockups**:
- Verifica que estés autenticado
- Verifica que tu usuario tenga role='admin'
- Revisa `storage/logs/laravel.log`

**Las imágenes no se ven**:
- Ejecuta `php artisan storage:link`
- Verifica permisos de `storage/app/public/mockups/`
- Verifica que los archivos existan físicamente

**Error al subir archivo grande**:
- Aumenta `upload_max_filesize` en `php.ini`
- Aumenta `post_max_size` en `php.ini`
- Reinicia servidor web

## 📚 Referencias

- Modelo: `app/Models/Mockup.php`
- Controlador: `app/Http/Controllers/MockupController.php`
- Middleware: `app/Http/Middleware/EnsureUserIsAdmin.php`
- Componente Vue: `resources/js/components/AdminMockups.vue`
- Rutas API: `routes/api.php` (líneas mockups admin)
- Rutas Web: `routes/web.php` (líneas admin)
