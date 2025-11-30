# IsoGUI Screen

<p align="center">
  <img src="https://img.shields.io/badge/Laravel-11-FF2D20?style=for-the-badge&logo=laravel&logoColor=white" alt="Laravel 11">
  <img src="https://img.shields.io/badge/Vue.js-3-4FC08D?style=for-the-badge&logo=vue.js&logoColor=white" alt="Vue 3">
  <img src="https://img.shields.io/badge/PHP-8.2+-777BB4?style=for-the-badge&logo=php&logoColor=white" alt="PHP 8.2+">
  <img src="https://img.shields.io/badge/License-MIT-blue?style=for-the-badge" alt="MIT License">
</p>

## 📱 Descripción

**IsoGUI Screen** es una aplicación SaaS para crear mockups profesionales de screenshots de aplicaciones móviles. Diseñada para desarrolladores, diseñadores y equipos de marketing que necesitan generar imágenes atractivas para App Store y Google Play.

### ✨ Características Principales

- 🎨 **Editor Visual Interactivo** - Diseña mockups con arrastrar y soltar
- 📐 **Templates Profesionales** - Plantillas pre-diseñadas para App Store y Play Store
- 🌍 **Multi-idioma** - Genera mockups en múltiples idiomas
- 🤖 **Generación con IA** - Textos de marketing generados automáticamente
- 📦 **Exportación Múltiple** - PNG, JPEG, WebP
- 👥 **Sistema de Planes** - Free, Pro y Enterprise

## 🚀 Instalación Rápida

```bash
# Clonar repositorio
git clone https://github.com/azuajedev/isogui-screen.git
cd isogui-screen

# Instalar dependencias
composer install
npm install

# Configurar entorno
cp .env.example .env
php artisan key:generate

# Crear base de datos y ejecutar migraciones
php artisan migrate --seed

# Compilar assets
npm run dev

# Iniciar servidor
php artisan serve
```

> 📖 Para instrucciones detalladas, consulta [INSTALLATION.md](INSTALLATION.md)

## 📋 Requisitos

- PHP >= 8.2
- Composer >= 2.0
- Node.js >= 18
- npm >= 9
- SQLite, MySQL o PostgreSQL

## 🏗️ Arquitectura

```
app/
├── Http/Controllers/
│   ├── DashboardController.php    # Dashboard y estadísticas
│   ├── ProjectController.php      # CRUD de proyectos
│   ├── ScreenshotController.php   # Gestión de screenshots
│   ├── TemplateController.php     # Listado de templates
│   ├── RenderController.php       # Renderizado de mockups
│   └── MarketingCopyController.php # Generación de textos con IA
├── Models/
│   ├── User.php                   # Usuario con roles y planes
│   ├── Project.php                # Proyectos del usuario
│   ├── Screenshot.php             # Screenshots subidos
│   ├── Template.php               # Templates de mockups
│   └── RenderedImage.php          # Imágenes generadas
├── Services/
│   ├── MockupRenderService.php    # Motor de renderizado
│   └── MarketingCopyService.php   # Integración con IA
└── Policies/
    ├── ProjectPolicy.php          # Autorización de proyectos
    └── TemplatePolicy.php         # Autorización de templates
```

## 🔌 API Endpoints

### Autenticación Requerida

| Método | Endpoint | Descripción |
|--------|----------|-------------|
| GET | `/api/dashboard/stats` | Estadísticas del usuario |
| GET | `/api/projects` | Listar proyectos |
| POST | `/api/projects` | Crear proyecto |
| GET | `/api/projects/{id}` | Ver proyecto |
| PUT | `/api/projects/{id}` | Actualizar proyecto |
| DELETE | `/api/projects/{id}` | Eliminar proyecto |
| POST | `/api/projects/{id}/screenshots` | Subir screenshots |
| GET | `/api/templates` | Listar templates |
| POST | `/api/render` | Renderizar mockup |
| POST | `/api/marketing/generate` | Generar textos con IA |

## 📊 Modelos de Datos

### User
- `role`: enum('user', 'admin')
- `plan`: string('free', 'pro', 'enterprise')
- `plan_expires_at`: timestamp

### Project
- `user_id`: foreign key
- `name`: string
- `description`: text
- `app_type`: string

### Screenshot
- `project_id`: foreign key
- `original_filename`: string
- `stored_path`: string
- `orientation`: enum('horizontal', 'vertical')
- `width`, `height`: integer
- `file_size`: integer

### Template
- `name`, `slug`: string
- `type`: string (app-store, play-store)
- `orientation`: enum('horizontal', 'vertical', 'both')
- `layout_config`: json
- `is_premium`, `is_active`: boolean

### RenderedImage
- `screenshot_id`, `template_id`: foreign keys
- `language`: string
- `texts`: json
- `output_path`: string
- `output_format`: string

## 🎨 Frontend

El frontend está construido con **Vue 3** y **Composition API**:

- `MockupEditor.vue` - Editor principal con paneles laterales
- `MockupCanvas.vue` - Canvas de renderizado con controles de zoom

### Compilar para Producción

```bash
npm run build
```

## 🧪 Testing

```bash
# Tests PHP
php artisan test

# Tests con cobertura
php artisan test --coverage
```

## 📝 Planes y Límites

| Característica | Free | Pro | Enterprise |
|----------------|------|-----|------------|
| Proyectos | 3 | 20 | Ilimitados |
| Templates Premium | ❌ | ✅ | ✅ |
| Generación IA | ❌ | ✅ | ✅ |
| Traducción | ❌ | ✅ | ✅ |

## 🔧 Configuración

### Variables de Entorno

```env
# IA (OpenAI o compatible)
AI_API_KEY=sk-your-api-key
AI_ENDPOINT=https://api.openai.com/v1/chat/completions
AI_MODEL=gpt-3.5-turbo

# Browsershot (opcional, para renderizado avanzado)
NODE_BINARY=/usr/bin/node
NPM_BINARY=/usr/bin/npm
CHROME_PATH=/usr/bin/google-chrome
```

## 🤝 Contribuir

1. Fork el repositorio
2. Crea una rama (`git checkout -b feature/nueva-caracteristica`)
3. Commit tus cambios (`git commit -am 'Añadir nueva característica'`)
4. Push a la rama (`git push origin feature/nueva-caracteristica`)
5. Abre un Pull Request

## 📄 Licencia

Este proyecto está bajo la Licencia MIT. Ver [LICENSE](LICENSE) para más detalles.

## 👨‍💻 Autor

Desarrollado por [@azuajedev](https://github.com/azuajedev)

---

<p align="center">
  Hecho con ❤️ usando Laravel 11 y Vue 3
</p>
