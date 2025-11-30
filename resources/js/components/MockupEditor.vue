<template>
  <div class="mockup-editor">
    <!-- Header -->
    <header class="editor-header">
      <div class="header-left">
        <h1 class="logo">
          <span class="logo-icon">🎨</span>
          IsoGUI Screen
        </h1>
      </div>
      <div class="header-center">
        <span class="project-name">{{ projectName || 'Nuevo Proyecto' }}</span>
      </div>
      <div class="header-right">
        <button class="btn btn-secondary" @click="handleSave">
          <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
            <path d="M19 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11l5 5v11a2 2 0 0 1-2 2z"/>
            <polyline points="17 21 17 13 7 13 7 21"/>
            <polyline points="7 3 7 8 15 8"/>
          </svg>
          Guardar
        </button>
        <button class="btn btn-primary" @click="handleExport">
          <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
            <path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/>
            <polyline points="7 10 12 15 17 10"/>
            <line x1="12" y1="15" x2="12" y2="3"/>
          </svg>
          Exportar
        </button>
      </div>
    </header>

    <!-- Main Content -->
    <main class="editor-main">
      <!-- Sidebar Izquierda: Screenshots y Templates -->
      <aside class="sidebar sidebar-left">
        <!-- Sección Screenshots -->
        <div class="sidebar-section">
          <div class="section-header">
            <h2>Screenshots</h2>
            <button class="btn-icon" @click="handleUpload" title="Subir screenshot">
              <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/>
                <polyline points="17 8 12 3 7 8"/>
                <line x1="12" y1="3" x2="12" y2="15"/>
              </svg>
            </button>
          </div>

          <div class="screenshots-grid">
            <div 
              v-for="screenshot in screenshots" 
              :key="screenshot.id"
              class="screenshot-item"
              :class="{ active: selectedScreenshot?.id === screenshot.id }"
              @click="selectScreenshot(screenshot)"
            >
              <img :src="screenshot.url" :alt="screenshot.original_filename">
              <span class="screenshot-label">{{ screenshot.orientation }}</span>
            </div>

            <!-- Empty state -->
            <div v-if="screenshots.length === 0" class="empty-state">
              <svg xmlns="http://www.w3.org/2000/svg" width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
                <rect x="3" y="3" width="18" height="18" rx="2" ry="2"/>
                <circle cx="8.5" cy="8.5" r="1.5"/>
                <polyline points="21 15 16 10 5 21"/>
              </svg>
              <p>Sube tu primer screenshot</p>
              <button class="btn btn-primary btn-sm" @click="handleUpload">
                Subir imagen
              </button>
            </div>
          </div>
        </div>

        <!-- Sección Templates -->
        <div class="sidebar-section">
          <div class="section-header">
            <h2>Templates</h2>
            <select v-model="templateFilter" class="select-sm">
              <option value="all">Todos</option>
              <option value="vertical">Verticales</option>
              <option value="horizontal">Horizontales</option>
            </select>
          </div>

          <div class="templates-grid">
            <div 
              v-for="template in filteredTemplates" 
              :key="template.id"
              class="template-item"
              :class="{ 
                active: selectedTemplate?.id === template.id,
                premium: template.is_premium && !canUsePremium
              }"
              @click="selectTemplate(template)"
            >
              <div class="template-preview" :style="{ background: template.layout_config?.canvas?.background || '#6366f1' }">
                <span class="template-type">{{ template.type }}</span>
              </div>
              <span class="template-name">{{ template.name }}</span>
              <span v-if="template.is_premium" class="premium-badge">PRO</span>
            </div>
          </div>
        </div>
      </aside>

      <!-- Canvas Central -->
      <div class="editor-canvas-container">
        <MockupCanvas
          ref="canvasRef"
          :screenshot-url="selectedScreenshot?.url"
          :template="selectedTemplate"
          :texts="texts"
          :background-color="backgroundColor"
          @rendered="handleRendered"
          @error="handleRenderError"
        />
      </div>

      <!-- Sidebar Derecha: Opciones -->
      <aside class="sidebar sidebar-right">
        <!-- Textos -->
        <div class="sidebar-section">
          <div class="section-header">
            <h2>Textos</h2>
            <button 
              v-if="canUseAI" 
              class="btn-icon" 
              @click="handleGenerateAI" 
              title="Generar con IA"
            >
              <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <path d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z"/>
              </svg>
            </button>
          </div>

          <div class="form-group">
            <label>Headline</label>
            <input 
              type="text" 
              v-model="texts.headline" 
              placeholder="Texto principal llamativo"
              maxlength="50"
            >
            <span class="char-count">{{ texts.headline.length }}/50</span>
          </div>

          <div class="form-group">
            <label>Subheadline</label>
            <textarea 
              v-model="texts.subheadline" 
              placeholder="Descripción secundaria"
              maxlength="100"
              rows="2"
            ></textarea>
            <span class="char-count">{{ texts.subheadline.length }}/100</span>
          </div>

          <div class="form-group">
            <label>CTA (Llamada a la acción)</label>
            <input 
              type="text" 
              v-model="texts.cta" 
              placeholder="Ej: Descargar ahora"
              maxlength="30"
            >
            <span class="char-count">{{ texts.cta.length }}/30</span>
          </div>
        </div>

        <!-- Idioma -->
        <div class="sidebar-section">
          <div class="section-header">
            <h2>Idioma</h2>
          </div>

          <div class="language-grid">
            <button 
              v-for="lang in languages" 
              :key="lang.code"
              class="language-btn"
              :class="{ active: selectedLanguage === lang.code }"
              @click="selectedLanguage = lang.code"
            >
              {{ lang.name }}
            </button>
          </div>

          <button 
            v-if="canUseAI" 
            class="btn btn-outline btn-block mt-3"
            @click="handleTranslate"
          >
            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
              <path d="m5 8 6 6"/>
              <path d="m4 14 6-6 2-3"/>
              <path d="M2 5h12"/>
              <path d="M7 2h1"/>
              <path d="m22 22-5-10-5 10"/>
              <path d="M14 18h6"/>
            </svg>
            Traducir textos
          </button>
        </div>

        <!-- Colores -->
        <div class="sidebar-section">
          <div class="section-header">
            <h2>Color de fondo</h2>
          </div>

          <div class="color-grid">
            <button 
              v-for="color in presetColors" 
              :key="color"
              class="color-btn"
              :class="{ active: backgroundColor === color }"
              :style="{ backgroundColor: color }"
              @click="backgroundColor = color"
            ></button>
          </div>

          <div class="form-group mt-3">
            <label>Color personalizado</label>
            <div class="color-input-group">
              <input type="color" v-model="backgroundColor">
              <input type="text" v-model="backgroundColor" placeholder="#6366f1">
            </div>
          </div>
        </div>

        <!-- Exportar -->
        <div class="sidebar-section">
          <div class="section-header">
            <h2>Formato de exportación</h2>
          </div>

          <div class="export-options">
            <label class="radio-option">
              <input type="radio" v-model="exportFormat" value="png">
              <span>PNG</span>
              <small>Alta calidad, transparencia</small>
            </label>
            <label class="radio-option">
              <input type="radio" v-model="exportFormat" value="jpeg">
              <span>JPEG</span>
              <small>Archivo más pequeño</small>
            </label>
            <label class="radio-option">
              <input type="radio" v-model="exportFormat" value="webp">
              <span>WebP</span>
              <small>Mejor compresión</small>
            </label>
          </div>
        </div>
      </aside>
    </main>

    <!-- Input oculto para subir archivos -->
    <input 
      ref="fileInput"
      type="file" 
      accept="image/png,image/jpeg,image/webp" 
      multiple
      style="display: none"
      @change="handleFileSelect"
    >

    <!-- Modal de notificación -->
    <div v-if="notification.show" class="notification" :class="notification.type">
      {{ notification.message }}
    </div>
  </div>
</template>

<script>
import { ref, computed, onMounted } from 'vue';
import MockupCanvas from './MockupCanvas.vue';

export default {
  name: 'MockupEditor',

  components: {
    MockupCanvas,
  },

  setup() {
    // Refs
    const canvasRef = ref(null);
    const fileInput = ref(null);

    // Estado
    const projectName = ref('Mi Proyecto');
    const screenshots = ref([]);
    const selectedScreenshot = ref(null);
    const templates = ref([]);
    const selectedTemplate = ref(null);
    const templateFilter = ref('all');

    const texts = ref({
      headline: '',
      subheadline: '',
      cta: '',
    });

    const selectedLanguage = ref('es');
    const backgroundColor = ref('#6366f1');
    const exportFormat = ref('png');

    const notification = ref({
      show: false,
      type: 'success',
      message: '',
    });

    // Colores preestablecidos
    const presetColors = [
      '#6366f1', // Indigo
      '#8b5cf6', // Violet
      '#ec4899', // Pink
      '#ef4444', // Red
      '#f97316', // Orange
      '#eab308', // Yellow
      '#22c55e', // Green
      '#14b8a6', // Teal
      '#0ea5e9', // Sky
      '#3b82f6', // Blue
      '#1e293b', // Slate dark
      '#f8fafc', // White
    ];

    // Idiomas disponibles
    const languages = [
      { code: 'es', name: 'Español' },
      { code: 'en', name: 'English' },
      { code: 'pt', name: 'Português' },
      { code: 'fr', name: 'Français' },
      { code: 'de', name: 'Deutsch' },
    ];

    // Computed
    const canUsePremium = computed(() => {
      return window.IsoGUI?.user?.plan !== 'free';
    });

    const canUseAI = computed(() => {
      return window.IsoGUI?.user?.plan !== 'free';
    });

    const filteredTemplates = computed(() => {
      if (templateFilter.value === 'all') return templates.value;
      return templates.value.filter(t => 
        t.orientation === templateFilter.value || t.orientation === 'both'
      );
    });

    // Métodos
    const showNotification = (message, type = 'success') => {
      notification.value = { show: true, type, message };
      setTimeout(() => {
        notification.value.show = false;
      }, 3000);
    };

    const selectScreenshot = (screenshot) => {
      selectedScreenshot.value = screenshot;
    };

    const selectTemplate = (template) => {
      if (template.is_premium && !canUsePremium.value) {
        showNotification('Este template requiere plan PRO', 'warning');
        return;
      }
      selectedTemplate.value = template;
    };

    const handleUpload = () => {
      fileInput.value?.click();
    };

    const handleFileSelect = async (event) => {
      const files = event.target.files;
      if (!files.length) return;

      // Simular carga de screenshots (en producción, esto subiría al servidor)
      for (const file of files) {
        const url = URL.createObjectURL(file);
        const img = new Image();
        img.src = url;

        await new Promise((resolve) => {
          img.onload = () => {
            const screenshot = {
              id: Date.now() + Math.random(),
              original_filename: file.name,
              url: url,
              orientation: img.width > img.height ? 'horizontal' : 'vertical',
              width: img.width,
              height: img.height,
            };
            screenshots.value.push(screenshot);

            // Seleccionar automáticamente si es el primero
            if (screenshots.value.length === 1) {
              selectedScreenshot.value = screenshot;
            }

            resolve();
          };
        });
      }

      showNotification(`${files.length} screenshot(s) agregado(s)`);
      event.target.value = '';
    };

    const handleSave = async () => {
      showNotification('Proyecto guardado');
    };

    const handleExport = async () => {
      if (!canvasRef.value) return;

      try {
        // Obtener la imagen del canvas
        const dataUrl = await new Promise((resolve) => {
          canvasRef.value.render();
          setTimeout(() => {
            const canvas = canvasRef.value.$refs.canvas;
            resolve(canvas.toDataURL(`image/${exportFormat.value}`));
          }, 100);
        });

        // Crear enlace de descarga
        const link = document.createElement('a');
        link.download = `mockup-${Date.now()}.${exportFormat.value}`;
        link.href = dataUrl;
        link.click();

        showNotification('Mockup exportado exitosamente');
      } catch (error) {
        showNotification('Error al exportar', 'error');
        console.error(error);
      }
    };

    const handleGenerateAI = async () => {
      if (!canUseAI.value) {
        showNotification('Función disponible en plan PRO', 'warning');
        return;
      }

      showNotification('Generando textos con IA...', 'info');

      // Simular generación de IA
      setTimeout(() => {
        texts.value = {
          headline: 'Tu nueva app favorita',
          subheadline: 'Simplifica tu vida con nuestra solución innovadora',
          cta: 'Descargar gratis',
        };
        showNotification('Textos generados');
      }, 1500);
    };

    const handleTranslate = async () => {
      if (!canUseAI.value) {
        showNotification('Función disponible en plan PRO', 'warning');
        return;
      }

      showNotification('Traduciendo...', 'info');
      // Aquí iría la llamada al API de traducción
    };

    const handleRendered = (dataUrl) => {
      // Canvas renderizado correctamente
    };

    const handleRenderError = (error) => {
      showNotification('Error al renderizar', 'error');
    };

    const loadTemplates = async () => {
      // Cargar templates (mock data para demo)
      templates.value = [
        {
          id: 1,
          name: 'Gradiente Moderno',
          slug: 'gradient-modern',
          type: 'app-store',
          orientation: 'vertical',
          is_premium: false,
          layout_config: {
            canvas: {
              width: 1242,
              height: 2688,
              background: '#6366f1',
              gradient: { start: '#6366f1', end: '#8b5cf6', direction: 'vertical' }
            },
            screenshot: { x: 121, y: 500, width: 1000, height: 1800, shadow: { enabled: true, blur: 30, color: 'rgba(0,0,0,0.3)' } },
            texts: {
              headline: { x: 100, y: 200, size: 72, color: '#ffffff', weight: 'bold' },
              subheadline: { x: 100, y: 300, size: 36, color: 'rgba(255,255,255,0.8)' },
              cta: { x: 100, y: 2550, size: 28, color: '#ffffff' }
            }
          }
        },
        {
          id: 2,
          name: 'Minimalista Blanco',
          slug: 'minimal-white',
          type: 'app-store',
          orientation: 'vertical',
          is_premium: false,
          layout_config: {
            canvas: { width: 1242, height: 2688, background: '#ffffff' },
            screenshot: { x: 121, y: 600, width: 1000, height: 1700, shadow: { enabled: true, blur: 40, color: 'rgba(0,0,0,0.15)' } },
            texts: {
              headline: { x: 100, y: 200, size: 64, color: '#1e293b', weight: 'bold' },
              subheadline: { x: 100, y: 300, size: 32, color: '#64748b' },
              cta: { x: 100, y: 2550, size: 24, color: '#6366f1', weight: '600' }
            }
          }
        },
        {
          id: 3,
          name: 'Dark Mode',
          slug: 'dark-mode',
          type: 'app-store',
          orientation: 'both',
          is_premium: true,
          layout_config: {
            canvas: { width: 1242, height: 2688, background: '#0f172a' },
            screenshot: { x: 121, y: 500, width: 1000, height: 1800, shadow: { enabled: true, blur: 50, color: 'rgba(99,102,241,0.3)' } },
            texts: {
              headline: { x: 100, y: 200, size: 72, color: '#f8fafc', weight: 'bold' },
              subheadline: { x: 100, y: 300, size: 36, color: '#94a3b8' },
              cta: { x: 100, y: 2550, size: 28, color: '#6366f1' }
            }
          }
        },
        {
          id: 4,
          name: 'Feature Hero',
          slug: 'feature-hero',
          type: 'play-store',
          orientation: 'horizontal',
          is_premium: true,
          layout_config: {
            canvas: { width: 1024, height: 500, background: '#1e293b', gradient: { start: '#1e293b', end: '#334155', direction: 'horizontal' } },
            screenshot: { x: 550, y: 50, width: 400, height: 400, shadow: { enabled: true, blur: 25 } },
            texts: {
              headline: { x: 50, y: 150, size: 48, color: '#f8fafc', weight: 'bold' },
              subheadline: { x: 50, y: 220, size: 24, color: '#94a3b8' },
              cta: { x: 50, y: 400, size: 20, color: '#22c55e' }
            }
          }
        }
      ];

      // Seleccionar el primer template por defecto
      if (templates.value.length > 0) {
        selectedTemplate.value = templates.value[0];
      }
    };

    onMounted(() => {
      loadTemplates();
    });

    return {
      // Refs
      canvasRef,
      fileInput,

      // Estado
      projectName,
      screenshots,
      selectedScreenshot,
      templates,
      selectedTemplate,
      templateFilter,
      texts,
      selectedLanguage,
      backgroundColor,
      exportFormat,
      notification,

      // Constantes
      presetColors,
      languages,

      // Computed
      canUsePremium,
      canUseAI,
      filteredTemplates,

      // Métodos
      selectScreenshot,
      selectTemplate,
      handleUpload,
      handleFileSelect,
      handleSave,
      handleExport,
      handleGenerateAI,
      handleTranslate,
      handleRendered,
      handleRenderError,
    };
  },
};
</script>

<style scoped>
/* ========================================
   Variables CSS
   ======================================== */
.mockup-editor {
  --primary: #6366f1;
  --primary-hover: #4f46e5;
  --bg: #0f172a;
  --bg-surface: #1e293b;
  --bg-surface-light: #334155;
  --text-primary: #f8fafc;
  --text-secondary: #94a3b8;
  --border: #334155;
  --success: #22c55e;
  --warning: #f59e0b;
  --error: #ef4444;
  --info: #0ea5e9;

  display: flex;
  flex-direction: column;
  height: 100vh;
  background: var(--bg);
  color: var(--text-primary);
  font-family: 'Inter', sans-serif;
}

/* ========================================
   Header
   ======================================== */
.editor-header {
  display: flex;
  align-items: center;
  justify-content: space-between;
  padding: 0.75rem 1.5rem;
  background: var(--bg-surface);
  border-bottom: 1px solid var(--border);
}

.header-left,
.header-right {
  display: flex;
  align-items: center;
  gap: 0.75rem;
}

.logo {
  display: flex;
  align-items: center;
  gap: 0.5rem;
  font-size: 1.125rem;
  font-weight: 600;
  color: var(--text-primary);
}

.logo-icon {
  font-size: 1.5rem;
}

.project-name {
  color: var(--text-secondary);
  font-size: 0.875rem;
}

/* ========================================
   Botones
   ======================================== */
.btn {
  display: inline-flex;
  align-items: center;
  gap: 0.5rem;
  padding: 0.5rem 1rem;
  font-size: 0.875rem;
  font-weight: 500;
  border-radius: 8px;
  border: none;
  cursor: pointer;
  transition: all 0.2s ease;
}

.btn-primary {
  background: var(--primary);
  color: white;
}

.btn-primary:hover {
  background: var(--primary-hover);
}

.btn-secondary {
  background: var(--bg-surface-light);
  color: var(--text-primary);
}

.btn-secondary:hover {
  background: #475569;
}

.btn-outline {
  background: transparent;
  border: 1px solid var(--border);
  color: var(--text-primary);
}

.btn-outline:hover {
  background: var(--bg-surface-light);
}

.btn-block {
  width: 100%;
  justify-content: center;
}

.btn-sm {
  padding: 0.375rem 0.75rem;
  font-size: 0.75rem;
}

.btn-icon {
  display: flex;
  align-items: center;
  justify-content: center;
  width: 32px;
  height: 32px;
  padding: 0;
  background: transparent;
  border: none;
  border-radius: 8px;
  color: var(--text-secondary);
  cursor: pointer;
  transition: all 0.2s ease;
}

.btn-icon:hover {
  background: var(--bg-surface-light);
  color: var(--text-primary);
}

/* ========================================
   Main Layout
   ======================================== */
.editor-main {
  display: flex;
  flex: 1;
  overflow: hidden;
}

/* ========================================
   Sidebars
   ======================================== */
.sidebar {
  width: 280px;
  background: var(--bg-surface);
  border-right: 1px solid var(--border);
  overflow-y: auto;
}

.sidebar-right {
  border-right: none;
  border-left: 1px solid var(--border);
}

.sidebar-section {
  padding: 1rem;
  border-bottom: 1px solid var(--border);
}

.section-header {
  display: flex;
  align-items: center;
  justify-content: space-between;
  margin-bottom: 0.75rem;
}

.section-header h2 {
  font-size: 0.75rem;
  font-weight: 600;
  text-transform: uppercase;
  letter-spacing: 0.05em;
  color: var(--text-secondary);
}

/* ========================================
   Screenshots Grid
   ======================================== */
.screenshots-grid {
  display: grid;
  grid-template-columns: repeat(2, 1fr);
  gap: 0.5rem;
}

.screenshot-item {
  position: relative;
  aspect-ratio: 9/16;
  border-radius: 8px;
  overflow: hidden;
  cursor: pointer;
  border: 2px solid transparent;
  transition: all 0.2s ease;
}

.screenshot-item:hover {
  border-color: var(--bg-surface-light);
}

.screenshot-item.active {
  border-color: var(--primary);
}

.screenshot-item img {
  width: 100%;
  height: 100%;
  object-fit: cover;
}

.screenshot-label {
  position: absolute;
  bottom: 4px;
  left: 4px;
  padding: 2px 6px;
  font-size: 0.625rem;
  background: rgba(0, 0, 0, 0.7);
  border-radius: 4px;
  color: white;
}

/* ========================================
   Templates Grid
   ======================================== */
.templates-grid {
  display: grid;
  grid-template-columns: repeat(2, 1fr);
  gap: 0.5rem;
}

.template-item {
  position: relative;
  border-radius: 8px;
  overflow: hidden;
  cursor: pointer;
  border: 2px solid transparent;
  transition: all 0.2s ease;
}

.template-item:hover {
  border-color: var(--bg-surface-light);
}

.template-item.active {
  border-color: var(--primary);
}

.template-item.premium {
  opacity: 0.6;
}

.template-preview {
  aspect-ratio: 9/16;
  display: flex;
  align-items: center;
  justify-content: center;
}

.template-type {
  font-size: 0.625rem;
  padding: 2px 6px;
  background: rgba(255, 255, 255, 0.2);
  border-radius: 4px;
  color: white;
}

.template-name {
  display: block;
  padding: 0.375rem;
  font-size: 0.75rem;
  color: var(--text-secondary);
  text-align: center;
  background: var(--bg-surface-light);
}

.premium-badge {
  position: absolute;
  top: 4px;
  right: 4px;
  padding: 2px 6px;
  font-size: 0.625rem;
  font-weight: 600;
  background: var(--warning);
  border-radius: 4px;
  color: black;
}

/* ========================================
   Empty State
   ======================================== */
.empty-state {
  grid-column: span 2;
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  padding: 2rem 1rem;
  text-align: center;
  color: var(--text-secondary);
}

.empty-state svg {
  margin-bottom: 0.75rem;
  opacity: 0.5;
}

.empty-state p {
  margin-bottom: 0.75rem;
  font-size: 0.875rem;
}

/* ========================================
   Canvas Container
   ======================================== */
.editor-canvas-container {
  flex: 1;
  display: flex;
  align-items: center;
  justify-content: center;
  padding: 2rem;
  background: var(--bg);
  overflow: hidden;
}

/* ========================================
   Form Elements
   ======================================== */
.form-group {
  margin-bottom: 1rem;
}

.form-group label {
  display: block;
  margin-bottom: 0.375rem;
  font-size: 0.75rem;
  font-weight: 500;
  color: var(--text-secondary);
}

.form-group input[type="text"],
.form-group textarea {
  width: 100%;
  padding: 0.5rem 0.75rem;
  font-size: 0.875rem;
  background: var(--bg-surface-light);
  border: 1px solid var(--border);
  border-radius: 8px;
  color: var(--text-primary);
  transition: border-color 0.2s ease;
}

.form-group input:focus,
.form-group textarea:focus {
  outline: none;
  border-color: var(--primary);
}

.form-group textarea {
  resize: vertical;
  min-height: 60px;
}

.char-count {
  display: block;
  margin-top: 0.25rem;
  font-size: 0.625rem;
  color: var(--text-secondary);
  text-align: right;
}

.select-sm {
  padding: 0.25rem 0.5rem;
  font-size: 0.75rem;
  background: var(--bg-surface-light);
  border: 1px solid var(--border);
  border-radius: 6px;
  color: var(--text-primary);
}

/* ========================================
   Language Grid
   ======================================== */
.language-grid {
  display: flex;
  flex-wrap: wrap;
  gap: 0.375rem;
}

.language-btn {
  padding: 0.375rem 0.75rem;
  font-size: 0.75rem;
  background: var(--bg-surface-light);
  border: 1px solid var(--border);
  border-radius: 6px;
  color: var(--text-secondary);
  cursor: pointer;
  transition: all 0.2s ease;
}

.language-btn:hover {
  background: #475569;
}

.language-btn.active {
  background: var(--primary);
  border-color: var(--primary);
  color: white;
}

/* ========================================
   Color Grid
   ======================================== */
.color-grid {
  display: grid;
  grid-template-columns: repeat(6, 1fr);
  gap: 0.375rem;
}

.color-btn {
  aspect-ratio: 1;
  border: 2px solid transparent;
  border-radius: 8px;
  cursor: pointer;
  transition: all 0.2s ease;
}

.color-btn:hover {
  transform: scale(1.1);
}

.color-btn.active {
  border-color: white;
  box-shadow: 0 0 0 2px var(--primary);
}

.color-input-group {
  display: flex;
  gap: 0.5rem;
}

.color-input-group input[type="color"] {
  width: 40px;
  height: 36px;
  padding: 0;
  border: none;
  border-radius: 6px;
  cursor: pointer;
}

.color-input-group input[type="text"] {
  flex: 1;
}

/* ========================================
   Export Options
   ======================================== */
.export-options {
  display: flex;
  flex-direction: column;
  gap: 0.5rem;
}

.radio-option {
  display: flex;
  align-items: flex-start;
  gap: 0.75rem;
  padding: 0.75rem;
  background: var(--bg-surface-light);
  border-radius: 8px;
  cursor: pointer;
  transition: background 0.2s ease;
}

.radio-option:hover {
  background: #475569;
}

.radio-option input[type="radio"] {
  margin-top: 2px;
}

.radio-option span {
  font-size: 0.875rem;
  font-weight: 500;
}

.radio-option small {
  display: block;
  font-size: 0.75rem;
  color: var(--text-secondary);
}

/* ========================================
   Notification
   ======================================== */
.notification {
  position: fixed;
  bottom: 24px;
  left: 50%;
  transform: translateX(-50%);
  padding: 0.75rem 1.5rem;
  font-size: 0.875rem;
  border-radius: 8px;
  animation: slideUp 0.3s ease;
  z-index: 1000;
}

.notification.success {
  background: var(--success);
  color: white;
}

.notification.error {
  background: var(--error);
  color: white;
}

.notification.warning {
  background: var(--warning);
  color: black;
}

.notification.info {
  background: var(--info);
  color: white;
}

@keyframes slideUp {
  from {
    opacity: 0;
    transform: translateX(-50%) translateY(20px);
  }
  to {
    opacity: 1;
    transform: translateX(-50%) translateY(0);
  }
}

/* ========================================
   Utilidades
   ======================================== */
.mt-3 {
  margin-top: 0.75rem;
}

/* ========================================
   Responsive
   ======================================== */
@media (max-width: 1024px) {
  .sidebar {
    width: 240px;
  }
}

@media (max-width: 768px) {
  .editor-main {
    flex-direction: column;
  }

  .sidebar {
    width: 100%;
    border-right: none;
    border-bottom: 1px solid var(--border);
  }

  .sidebar-right {
    border-left: none;
    border-top: 1px solid var(--border);
  }

  .editor-canvas-container {
    min-height: 300px;
  }
}
</style>
