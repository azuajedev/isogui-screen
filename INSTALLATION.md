# Guía de Instalación - Idogui Screen

Esta guía te ayudará a configurar Idogui Screen en tu entorno de desarrollo local.

## 📋 Requisitos Previos

### Software Requerido

| Software | Versión Mínima | Verificar |
|----------|----------------|-----------|
| PHP | 8.2+ | `php -v` |
| Composer | 2.0+ | `composer -V` |
| Node.js | 18+ | `node -v` |
| npm | 9+ | `npm -v` |

### Extensiones PHP Requeridas

- BCMath
- Ctype
- Fileinfo
- JSON
- Mbstring
- OpenSSL
- PDO
- Tokenizer
- XML
- GD (para procesamiento de imágenes)

Verificar extensiones instaladas:
```bash
php -m
```

## 🚀 Instalación Paso a Paso

### 1. Clonar el Repositorio

```bash
git clone https://github.com/azuajedev/isogui-screen.git
cd isogui-screen
```

### 2. Instalar Dependencias PHP

```bash
composer install
```

Si hay problemas de memoria:
```bash
COMPOSER_MEMORY_LIMIT=-1 composer install
```

### 3. Instalar Dependencias JavaScript

```bash
npm install
```

### 4. Configurar Variables de Entorno

```bash
# Copiar archivo de ejemplo
cp .env.example .env

# Generar clave de aplicación
php artisan key:generate
```

### 5. Configurar Base de Datos

#### Opción A: SQLite (Recomendado para desarrollo)

El proyecto ya viene configurado para SQLite. Solo necesitas crear el archivo:

```bash
touch database/database.sqlite
```

#### Opción B: MySQL

Editar `.env`:
```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=isogui_screen
DB_USERNAME=tu_usuario
DB_PASSWORD=tu_contraseña
```

Crear la base de datos:
```sql
CREATE DATABASE isogui_screen CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
```

#### Opción C: PostgreSQL

Editar `.env`:
```env
DB_CONNECTION=pgsql
DB_HOST=127.0.0.1
DB_PORT=5432
DB_DATABASE=isogui_screen
DB_USERNAME=tu_usuario
DB_PASSWORD=tu_contraseña
```

### 6. Ejecutar Migraciones y Seeders

```bash
php artisan migrate --seed
```

Esto creará:
- Todas las tablas necesarias
- Usuario de prueba: `test@example.com`
- Usuario admin: `admin@example.com`
- 4 templates profesionales

### 7. Crear Enlace Simbólico para Storage

```bash
php artisan storage:link
```

### 8. Compilar Assets del Frontend

#### Desarrollo (con hot reload)
```bash
npm run dev
```

#### Producción
```bash
npm run build
```

### 9. Iniciar el Servidor

```bash
php artisan serve
```

La aplicación estará disponible en: `http://localhost:8000`

## ⚙️ Configuración Adicional

### Configurar Generación de Textos con IA (Opcional)

Si deseas usar la funcionalidad de generación de textos con IA:

1. Obtén una API key de OpenAI o servicio compatible
2. Añade a tu `.env`:

```env
AI_API_KEY=sk-tu-api-key-aqui
AI_ENDPOINT=https://api.openai.com/v1/chat/completions
AI_MODEL=gpt-3.5-turbo
```

### Configurar Browsershot (Opcional)

Para renderizado avanzado con HTML/CSS:

1. Instalar Node.js y npm (ya deberías tenerlos)
2. Instalar Puppeteer globalmente:
```bash
npm install -g puppeteer
```

3. Configurar en `.env`:
```env
NODE_BINARY=/usr/bin/node
NPM_BINARY=/usr/bin/npm
CHROME_PATH=/usr/bin/google-chrome
BROWSERSHOT_TIMEOUT=60
```

### Configurar Cola de Trabajos (Opcional)

Para procesar renderizados en segundo plano:

1. Editar `.env`:
```env
QUEUE_CONNECTION=database
```

2. Ejecutar el worker:
```bash
php artisan queue:work
```

## 🧪 Verificar la Instalación

### Verificar PHP
```bash
php artisan --version
# Debería mostrar: Laravel Framework 11.x.x
```

### Verificar Base de Datos
```bash
php artisan migrate:status
# Todas las migraciones deberían mostrar "Ran"
```

### Verificar Seeders
```bash
php artisan tinker
>>> App\Models\User::count()
# Debería mostrar: 2

>>> App\Models\Template::count()
# Debería mostrar: 4
```

### Verificar Frontend
```bash
npm run build
# Debería compilar sin errores
```

## 🔍 Solución de Problemas

### Error: "Class not found"
```bash
composer dump-autoload
php artisan config:clear
php artisan cache:clear
```

### Error: "SQLSTATE: no such table"
```bash
php artisan migrate:fresh --seed
```

### Error: "Permission denied" en storage
```bash
chmod -R 775 storage bootstrap/cache
chown -R www-data:www-data storage bootstrap/cache
```

### Error: "Vite manifest not found"
```bash
npm run build
```

### Error de memoria en PHP
Editar `php.ini`:
```ini
memory_limit = 512M
```

### Error de timeout en renderizado
Editar `.env`:
```env
BROWSERSHOT_TIMEOUT=120
```

## 📁 Estructura del Proyecto

```
isogui-screen/
├── app/
│   ├── Http/Controllers/    # Controladores API
│   ├── Models/              # Modelos Eloquent
│   ├── Policies/            # Políticas de autorización
│   ├── Providers/           # Service Providers
│   └── Services/            # Servicios de negocio
├── config/                  # Archivos de configuración
├── database/
│   ├── migrations/          # Migraciones de BD
│   └── seeders/             # Seeders
├── public/                  # Archivos públicos
├── resources/
│   ├── css/                 # Estilos CSS
│   ├── js/                  # JavaScript y Vue
│   │   └── components/      # Componentes Vue
│   └── views/               # Vistas Blade
├── routes/
│   ├── api.php              # Rutas API
│   └── web.php              # Rutas web
├── storage/                 # Archivos generados
└── tests/                   # Tests automatizados
```

## 🚢 Despliegue en Producción

### Variables de Entorno para Producción

```env
APP_ENV=production
APP_DEBUG=false
APP_URL=https://tu-dominio.com

# Usar driver de sesión y cache apropiados
SESSION_DRIVER=redis
CACHE_DRIVER=redis

# Queue para procesos en segundo plano
QUEUE_CONNECTION=redis
```

### Optimizar para Producción

```bash
# Optimizar autoload
composer install --optimize-autoloader --no-dev

# Cache de configuración
php artisan config:cache
php artisan route:cache
php artisan view:cache

# Compilar assets
npm run build
```

## 📞 Soporte

Si tienes problemas con la instalación:

1. Revisa los [Issues](https://github.com/azuajedev/isogui-screen/issues) existentes
2. Abre un nuevo Issue con:
   - Sistema operativo
   - Versiones de PHP, Node.js, npm
   - Mensaje de error completo
   - Pasos para reproducir

---

<p align="center">
  <strong>¿Listo para crear mockups increíbles?</strong><br>
  Ejecuta <code>php artisan serve</code> y comienza a diseñar 🚀
</p>
