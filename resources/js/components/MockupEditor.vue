<template>
  <div class="mockup-editor">
    <!-- Header -->
    <header class="editor-header">
      <div class="header-left">
        <h1 class="logo">
          <span class="logo-icon">🎨</span>
          Idogui Screen
        </h1>
        
        <!-- Controles de Páginas -->
        <div v-if="pages && pages.length > 0" class="pages-controls">
          <button 
            class="btn-icon-text" 
            @click="showPagesModal = true"
            title="Ver todas las páginas"
          >
            <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
              <rect x="3" y="3" width="7" height="7" rx="1"/>
              <rect x="14" y="3" width="7" height="7" rx="1"/>
              <rect x="3" y="14" width="7" height="7" rx="1"/>
              <rect x="14" y="14" width="7" height="7" rx="1"/>
            </svg>
            <span>{{ pages?.length || 1 }}</span>
          </button>
          <button 
            class="btn-icon-text btn-add-page" 
            @click="addNewPage"
            title="Agregar nueva página"
          >
            <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
              <line x1="12" y1="5" x2="12" y2="19"/>
              <line x1="5" y1="12" x2="19" y2="12"/>
            </svg>
            <span>Nueva</span>
          </button>
        </div>
      </div>
      <div class="header-center">
        <div class="project-name-wrapper">
          <input 
            v-model="projectName" 
            type="text" 
            class="project-name-input-inline" 
            placeholder="Nombre del proyecto"
            @blur="projectName = projectName || 'Nuevo Proyecto'"
            @input="markAsChanged"
          />
          <svg class="edit-icon" xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
            <path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/>
            <path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/>
          </svg>
        </div>
        
        <!-- Indicador de guardado -->
        <div class="save-indicator">
          <span v-if="isSaving" class="saving">
            <svg class="spin" xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
              <path d="M21 12a9 9 0 1 1-6.219-8.56"/>
            </svg>
            Guardando...
          </span>
          <span v-else-if="hasUnsavedChanges" class="unsaved">
            <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
              <circle cx="12" cy="12" r="10"/>
            </svg>
            Cambios sin guardar
          </span>
          <span v-else-if="lastSavedAt" class="saved">
            <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
              <polyline points="20 6 9 17 4 12"/>
            </svg>
            Guardado
          </span>
        </div>
      </div>
      <div class="header-right">
        <div class="canvas-controls">
          <!-- Orientación -->
          <div class="control-dropdown">
            <button 
              class="btn-icon has-dropdown"
              :class="{ active: canvasOrientation === 'vertical' }"
              @click="toggleCustomOrientation"
            >
              <!-- Icono de rotación de pantalla unificado -->
              <svg xmlns="http://www.w3.org/2000/svg" height="20px" viewBox="0 -960 960 960" width="20px" fill="currentColor">
                <path d="M496-182 183-496q-11-11-17-25t-6-29q0-15 6-29t17-25l173-173q11-11 25-16.5t29-5.5q15 0 29 5.5t25 16.5l313 313q11 11 17 25t6 29q0 15-6 29t-17 25L604-182q-11 11-25 16.5t-29 5.5q-15 0-29-5.5T496-182Zm54-58 170-170-310-310-170 170 310 310ZM480 0q-99 0-186.5-37.5t-153-103Q75-206 37.5-293.5T0-480h80q0 71 24 136t66.5 117Q213-175 272-138.5T401-87L296-192l56-56L588-12q-26 6-53.5 9T480 0Zm400-480q0-71-24-136t-66.5-117Q747-785 688-821.5T559-873l105 105-56 56-236-236q26-6 53.5-9t54.5-3q99 0 186.5 37.5t153 103q65.5 65.5 103 153T960-480h-80Zm-400 0Zm-107-76q13 0 21.5-9t8.5-21q0-13-8.5-21.5T373-616q-12 0-21 8.5t-9 21.5q0 12 9 21t21 9Z"/>
              </svg>
            </button>
            <div class="tooltip">
              <span v-if="selectedDevice === 'custom'">{{ customWidth }}×{{ customHeight }} - Click para invertir dimensiones</span>
              <span v-else>{{ canvasOrientation === 'vertical' ? 'Vertical' : 'Horizontal' }} - Click para cambiar</span>
            </div>
          </div>

          <!-- Selector de dispositivo -->
          <div class="control-dropdown" @click="showDeviceMenu = !showDeviceMenu">
            <button class="btn-icon has-dropdown">
              <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <rect x="5" y="2" width="14" height="20" rx="2" ry="2"/>
                <line x1="12" y1="18" x2="12.01" y2="18"/>
              </svg>
              <svg class="dropdown-arrow" xmlns="http://www.w3.org/2000/svg" width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <polyline points="6 9 12 15 18 9"/>
              </svg>
            </button>
            <div class="tooltip">{{ devicePresets[selectedDevice]?.name || 'Personalizado' }}</div>
            
            <!-- Menú desplegable -->
            <div v-if="showDeviceMenu" class="dropdown-menu" @click.stop>
              <div class="dropdown-section">
                <div class="dropdown-label">Móviles</div>
                <button @click="selectDeviceFromMenu('iphone-14-pro')" :class="{ active: selectedDevice === 'iphone-14-pro' }">
                  <span class="device-name">iPhone 14 Pro</span>
                  <span v-if="hasDevicePages(`iphone-14-pro-${canvasOrientation}`)" class="device-indicator">
                    <svg class="check-icon" xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3">
                      <polyline points="20 6 9 17 4 12"/>
                    </svg>
                    <span class="page-count">{{ getDevicePageCount(`iphone-14-pro-${canvasOrientation}`) }}</span>
                  </span>
                </button>
                <button @click="selectDeviceFromMenu('iphone-se')" :class="{ active: selectedDevice === 'iphone-se' }">
                  <span class="device-name">iPhone SE</span>
                  <span v-if="hasDevicePages(`iphone-se-${canvasOrientation}`)" class="device-indicator">
                    <svg class="check-icon" xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3">
                      <polyline points="20 6 9 17 4 12"/>
                    </svg>
                    <span class="page-count">{{ getDevicePageCount(`iphone-se-${canvasOrientation}`) }}</span>
                  </span>
                </button>
                <button @click="selectDeviceFromMenu('samsung-s23')" :class="{ active: selectedDevice === 'samsung-s23' }">
                  <span class="device-name">Samsung S23</span>
                  <span v-if="hasDevicePages(`samsung-s23-${canvasOrientation}`)" class="device-indicator">
                    <svg class="check-icon" xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3">
                      <polyline points="20 6 9 17 4 12"/>
                    </svg>
                    <span class="page-count">{{ getDevicePageCount(`samsung-s23-${canvasOrientation}`) }}</span>
                  </span>
                </button>
                <button @click="selectDeviceFromMenu('pixel-7')" :class="{ active: selectedDevice === 'pixel-7' }">
                  <span class="device-name">Google Pixel 7</span>
                  <span v-if="hasDevicePages(`pixel-7-${canvasOrientation}`)" class="device-indicator">
                    <svg class="check-icon" xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3">
                      <polyline points="20 6 9 17 4 12"/>
                    </svg>
                    <span class="page-count">{{ getDevicePageCount(`pixel-7-${canvasOrientation}`) }}</span>
                  </span>
                </button>
              </div>
              
              <div class="dropdown-section">
                <div class="dropdown-label">Tablets</div>
                <button @click="selectDeviceFromMenu('ipad-pro-11')" :class="{ active: selectedDevice === 'ipad-pro-11' }">
                  <span class="device-name">iPad Pro 11"</span>
                  <span v-if="hasDevicePages(`ipad-pro-11-${canvasOrientation}`)" class="device-indicator">
                    <svg class="check-icon" xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3">
                      <polyline points="20 6 9 17 4 12"/>
                    </svg>
                    <span class="page-count">{{ getDevicePageCount(`ipad-pro-11-${canvasOrientation}`) }}</span>
                  </span>
                </button>
                <button @click="selectDeviceFromMenu('ipad-air')" :class="{ active: selectedDevice === 'ipad-air' }">
                  <span class="device-name">iPad Air</span>
                  <span v-if="hasDevicePages(`ipad-air-${canvasOrientation}`)" class="device-indicator">
                    <svg class="check-icon" xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3">
                      <polyline points="20 6 9 17 4 12"/>
                    </svg>
                    <span class="page-count">{{ getDevicePageCount(`ipad-air-${canvasOrientation}`) }}</span>
                  </span>
                </button>
                <button @click="selectDeviceFromMenu('tablet-android')" :class="{ active: selectedDevice === 'tablet-android' }">
                  <span class="device-name">Tablet Android</span>
                  <span v-if="hasDevicePages(`tablet-android-${canvasOrientation}`)" class="device-indicator">
                    <svg class="check-icon" xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3">
                      <polyline points="20 6 9 17 4 12"/>
                    </svg>
                    <span class="page-count">{{ getDevicePageCount(`tablet-android-${canvasOrientation}`) }}</span>
                  </span>
                </button>
              </div>
              
              <div class="dropdown-section">
                <div class="dropdown-label">Desktop & Laptop</div>
                <button @click="selectDeviceFromMenu('desktop-fhd')" :class="{ active: selectedDevice === 'desktop-fhd' }">
                  <span class="device-name">Full HD (1920×1080)</span>
                  <span v-if="hasDevicePages(`desktop-fhd-${canvasOrientation}`)" class="device-indicator">
                    <svg class="check-icon" xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3">
                      <polyline points="20 6 9 17 4 12"/>
                    </svg>
                    <span class="page-count">{{ getDevicePageCount(`desktop-fhd-${canvasOrientation}`) }}</span>
                  </span>
                </button>
                <button @click="selectDeviceFromMenu('desktop-2k')" :class="{ active: selectedDevice === 'desktop-2k' }">
                  <span class="device-name">2K (2560×1440)</span>
                  <span v-if="hasDevicePages(`desktop-2k-${canvasOrientation}`)" class="device-indicator">
                    <svg class="check-icon" xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3">
                      <polyline points="20 6 9 17 4 12"/>
                    </svg>
                    <span class="page-count">{{ getDevicePageCount(`desktop-2k-${canvasOrientation}`) }}</span>
                  </span>
                </button>
                <button @click="selectDeviceFromMenu('desktop-4k')" :class="{ active: selectedDevice === 'desktop-4k' }">
                  <span class="device-name">4K (3840×2160)</span>
                  <span v-if="hasDevicePages(`desktop-4k-${canvasOrientation}`)" class="device-indicator">
                    <svg class="check-icon" xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3">
                      <polyline points="20 6 9 17 4 12"/>
                    </svg>
                    <span class="page-count">{{ getDevicePageCount(`desktop-4k-${canvasOrientation}`) }}</span>
                  </span>
                </button>
                <button @click="selectDeviceFromMenu('macbook-pro-14')" :class="{ active: selectedDevice === 'macbook-pro-14' }">
                  <span class="device-name">MacBook Pro 14"</span>
                  <span v-if="hasDevicePages(`macbook-pro-14-${canvasOrientation}`)" class="device-indicator">
                    <svg class="check-icon" xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3">
                      <polyline points="20 6 9 17 4 12"/>
                    </svg>
                    <span class="page-count">{{ getDevicePageCount(`macbook-pro-14-${canvasOrientation}`) }}</span>
                  </span>
                </button>
                <button @click="selectDeviceFromMenu('macbook-air-13')" :class="{ active: selectedDevice === 'macbook-air-13' }">
                  <span class="device-name">MacBook Air 13"</span>
                  <span v-if="hasDevicePages(`macbook-air-13-${canvasOrientation}`)" class="device-indicator">
                    <svg class="check-icon" xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3">
                      <polyline points="20 6 9 17 4 12"/>
                    </svg>
                    <span class="page-count">{{ getDevicePageCount(`macbook-air-13-${canvasOrientation}`) }}</span>
                  </span>
                </button>
              </div>
              
              <div class="dropdown-section">
                <div class="dropdown-label">Personalizado</div>
                <!-- Presets guardados -->
                <button 
                  v-for="preset in customPresets" 
                  :key="preset.key"
                  @click="selectCustomPreset(preset)" 
                  :class="{ active: selectedDevice === 'custom' && customWidth === preset.width && customHeight === preset.height }"
                >
                  <span class="device-name">{{ preset.key }}</span>
                  <span v-if="hasDevicePages(`custom-${preset.width}x${preset.height}`)" class="device-indicator">
                    <svg class="check-icon" xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3">
                      <polyline points="20 6 9 17 4 12"/>
                    </svg>
                    <span class="page-count">{{ getDevicePageCount(`custom-${preset.width}x${preset.height}`) }}</span>
                  </span>
                </button>
                <!-- Botón para crear nuevo -->
                <button @click="selectDeviceFromMenu('custom')" class="btn-create-custom">
                  <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <line x1="12" y1="5" x2="12" y2="19"/>
                    <line x1="5" y1="12" x2="19" y2="12"/>
                  </svg>
                  Crear Nueva Dimensión
                </button>
              </div>
            </div>
          </div>
          <button 
            class="btn-icon"
            :class="{ active: canvasMode === 'template' }"
            @click="openTemplateModal"
            title="Usar Template"
          >
            <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
              <rect x="3" y="3" width="7" height="7"/>
              <rect x="14" y="3" width="7" height="7"/>
              <rect x="14" y="14" width="7" height="7"/>
              <rect x="3" y="14" width="7" height="7"/>
            </svg>
          </button>
        </div>
        <button class="btn btn-secondary" @click="showSavedDesignsModal = true">
          <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
            <path d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"/>
            <polyline points="9 22 9 12 15 12 15 22"/>
          </svg>
          Mis Diseños
        </button>
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
        <!-- Sección Galería de Imágenes -->
        <div class="sidebar-section">
          <div class="section-header">
            <h2>Galería</h2>
            <button class="btn-icon" @click="handleUpload" title="Subir imágenes">
              <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/>
                <polyline points="17 8 12 3 7 8"/>
                <line x1="12" y1="3" x2="12" y2="15"/>
              </svg>
            </button>
          </div>

          <div class="gallery-grid">
            <div 
              v-for="image in galleryImages" 
              :key="image.id"
              class="gallery-item"
              @click="insertImageToCanvas(image)"
              :title="`Click para insertar en el lienzo`"
            >
              <img :src="image.url" :alt="image.filename">
              <div class="gallery-item-overlay">
                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                  <line x1="12" y1="5" x2="12" y2="19"></line>
                  <line x1="5" y1="12" x2="19" y2="12"></line>
                </svg>
              </div>
              <button 
                class="gallery-delete" 
                @click.stop="removeImage(image)"
                title="Eliminar"
              >
                <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                  <line x1="18" y1="6" x2="6" y2="18"></line>
                  <line x1="6" y1="6" x2="18" y2="18"></line>
                </svg>
              </button>
            </div>

            <!-- Empty state -->
            <div v-if="galleryImages.length === 0" class="empty-state">
              <svg xmlns="http://www.w3.org/2000/svg" width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
                <rect x="3" y="3" width="18" height="18" rx="2" ry="2"/>
                <circle cx="8.5" cy="8.5" r="1.5"/>
                <polyline points="21 15 16 10 5 21"/>
              </svg>
              <p>Sube imágenes a tu galería</p>
              <button class="btn btn-primary btn-sm" @click="handleUpload">
                Subir imágenes
              </button>
            </div>
          </div>
        </div>

        <!-- Sección Mockups (Imágenes Prediseñadas Compartidas) -->
        <div class="sidebar-section">
          <div class="section-header">
            <h2>Mockups</h2>
            <select v-model="mockupCategoryFilter" class="select-sm" @change="loadMockups">
              <option value="">Todas las categorías</option>
              <option v-for="category in mockupCategories" :key="category" :value="category">
                {{ category }}
              </option>
            </select>
          </div>

          <div class="gallery-grid">
            <div 
              v-for="mockup in mockups" 
              :key="mockup.id"
              class="gallery-item"
              @click="insertMockupToCanvas(mockup)"
              :title="`Click para insertar: ${mockup.name}`"
            >
              <img :src="mockup.thumbnail_url" :alt="mockup.name">
              <div class="gallery-item-overlay">
                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                  <line x1="12" y1="5" x2="12" y2="19"></line>
                  <line x1="5" y1="12" x2="19" y2="12"></line>
                </svg>
              </div>
              <div class="mockup-name">{{ mockup.name }}</div>
            </div>

            <!-- Loading state -->
            <div v-if="loadingMockups" class="empty-state">
              <p>Cargando mockups...</p>
            </div>

            <!-- Empty state -->
            <div v-else-if="mockups.length === 0" class="empty-state">
              <svg xmlns="http://www.w3.org/2000/svg" width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
                <rect x="3" y="3" width="18" height="18" rx="2" ry="2"/>
                <circle cx="8.5" cy="8.5" r="1.5"/>
                <polyline points="21 15 16 10 5 21"/>
              </svg>
              <p>No hay mockups disponibles</p>
            </div>
          </div>
        </div>

        <!-- Sección Templates (solo visible en modo template) -->
        <div class="sidebar-section" v-if="canvasMode === 'template'">
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
              :data-orientation="template.orientation"
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
          :canvas-images="canvasImages"
          :template="canvasMode === 'template' ? selectedTemplate : blankCanvasConfig"
          :texts="textItems"
          :background-color="backgroundColor"
          @rendered="handleRendered"
          @error="handleRenderError"
        />
      </div>

      <!-- Sidebar Derecha: Opciones -->
      <aside class="sidebar sidebar-right">
        <!-- Textos Dinámicos -->
        <div class="sidebar-section">
          <div class="section-header">
            <h2>Textos</h2>
            <button 
              class="btn-icon" 
              @click="addNewText" 
              title="Agregar texto"
            >
              <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <line x1="12" y1="5" x2="12" y2="19"/>
                <line x1="5" y1="12" x2="19" y2="12"/>
              </svg>
            </button>
          </div>

          <!-- Lista de textos -->
          <div v-if="textItems.length === 0" class="empty-state-small">
            <p style="font-size: 0.875rem; color: var(--text-secondary); text-align: center; padding: 1rem 0;">
              No hay textos. Click en + para agregar.
            </p>
          </div>

          <div v-else class="text-items-list">
            <div 
              v-for="(textItem, index) in textItems" 
              :key="textItem.id"
              class="text-item-card"
              :class="{ active: selectedTextIndex === index }"
              @click="selectedTextIndex = index"
            >
              <div class="text-item-header">
                <div class="text-item-number">{{ index + 1 }}</div>
                <input 
                  type="text" 
                  v-model="textItem.content" 
                  @click.stop
                  placeholder="Escribe tu texto..."
                  class="text-item-input"
                  maxlength="200"
                >
                <button 
                  class="btn-delete-text" 
                  @click.stop="removeText(index)"
                  title="Eliminar"
                >
                  <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <line x1="18" y1="6" x2="6" y2="18"/>
                    <line x1="6" y1="6" x2="18" y2="18"/>
                  </svg>
                </button>
              </div>

              <!-- Controles del texto seleccionado -->
              <div v-if="selectedTextIndex === index" class="text-item-controls">
                <div class="form-group-inline">
                  <label>Tamaño</label>
                  <input 
                    type="number" 
                    v-model.number="textItem.fontSize" 
                    @click.stop
                    min="8" 
                    max="200"
                    class="input-small"
                  >
                </div>

                <div class="form-group-inline">
                  <label>Fuente</label>
                  <select v-model="textItem.fontFamily" @click.stop class="select-small">
                    <option value="Arial">Arial</option>
                    <option value="Helvetica">Helvetica</option>
                    <option value="Times New Roman">Times New Roman</option>
                    <option value="Georgia">Georgia</option>
                    <option value="Verdana">Verdana</option>
                    <option value="Courier New">Courier New</option>
                    <option value="Comic Sans MS">Comic Sans MS</option>
                    <option value="Impact">Impact</option>
                    <option value="Trebuchet MS">Trebuchet MS</option>
                  </select>
                </div>

                <div class="form-group-inline">
                  <label>Color</label>
                  <input 
                    type="color" 
                    v-model="textItem.color" 
                    @click.stop
                    class="input-color-small"
                  >
                </div>

                <div class="form-group-inline">
                  <label>Estilo</label>
                  <div class="text-style-buttons">
                    <button 
                      @click.stop="textItem.fontWeight = textItem.fontWeight === 'bold' ? 'normal' : 'bold'"
                      :class="{ active: textItem.fontWeight === 'bold' }"
                      class="btn-style"
                      title="Negrita"
                    >
                      <strong>B</strong>
                    </button>
                    <button 
                      @click.stop="textItem.fontStyle = textItem.fontStyle === 'italic' ? 'normal' : 'italic'"
                      :class="{ active: textItem.fontStyle === 'italic' }"
                      class="btn-style"
                      title="Cursiva"
                    >
                      <em>I</em>
                    </button>
                  </div>
                </div>
              </div>
            </div>
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

    <!-- Modal de Templates -->
    <div v-if="showTemplateModal" class="modal-overlay" @click="closeTemplateModal">
      <div class="modal-content" @click.stop>
        <div class="modal-header">
          <h2>Seleccionar Template</h2>
          <button class="btn-icon" @click="closeTemplateModal">
            <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
              <line x1="18" y1="6" x2="6" y2="18"/>
              <line x1="6" y1="6" x2="18" y2="18"/>
            </svg>
          </button>
        </div>
        <div class="modal-body">
          <p class="text-center text-secondary">Próximamente...</p>
        </div>
      </div>
    </div>

    <!-- Modal de Diseños Guardados -->
    <div v-if="showSavedDesignsModal" class="modal-overlay" @click="showSavedDesignsModal = false">
      <div class="modal-content modal-large" @click.stop>
        <div class="modal-header">
          <h2>Mis Diseños</h2>
          <button class="btn-icon" @click="showSavedDesignsModal = false">
            <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
              <line x1="18" y1="6" x2="6" y2="18"/>
              <line x1="6" y1="6" x2="18" y2="18"/>
            </svg>
          </button>
        </div>
        <div class="modal-body">
          <div v-if="loadingDesigns" class="text-center py-8">
            <div class="loading-spinner"></div>
            <p>Cargando diseños...</p>
          </div>

          <div v-else-if="savedDesigns.length === 0" class="text-center py-8 text-secondary">
            <p>No tienes diseños guardados aún.</p>
            <p class="text-sm mt-2">Crea tu primer diseño y guárdalo para acceder desde aquí.</p>
          </div>

          <div v-else class="designs-grid">
            <div 
              v-for="design in savedDesigns" 
              :key="design.id"
              class="design-card"
              @click="loadDesign(design.id)"
            >
              <div class="design-thumbnail">
                <img 
                  v-if="design.thumbnail_path" 
                  :src="`/storage/${design.thumbnail_path}`" 
                  :alt="design.name"
                >
                <div v-else class="design-placeholder">
                  <svg xmlns="http://www.w3.org/2000/svg" width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1">
                    <rect x="3" y="3" width="18" height="18" rx="2"/>
                    <circle cx="8.5" cy="8.5" r="1.5"/>
                    <path d="M21 15l-5-5L5 21"/>
                  </svg>
                </div>
              </div>
              <div class="design-info">
                <h3>{{ design.name }}</h3>
                <p class="design-meta">
                  <span class="design-type">{{ getCanvasTypeLabel(design.canvas_type) }}</span>
                  <span class="design-date">{{ formatDate(design.last_edited_at) }}</span>
                </p>
              </div>
              <div class="design-actions" @click.stop>
                <button 
                  class="btn-icon btn-sm" 
                  @click="duplicateDesign(design.id)"
                  title="Duplicar"
                >
                  <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <rect x="9" y="9" width="13" height="13" rx="2" ry="2"/>
                    <path d="M5 15H4a2 2 0 0 1-2-2V4a2 2 0 0 1 2-2h9a2 2 0 0 1 2 2v1"/>
                  </svg>
                </button>
                <button 
                  class="btn-icon btn-sm btn-danger" 
                  @click="deleteDesign(design.id)"
                  title="Eliminar"
                >
                  <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <polyline points="3 6 5 6 21 6"/>
                    <path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"/>
                  </svg>
                </button>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Modal de Páginas -->
    <div v-if="showPagesModal && pages && pages.length > 0" class="modal-overlay" @click="showPagesModal = false">
      <div class="modal-content" @click.stop>
        <div class="modal-header">
          <h2>Páginas del Proyecto</h2>
          <button class="btn-icon" @click="showPagesModal = false">
            <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
              <line x1="18" y1="6" x2="6" y2="18"/>
              <line x1="6" y1="6" x2="18" y2="18"/>
            </svg>
          </button>
        </div>
        <div class="modal-body">
          <div class="pages-header">
            <p class="text-secondary">{{ pages?.length || 0 }} {{ (pages?.length || 0) === 1 ? 'página' : 'páginas' }}</p>
            <button class="btn btn-primary btn-sm" @click="addNewPage">
              <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <line x1="12" y1="5" x2="12" y2="19"/>
                <line x1="5" y1="12" x2="19" y2="12"/>
              </svg>
              Nueva Página
            </button>
          </div>

          <div class="pages-grid">
            <div 
              v-for="(page, index) in pages" 
              :key="page.id"
              class="page-card"
              :class="{ active: index === currentPageIndex }"
              @click="switchToPage(index); showPagesModal = false"
            >
              <div class="page-thumbnail">
                <div class="page-number">{{ index + 1 }}</div>
                <img 
                  v-if="page.thumbnail" 
                  :src="page.thumbnail" 
                  :alt="page.name"
                >
                <div v-else class="page-placeholder">
                  <svg xmlns="http://www.w3.org/2000/svg" width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1">
                    <rect x="4" y="4" width="16" height="16" rx="2"/>
                  </svg>
                </div>
              </div>
              <div class="page-info">
                <h4>{{ page.name }}</h4>
                <p class="page-type">{{ page.canvas_type === 'vertical' ? 'Vertical' : 'Horizontal' }}</p>
              </div>
              <div class="page-actions" @click.stop>
                <button 
                  class="btn-icon btn-sm" 
                  @click="renamePage(index)"
                  title="Renombrar"
                >
                  <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/>
                    <path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/>
                  </svg>
                </button>
                <button 
                  class="btn-icon btn-sm" 
                  @click="duplicatePage(index)"
                  title="Duplicar"
                >
                  <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <rect x="9" y="9" width="13" height="13" rx="2" ry="2"/>
                    <path d="M5 15H4a2 2 0 0 1-2-2V4a2 2 0 0 1 2-2h9a2 2 0 0 1 2 2v1"/>
                  </svg>
                </button>
                <button 
                  class="btn-icon btn-sm btn-danger" 
                  @click="deletePage(index)"
                  title="Eliminar"
                  :disabled="(pages?.length || 1) <= 1"
                >
                  <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <polyline points="3 6 5 6 21 6"/>
                    <path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"/>
                  </svg>
                </button>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Modal de notificación -->
    <div v-if="notification.show" class="notification" :class="notification.type">
      {{ notification.message }}
    </div>

    <!-- Modal para nombre del proyecto -->
    <div v-if="showProjectNameModal" class="modal-overlay" @click="cancelProjectName">
      <div class="modal-container" @click.stop>
        <div class="modal-header">
          <h3>Nuevo Proyecto</h3>
          <button class="btn-icon" @click="cancelProjectName">
            <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
              <line x1="18" y1="6" x2="6" y2="18"/>
              <line x1="6" y1="6" x2="18" y2="18"/>
            </svg>
          </button>
        </div>
        <div class="modal-body">
          <div class="form-group">
            <label for="project-name">Nombre del proyecto</label>
            <input 
              id="project-name"
              type="text" 
              v-model="tempProjectName"
              @keyup.enter="confirmProjectName"
              placeholder="Ej: Captura App Store"
              class="input-text"
              autofocus
            >
            <small class="form-hint">Si dejas vacío, se generará un nombre automático</small>
          </div>
        </div>
        <div class="modal-footer">
          <button class="btn btn-secondary" @click="cancelProjectName">
            Cancelar
          </button>
          <button class="btn btn-primary" @click="confirmProjectName">
            Crear Proyecto
          </button>
        </div>
      </div>
    </div>

    <!-- Modal para dimensiones personalizadas -->
    <div v-if="showCustomDimensionsModal" class="modal-overlay" @click="showCustomDimensionsModal = false">
      <div class="modal-container" @click.stop>
        <div class="modal-header">
          <h3>Dimensiones Personalizadas</h3>
          <button class="btn-icon" @click="showCustomDimensionsModal = false">
            <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
              <line x1="18" y1="6" x2="6" y2="18"/>
              <line x1="6" y1="6" x2="18" y2="18"/>
            </svg>
          </button>
        </div>
        <div class="modal-body">
          <p class="text-secondary" style="margin-bottom: 1rem;">
            Ingresa las dimensiones del lienzo en píxeles
          </p>
          <div class="custom-dimensions-form">
            <div class="form-group">
              <label for="custom-width">Ancho (px)</label>
              <input 
                id="custom-width"
                type="number" 
                v-model.number="tempCustomWidth"
                @keyup.enter="confirmCustomDimensions"
                placeholder="1200"
                class="input-text"
                min="100"
                max="10000"
                autofocus
              >
            </div>
            <div class="dimension-separator">×</div>
            <div class="form-group">
              <label for="custom-height">Alto (px)</label>
              <input 
                id="custom-height"
                type="number" 
                v-model.number="tempCustomHeight"
                @keyup.enter="confirmCustomDimensions"
                placeholder="2688"
                class="input-text"
                min="100"
                max="10000"
              >
            </div>
          </div>
          <small class="form-hint">Dimensión mínima: 100px</small>
        </div>
        <div class="modal-footer">
          <button class="btn btn-secondary" @click="showCustomDimensionsModal = false">
            Cancelar
          </button>
          <button class="btn btn-primary" @click="confirmCustomDimensions">
            Crear Lienzo
          </button>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
import { ref, computed, onMounted, onUnmounted, nextTick, watch } from 'vue';
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
    const canvasMode = ref('blank-vertical'); // 'blank-vertical', 'blank-horizontal', 'template'
    const canvasOrientation = ref('vertical'); // 'vertical' o 'horizontal'
    const selectedDevice = ref('iphone-14-pro'); // Dispositivo seleccionado
    const customWidth = ref(1200);
    const customHeight = ref(2688);
    const showDeviceMenu = ref(false);
    const blankCanvasConfig = ref(null);
    const showTemplateModal = ref(false);
    const showSavedDesignsModal = ref(false);
    const showPagesModal = ref(false);
    const showProjectNameModal = ref(false);
    const showCustomDimensionsModal = ref(false);
    const tempCustomWidth = ref(1200);
    const tempCustomHeight = ref(2688);
    const customPresets = ref([]); // Lista de dimensiones personalizadas guardadas
    const tempProjectName = ref('');
    const projectCounter = ref(1);
    const galleryImages = ref([]); // Galería de imágenes
    const canvasImages = ref([]); // Imágenes insertadas en el canvas
    const templates = ref([]);
    const selectedTemplate = ref(null);
    const templateFilter = ref('all');
    const savedDesigns = ref([]);
    const loadingDesigns = ref(false);
    const currentDesignId = ref(null); // ID del diseño actual si se está editando
    
    // Mockups (imágenes prediseñadas compartidas)
    const mockups = ref([]);
    const mockupCategories = ref([]);
    const mockupCategoryFilter = ref('');
    const loadingMockups = ref(false);
    
    // Nueva estructura: configuraciones por dispositivo
    const deviceConfigs = ref({});
    const currentDeviceKey = ref(null); // Dispositivo actualmente activo
    
    // Páginas del dispositivo actual (computed)
    const pages = computed({
      get: () => {
        if (!currentDeviceKey.value || !deviceConfigs.value[currentDeviceKey.value]) {
          return [];
        }
        return deviceConfigs.value[currentDeviceKey.value].pages || [];
      },
      set: (newPages) => {
        if (currentDeviceKey.value && deviceConfigs.value[currentDeviceKey.value]) {
          deviceConfigs.value[currentDeviceKey.value].pages = newPages;
        }
      }
    });
    
    const currentPageIndex = ref(0);
    
    // Sistema de textos dinámico
    const textItems = ref([]);
    const selectedTextIndex = ref(null);
    const textIdCounter = ref(1);

    const selectedLanguage = ref('es');
    const backgroundColor = ref('#6366f1');
    const exportFormat = ref('png');

    const notification = ref({
      show: false,
      type: 'success',
      message: '',
    });

    // Auto-guardado
    const hasUnsavedChanges = ref(false);
    const isSaving = ref(false);
    const lastSavedAt = ref(null);
    const autoSaveInterval = ref(null);
    const autoSaveDebounceTimer = ref(null);

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
      return window.Idogui?.user?.plan !== 'free';
    });

    const canUseAI = computed(() => {
      return window.Idogui?.user?.plan !== 'free';
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

    // Encriptación simple del ID (Base64 con salt)
    const encryptId = (id) => {
      const salt = 'idogui2025';
      const mixed = `${salt}${id}${salt}`;
      return btoa(mixed).replace(/[+/=]/g, (char) => {
        return { '+': '-', '/': '_', '=': '' }[char];
      });
    };

    const decryptId = (encrypted) => {
      try {
        const restored = encrypted.replace(/[-_]/g, (char) => {
          return { '-': '+', '_': '/' }[char];
        });
        const decoded = atob(restored);
        const salt = 'idogui2025';
        const idStr = decoded.replace(new RegExp(salt, 'g'), '');
        return parseInt(idStr);
      } catch (e) {
        console.error('Error desencriptando ID:', e);
        return null;
      }
    };

    const updateUrlWithDesignId = (designId) => {
      const encrypted = encryptId(designId);
      const newUrl = `${window.location.origin}/editor/${encrypted}`;
      window.history.pushState({ designId }, '', newUrl);
    };

    const getDesignIdFromUrl = () => {
      const pathParts = window.location.pathname.split('/');
      const encryptedId = pathParts[pathParts.length - 1];
      if (encryptedId && encryptedId !== 'editor') {
        return decryptId(encryptedId);
      }
      return null;
    };

    // Auto-guardado
    const markAsChanged = () => {
      hasUnsavedChanges.value = true;
      
      // Auto-guardar con debounce (espera 3 segundos después del último cambio)
      if (currentDesignId.value) {
        if (autoSaveDebounceTimer.value) {
          clearTimeout(autoSaveDebounceTimer.value);
        }
        
        autoSaveDebounceTimer.value = setTimeout(() => {
          autoSave();
        }, 3000); // Guardar 3 segundos después del último cambio
      }
    };

    const autoSave = async () => {
      if (!hasUnsavedChanges.value || isSaving.value || !currentDesignId.value) {
        return;
      }

      await handleSave(true); // true = auto-save silencioso
    };

    const startAutoSave = () => {
      // Limpiar intervalo anterior si existe
      if (autoSaveInterval.value) {
        clearInterval(autoSaveInterval.value);
      }

      // Auto-guardar cada 10 segundos como respaldo
      autoSaveInterval.value = setInterval(() => {
        autoSave();
      }, 10000);
    };

    const stopAutoSave = () => {
      if (autoSaveInterval.value) {
        clearInterval(autoSaveInterval.value);
        autoSaveInterval.value = null;
      }
    };

    // Presets de dispositivos
    const devicePresets = {
      'iphone-14-pro': { width: 1179, height: 2556, name: 'iPhone 14 Pro' },
      'iphone-se': { width: 750, height: 1334, name: 'iPhone SE' },
      'samsung-s23': { width: 1080, height: 2340, name: 'Samsung S23' },
      'pixel-7': { width: 1080, height: 2400, name: 'Google Pixel 7' },
      'ipad-pro-11': { width: 1668, height: 2388, name: 'iPad Pro 11"' },
      'ipad-air': { width: 1640, height: 2360, name: 'iPad Air' },
      'tablet-android': { width: 1600, height: 2560, name: 'Tablet Android' },
      'desktop-fhd': { width: 1920, height: 1080, name: 'Desktop Full HD' },
      'desktop-2k': { width: 2560, height: 1440, name: 'Desktop 2K' },
      'desktop-4k': { width: 3840, height: 2160, name: 'Desktop 4K' },
      'macbook-pro-14': { width: 3024, height: 1964, name: 'MacBook Pro 14"' },
      'macbook-air-13': { width: 2560, height: 1664, name: 'MacBook Air 13"' },
    };

    const setOrientation = (orientation) => {
      canvasOrientation.value = orientation;
      applyDevicePreset();
    };

    const applyDevicePreset = () => {
      if (selectedDevice.value === 'custom') {
        applyCustomSize();
        return;
      }

      const preset = devicePresets[selectedDevice.value];
      if (!preset) return;

      let width = preset.width;
      let height = preset.height;

      // Intercambiar dimensiones según orientación
      if (canvasOrientation.value === 'horizontal') {
        if (width < height) {
          [width, height] = [height, width];
        }
      } else {
        if (width > height) {
          [width, height] = [height, width];
        }
      }

      createCanvasWithDimensions(width, height);
    };

    const applyCustomSize = () => {
      if (customWidth.value && customHeight.value) {
        createCanvasWithDimensions(customWidth.value, customHeight.value);
      }
    };

    // Obtener clave única del dispositivo
    const getDeviceKey = () => {
      if (selectedDevice.value === 'custom') {
        return `custom-${customWidth.value}x${customHeight.value}`;
      }
      return `${selectedDevice.value}-${canvasOrientation.value}`;
    };

    // Verificar si un dispositivo tiene páginas creadas
    const hasDevicePages = (deviceKey) => {
      return deviceConfigs.value[deviceKey] && deviceConfigs.value[deviceKey].pages.length > 0;
    };

    // Obtener número de páginas de un dispositivo
    const getDevicePageCount = (deviceKey) => {
      if (!deviceConfigs.value[deviceKey]) return 0;
      return deviceConfigs.value[deviceKey].pages.length;
    };

    // Inicializar dispositivo si no existe
    const ensureDeviceExists = (deviceKey) => {
      if (!deviceConfigs.value[deviceKey]) {
        deviceConfigs.value[deviceKey] = {
          device: selectedDevice.value,
          orientation: canvasOrientation.value,
          width: customWidth.value,
          height: customHeight.value,
          pages: [{
            id: Date.now(),
            name: 'Página 1',
            order: 1,
            canvas_type: canvasOrientation.value,
            canvas_config: null,
            canvas_images: [],
            text_items: [],
            thumbnail: null,
          }]
        };
      }
    };

    const selectDeviceFromMenu = (device) => {
      selectedDevice.value = device;
      showDeviceMenu.value = false;
      
      // Si es custom, mostrar modal para pedir dimensiones
      if (device === 'custom') {
        tempCustomWidth.value = 1200;
        tempCustomHeight.value = 2688;
        showCustomDimensionsModal.value = true;
        return;
      }
      
      const newDeviceKey = getDeviceKey();
      
      // Si el dispositivo ya tiene páginas, cambiar a él
      if (hasDevicePages(newDeviceKey)) {
        switchToDevice(newDeviceKey);
      } else {
        // Si no existe, aplicar preset (creará el dispositivo)
        applyDevicePreset();
      }
    };

    const switchToDevice = (deviceKey) => {
      if (!deviceConfigs.value[deviceKey]) return;
      
      currentDeviceKey.value = deviceKey;
      const config = deviceConfigs.value[deviceKey];
      
      // Actualizar dispositivo seleccionado
      selectedDevice.value = config.device;
      canvasOrientation.value = config.orientation;
      customWidth.value = config.width;
      customHeight.value = config.height;
      
      // Cargar primera página
      currentPageIndex.value = 0;
      if (config.pages.length > 0) {
        const firstPage = config.pages[0];
        canvasImages.value = [...(firstPage.canvas_images || [])];
        textItems.value = firstPage.text_items || [];
        blankCanvasConfig.value = firstPage.canvas_config;
        
        // Sincronizar backgroundColor desde el config cargado
        if (firstPage.canvas_config?.layout_config?.canvas?.background) {
          backgroundColor.value = firstPage.canvas_config.layout_config.canvas.background;
        }
      }
    };

    const startNewProject = () => {
      tempProjectName.value = '';
      showProjectNameModal.value = true;
    };

    const confirmCustomDimensions = () => {
      if (!tempCustomWidth.value || !tempCustomHeight.value || 
          tempCustomWidth.value < 100 || tempCustomHeight.value < 100) {
        alert('Las dimensiones deben ser mayores a 100px');
        return;
      }
      
      customWidth.value = tempCustomWidth.value;
      customHeight.value = tempCustomHeight.value;
      
      // Agregar a presets si no existe
      const presetKey = `${tempCustomWidth.value}×${tempCustomHeight.value}`;
      if (!customPresets.value.some(p => p.key === presetKey)) {
        customPresets.value.push({
          key: presetKey,
          width: tempCustomWidth.value,
          height: tempCustomHeight.value
        });
      }
      
      showCustomDimensionsModal.value = false;
      
      const newDeviceKey = getDeviceKey();
      
      // Si el dispositivo ya tiene páginas, cambiar a él
      if (hasDevicePages(newDeviceKey)) {
        switchToDevice(newDeviceKey);
      } else {
        // Si no existe, aplicar preset (creará el dispositivo)
        applyDevicePreset();
      }
    };

    const selectCustomPreset = (preset) => {
      customWidth.value = preset.width;
      customHeight.value = preset.height;
      showDeviceMenu.value = false;
      
      const newDeviceKey = getDeviceKey();
      
      // Si el dispositivo ya tiene páginas, cambiar a él
      if (hasDevicePages(newDeviceKey)) {
        switchToDevice(newDeviceKey);
      } else {
        // Si no existe, aplicar preset (creará el dispositivo)
        applyDevicePreset();
      }
    };

    const toggleCustomOrientation = () => {
      if (selectedDevice.value === 'custom') {
        // Intercambiar dimensiones para custom
        const temp = customWidth.value;
        customWidth.value = customHeight.value;
        customHeight.value = temp;
        
        const newDeviceKey = getDeviceKey();
        
        // Si el dispositivo ya tiene páginas, cambiar a él
        if (hasDevicePages(newDeviceKey)) {
          switchToDevice(newDeviceKey);
        } else {
          // Si no existe, aplicar preset (creará el dispositivo)
          applyDevicePreset();
        }
      } else {
        // Comportamiento normal para otros dispositivos
        canvasOrientation.value = canvasOrientation.value === 'vertical' ? 'horizontal' : 'vertical';
        const newDeviceKey = getDeviceKey();
        
        if (hasDevicePages(newDeviceKey)) {
          switchToDevice(newDeviceKey);
        } else {
          applyDevicePreset();
        }
      }
    };

    const confirmProjectName = async () => {
      // Si no hay nombre, generar uno automático
      if (!tempProjectName.value.trim()) {
        projectName.value = `Mi Proyecto ${projectCounter.value}`;
        projectCounter.value++;
      } else {
        projectName.value = tempProjectName.value.trim();
      }

      showProjectNameModal.value = false;

      // Crear canvas con dispositivo seleccionado
      applyDevicePreset();

      // Guardar inmediatamente
      await handleSave(false);
      
      // Iniciar auto-guardado
      if (!autoSaveInterval.value) {
        startAutoSave();
      }
    };

    const cancelProjectName = () => {
      showProjectNameModal.value = false;
      tempProjectName.value = '';
    };

    const createCanvasWithDimensions = (width, height) => {
      canvasMode.value = `blank-${canvasOrientation.value}`;
      
      const deviceKey = getDeviceKey();
      
      const newConfig = {
        id: `blank-${Date.now()}`,
        name: `Canvas ${width}x${height}`,
        type: 'blank',
        orientation: canvasOrientation.value,
        is_premium: false,
        layout_config: {
          canvas: {
            width: width,
            height: height,
            background: backgroundColor.value
          },
          screenshot: {
            x: 100,
            y: 100,
            width: Math.min(width - 200, 1000),
            height: Math.min(height - 400, 1800),
            shadow: { enabled: false }
          },
          texts: {
            headline: { x: 100, y: 80, size: 48, color: '#000000', weight: 'bold' },
            subheadline: { x: 100, y: 140, size: 24, color: 'rgba(0,0,0,0.7)' },
            cta: { x: 100, y: height - 100, size: 20, color: '#000000' }
          }
        }
      };

      blankCanvasConfig.value = newConfig;

      // Crear o actualizar dispositivo
      if (!deviceConfigs.value[deviceKey]) {
        deviceConfigs.value[deviceKey] = {
          device: selectedDevice.value,
          orientation: canvasOrientation.value,
          width: width,
          height: height,
          pages: [{
            id: Date.now(),
            name: 'Página 1',
            order: 1,
            canvas_type: canvasOrientation.value,
            canvas_config: newConfig,
            canvas_images: [],
            texts: {},
            thumbnail: null,
          }]
        };
      }

      currentDeviceKey.value = deviceKey;
      currentPageIndex.value = 0;
      
      // Limpiar canvas e imágenes y textos
      canvasImages.value = [];
      textItems.value = [];
      selectedTextIndex.value = null;
      
      markAsChanged();
    };

    const createBlankCanvas = (orientation) => {
      canvasMode.value = `blank-${orientation}`;
      
      // Configuración de lienzo en blanco
      const isVertical = orientation === 'vertical';
      
      // Crear nuevo config directamente con ID único para forzar reactividad
      blankCanvasConfig.value = {
        id: `blank-${orientation}-${Date.now()}`,
        name: `Lienzo ${isVertical ? 'Vertical' : 'Horizontal'}`,
        type: 'blank',
        orientation: orientation,
        is_premium: false,
        layout_config: {
          canvas: {
            width: isVertical ? 1200 : 1920,
            height: isVertical ? 2688 : 1080,
            background: backgroundColor.value
          },
          screenshot: {
            x: 100,
            y: 100,
            width: isVertical ? 1000 : 800,
            height: isVertical ? 1800 : 700,
            shadow: { enabled: false }
          },
          texts: {
            headline: { x: 100, y: 80, size: 48, color: '#000000', weight: 'bold' },
            subheadline: { x: 100, y: 140, size: 24, color: 'rgba(0,0,0,0.7)' },
            cta: { x: 100, y: isVertical ? 2550 : 980, size: 20, color: '#000000' }
          }
        }
      };

      showNotification(`Lienzo ${orientation} creado`);
    };

    const openTemplateModal = () => {
      showTemplateModal.value = true;
    };

    const closeTemplateModal = () => {
      showTemplateModal.value = false;
    };

    // Funciones para textos dinámicos
    const addNewText = () => {
      const newText = {
        id: textIdCounter.value++,
        content: '',
        fontSize: 24,
        fontFamily: 'Arial',
        color: '#000000',
        fontWeight: 'normal',
        fontStyle: 'normal',
        rotation: 0,
        x: undefined,
        y: undefined,
      };
      textItems.value.push(newText);
      selectedTextIndex.value = textItems.value.length - 1;
      markAsChanged();
    };

    const removeText = (index) => {
      textItems.value.splice(index, 1);
      if (selectedTextIndex.value >= textItems.value.length) {
        selectedTextIndex.value = textItems.value.length - 1;
      }
      if (selectedTextIndex.value < 0) {
        selectedTextIndex.value = null;
      }
      markAsChanged();
    };

    const insertImageToCanvas = (image) => {
      // Insertar imagen en el canvas en el centro
      const newImage = {
        id: Date.now(),
        filename: image.filename, // Copiar filename
        url: image.url,
        x: 100,
        y: 100,
        width: 400,
        height: 400,
        rotation: 0,
        zIndex: canvasImages.value.length
      };
      
      canvasImages.value.push(newImage);
      showNotification('Imagen insertada en el lienzo');
    };

    const insertMockupToCanvas = async (mockup) => {
      try {
        // Incrementar contador de uso en el backend
        await axios.post(`/api/mockups/${mockup.id}/usage`);
        
        // Insertar mockup en el canvas
        const newImage = {
          id: Date.now(),
          filename: mockup.filename,
          url: mockup.url, // URL pública del mockup
          x: 100,
          y: 100,
          width: mockup.width || 400,
          height: mockup.height || 400,
          rotation: 0,
          zIndex: canvasImages.value.length
        };
        
        canvasImages.value.push(newImage);
        showNotification(`Mockup "${mockup.name}" insertado en el lienzo`);
      } catch (error) {
        console.error('Error al insertar mockup:', error);
        showNotification('Error al insertar mockup', 'error');
      }
    };

    const loadMockups = async () => {
      try {
        loadingMockups.value = true;
        
        const params = {};
        if (mockupCategoryFilter.value) {
          params.category = mockupCategoryFilter.value;
        }
        
        const response = await axios.get('/api/mockups', { params });
        mockups.value = response.data;
        
        loadingMockups.value = false;
      } catch (error) {
        console.error('Error al cargar mockups:', error);
        showNotification('Error al cargar mockups', 'error');
        loadingMockups.value = false;
      }
    };

    const loadMockupCategories = async () => {
      try {
        const response = await axios.get('/api/mockups/categories');
        mockupCategories.value = response.data;
      } catch (error) {
        console.error('Error al cargar categorías:', error);
      }
    };

    const removeImage = (image) => {
      // Liberar URL de blob
      if (image.url && image.url.startsWith('blob:')) {
        URL.revokeObjectURL(image.url);
      }
      
      // Eliminar del array de galería
      const index = galleryImages.value.findIndex(img => img.id === image.id);
      if (index > -1) {
        galleryImages.value.splice(index, 1);
      }
      
      showNotification('Imagen eliminada de la galería');
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

      // Verificar que tengamos un diseño guardado
      if (!currentDesignId.value) {
        showNotification('Debes guardar el proyecto primero', 'error');
        event.target.value = '';
        return;
      }

      let loadedCount = 0;

      // Subir cada imagen a la API
      for (const file of files) {
        try {
          const formData = new FormData();
          formData.append('image', file);

          const response = await axios.post(
            `/api/designs/${currentDesignId.value}/images`,
            formData,
            {
              headers: {
                'Content-Type': 'multipart/form-data'
              }
            }
          );

          // Agregar a la galería con la URL protegida
          const img = new Image();
          img.src = response.data.url;
          
          await new Promise((resolve) => {
            img.onload = () => {
              const image = {
                id: Date.now() + Math.random(),
                filename: response.data.filename,
                url: response.data.url,
                width: img.width,
                height: img.height,
              };
              galleryImages.value.push(image);
              loadedCount++;
              resolve();
            };
            img.onerror = () => resolve();
          });
        } catch (error) {
          console.error('Error subiendo imagen:', error);
        }
      }

      showNotification(`${loadedCount} imagen(es) agregada(s) a la galería`);
      event.target.value = '';
      
      // Marcar como cambios sin guardar para incluir galería en próximo guardado
      markAsChanged();
    };

    // Gestión de páginas
    const addNewPage = async () => {
      const newPageNumber = pages.value.length + 1;
      const newPage = {
        id: Date.now(),
        name: `Página ${newPageNumber}`,
        order: newPageNumber,
        canvas_type: 'vertical',
        canvas_config: {
          canvas: { width: 1200, height: 2688, background: '#ffffff' }
        },
        canvas_images: [],
        text_items: [],
        thumbnail: null,
      };
      pages.value.push(newPage);
      switchToPage(pages.value.length - 1);
      showNotification(`Página ${newPageNumber} creada`);
      
      // Auto-guardar al agregar página
      if (currentDesignId.value) {
        await autoSave();
      }
    };

    const switchToPage = (index) => {
      // Guardar estado de página actual
      const currentPage = pages.value[currentPageIndex.value];
      if (currentPage) {
        currentPage.canvas_images = [...canvasImages.value];
        currentPage.text_items = [...textItems.value];
        currentPage.canvas_config = blankCanvasConfig.value;
        currentPage.canvas_type = canvasMode.value.replace('blank-', '');
      }

      // Cargar nueva página
      currentPageIndex.value = index;
      const newPage = pages.value[index];
      canvasImages.value = [...(newPage.canvas_images || [])];
      textItems.value = [...(newPage.text_items || [])];
      blankCanvasConfig.value = newPage.canvas_config;
      canvasMode.value = `blank-${newPage.canvas_type}`;
    };

    const duplicatePage = async (index) => {
      const original = pages.value[index];
      const newPage = {
        ...original,
        id: Date.now(),
        name: `${original.name} (Copia)`,
        order: pages.value.length + 1,
      };
      pages.value.push(newPage);
      showNotification('Página duplicada');
      
      // Auto-guardar al duplicar página
      if (currentDesignId.value) {
        await autoSave();
      }
    };

    const deletePage = (index) => {
      if (pages.value.length <= 1) {
        showNotification('No puedes eliminar la última página', 'error');
        return;
      }
      
      if (confirm(`¿Eliminar ${pages.value[index].name}?`)) {
        pages.value.splice(index, 1);
        
        // Reordenar
        pages.value.forEach((page, idx) => {
          page.order = idx + 1;
        });
        
        // Si eliminamos la página actual, cambiar a la primera
        if (currentPageIndex.value >= pages.value.length) {
          switchToPage(0);
        } else if (currentPageIndex.value === index) {
          switchToPage(0);
        }
        
        showNotification('Página eliminada');
        
        // Auto-guardar al eliminar página
        if (currentDesignId.value) {
          autoSave();
        }
      }
    };

    const renamePage = (index) => {
      const currentName = pages.value[index].name;
      const newName = prompt('Nuevo nombre de la página:', currentName);
      if (newName && newName.trim() && newName.trim() !== currentName) {
        pages.value[index].name = newName.trim();
        showNotification('Página renombrada correctamente');
      }
    };

    const handleSave = async (isAutoSave = false) => {
      // Si es auto-save y no hay nombre, no hacer nada
      if (isAutoSave && !projectName.value) {
        return;
      }

      if (!projectName.value) {
        projectName.value = prompt('Nombre del diseño:', `Diseño ${new Date().toLocaleString()}`);
        if (!projectName.value) return;
      }

      isSaving.value = true;

      try {
        // Guardar estado actual en la página actual
        if (currentDeviceKey.value && deviceConfigs.value[currentDeviceKey.value]) {
          const currentPage = pages.value[currentPageIndex.value];
          if (currentPage) {
            currentPage.canvas_images = [...canvasImages.value];
            currentPage.text_items = [...textItems.value];
            currentPage.canvas_config = blankCanvasConfig.value;
          }
        }
        
        // Generar thumbnail
        const thumbnail = await generateThumbnail();
        
        // Preparar datos del diseño con nueva estructura multi-dispositivo
        const designData = {
          name: projectName.value,
          description: '',
          device_configs: deviceConfigs.value, // Nueva estructura
          current_device_key: currentDeviceKey.value, // Dispositivo actual
          canvas_type: canvasMode.value === 'template' ? 'template' : canvasMode.value.replace('blank-', ''),
          canvas_config: blankCanvasConfig.value || selectedTemplate.value?.layout_config || {},
          canvas_images: canvasImages.value.map(img => ({
            id: img.id,
            filename: img.filename, // Guardar solo filename
            x: img.x,
            y: img.y,
            width: img.width,
            height: img.height,
            rotation: img.rotation || 0,
            zIndex: img.zIndex
          })),
          gallery_images: galleryImages.value.map(img => ({
            id: img.id,
            filename: img.filename, // Guardar solo filename
            width: img.width,
            height: img.height
          })),
          text_items: textItems.value,
          thumbnail: thumbnail
        };

        let response;
        let designId;
        
        // Si ya existe el diseño, actualizar; si no, crear uno nuevo
        if (currentDesignId.value) {
          response = await axios.put(`/api/designs/${currentDesignId.value}`, designData);
          designId = currentDesignId.value;
        } else {
          response = await axios.post('/api/designs', designData);
          designId = response.data.id || response.data.design.id;
          currentDesignId.value = designId;
        }
        
        // Guardar la página actual antes de sincronizar
        const currentPage = pages.value[currentPageIndex.value];
        if (currentPage) {
          currentPage.canvas_images = [...canvasImages.value];
          currentPage.text_items = [...textItems.value];
          currentPage.canvas_config = blankCanvasConfig.value;
        }
        
        // Guardar/actualizar todas las páginas en la base de datos
        try {
          // Obtener páginas existentes
          let existingPages = [];
          try {
            const existingPagesResponse = await axios.get(`/api/designs/${designId}/pages`);
            existingPages = existingPagesResponse.data || [];
          } catch (e) {
            console.log('No hay páginas existentes');
          }
          
          // Actualizar o crear cada página
          for (let i = 0; i < pages.value.length; i++) {
            const page = pages.value[i];
            const pageData = {
              name: page.name,
              order: i + 1,
              canvas_type: page.canvas_type,
              canvas_config: page.canvas_config,
              canvas_images: (page.canvas_images || []).map(img => ({
                id: img.id,
                filename: img.filename,
                x: img.x,
                y: img.y,
                width: img.width,
                height: img.height,
                rotation: img.rotation || 0,
                zIndex: img.zIndex
              })),
              text_items: page.text_items || [],
              thumbnail: page.thumbnail || null,
            };
            
            // Si la página tiene id de DB y existe, actualizar; si no, crear
            const existingPage = existingPages.find(p => p.id === page.id);
            if (existingPage) {
              await axios.put(`/api/designs/${designId}/pages/${existingPage.id}`, pageData);
            } else {
              const newPageResponse = await axios.post(`/api/designs/${designId}/pages`, pageData);
              // Actualizar el ID local con el ID de la base de datos
              pages.value[i].id = newPageResponse.data.id || newPageResponse.data.page?.id;
            }
          }
          
          // Eliminar páginas que ya no existen
          for (const existingPage of existingPages) {
            const stillExists = pages.value.some(p => p.id === existingPage.id);
            if (!stillExists) {
              await axios.delete(`/api/designs/${designId}/pages/${existingPage.id}`);
            }
          }
        } catch (pagesError) {
          console.error('Error guardando páginas:', pagesError);
        }
        
        hasUnsavedChanges.value = false;
        lastSavedAt.value = new Date();
        
        // Actualizar URL con el ID encriptado
        updateUrlWithDesignId(designId);
        
        // Iniciar auto-guardado si es la primera vez que se guarda
        if (!autoSaveInterval.value) {
          startAutoSave();
        }
        
        if (!isAutoSave) {
          showNotification('Diseño guardado correctamente');
        }
        console.log('Diseño guardado:', response.data);
      } catch (error) {
        // El interceptor global maneja el error 401
        if (error.response?.status !== 401) {
          if (!isAutoSave) {
            showNotification('Error al guardar el diseño', 'error');
          }
          console.error('Error guardando diseño:', error);
        }
      } finally {
        isSaving.value = false;
      }
    };

    const generateThumbnail = async () => {
      if (!canvasRef.value) return null;
      
      return new Promise((resolve) => {
        const canvas = canvasRef.value.$refs.canvas;
        if (!canvas) {
          resolve(null);
          return;
        }
        
        // Generar thumbnail a escala reducida
        const thumbnailCanvas = document.createElement('canvas');
        thumbnailCanvas.width = 400;
        thumbnailCanvas.height = (canvas.height / canvas.width) * 400;
        const ctx = thumbnailCanvas.getContext('2d');
        ctx.drawImage(canvas, 0, 0, thumbnailCanvas.width, thumbnailCanvas.height);
        
        resolve(thumbnailCanvas.toDataURL('image/png'));
      });
    };

    const loadSavedDesigns = async () => {
      loadingDesigns.value = true;
      try {
        const response = await axios.get('/api/designs');
        savedDesigns.value = response.data.data || response.data;
      } catch (error) {
        showNotification('Error al cargar diseños', 'error');
        console.error('Error cargando diseños:', error);
      } finally {
        loadingDesigns.value = false;
      }
    };

    const loadDesign = async (designId) => {
      try {
        const response = await axios.get(`/api/designs/${designId}`);
        const design = response.data;

        // Cargar datos del diseño
        projectName.value = design.name;
        currentDesignId.value = design.id;
        
        // Cargar galería - construir URLs desde filenames (filtrar datos antiguos sin filename)
        galleryImages.value = (design.gallery_images || [])
          .filter(img => img.filename) // Solo imágenes con filename
          .map(img => ({
            id: img.id,
            filename: img.filename,
            url: `/api/designs/${designId}/images/${img.filename}`,
            width: img.width,
            height: img.height
          }));
        
        // NUEVO: Cargar estructura multi-dispositivo
        if (design.device_configs && Object.keys(design.device_configs).length > 0) {
          // Formato nuevo: cargar deviceConfigs directamente
          deviceConfigs.value = design.device_configs;
          currentDeviceKey.value = design.current_device_key || Object.keys(design.device_configs)[0];
          
          // Cargar configuración del dispositivo actual
          const currentConfig = deviceConfigs.value[currentDeviceKey.value];
          if (currentConfig) {
            selectedDevice.value = currentConfig.device;
            canvasOrientation.value = currentConfig.orientation;
            customWidth.value = currentConfig.width;
            customHeight.value = currentConfig.height;
            
            // Cargar primera página del dispositivo actual
            if (currentConfig.pages && currentConfig.pages.length > 0) {
              currentPageIndex.value = 0;
              const firstPage = currentConfig.pages[0];
              
              canvasImages.value = (firstPage.canvas_images || [])
                .filter(img => img.filename)
                .map(img => ({
                  ...img,
                  url: `/api/designs/${designId}/images/${img.filename}`
                }));
              textItems.value = firstPage.text_items || [];
              blankCanvasConfig.value = firstPage.canvas_config;
              canvasMode.value = firstPage.canvas_type ? `blank-${firstPage.canvas_type}` : 'blank-vertical';
              
              // Sincronizar backgroundColor desde el config cargado
              if (firstPage.canvas_config?.layout_config?.canvas?.background) {
                backgroundColor.value = firstPage.canvas_config.layout_config.canvas.background;
              }
            }
          }
        } else {
          // MIGRACIÓN: Formato antiguo - migrar a deviceConfigs
          console.log('Migrando proyecto antiguo a formato multi-dispositivo...');
          
          // Configurar canvas
          if (design.canvas_type === 'template') {
            canvasMode.value = 'template';
            selectedTemplate.value = { layout_config: design.canvas_config };
          } else {
            canvasMode.value = `blank-${design.canvas_type}`;
            blankCanvasConfig.value = design.canvas_config;
          }

          // Cargar imágenes del canvas - construir URLs desde filenames (filtrar datos antiguos sin filename)
          canvasImages.value = (design.canvas_images || [])
            .filter(img => img.filename) // Solo imágenes con filename
            .map(img => ({
              ...img,
              url: `/api/designs/${designId}/images/${img.filename}`
            }));

          // Cargar textos
          textItems.value = design.text_items || [];

          // Intentar cargar páginas del diseño
          let legacyPages = [];
          try {
            const pagesResponse = await axios.get(`/api/designs/${designId}/pages`);
            if (pagesResponse.data && pagesResponse.data.length > 0) {
              legacyPages = pagesResponse.data.map(page => ({
                ...page,
                canvas_images: (page.canvas_images || [])
                  .filter(img => img.filename)
                  .map(img => ({
                    ...img,
                    url: `/api/designs/${designId}/images/${img.filename}`
                  }))
              }));
            } else {
              // Si no tiene páginas, crear una con los datos actuales
              legacyPages = [{
                id: Date.now(),
                name: 'Página 1',
                order: 1,
                canvas_type: design.canvas_type,
                canvas_config: design.canvas_config,
                canvas_images: design.canvas_images || [],
                text_items: design.text_items || [],
                thumbnail: design.thumbnail_path || null,
              }];
            }
          } catch (pagesError) {
            console.log('No se pudieron cargar páginas, creando página única');
            legacyPages = [{
              id: Date.now(),
              name: 'Página 1',
              order: 1,
              canvas_type: design.canvas_type,
              canvas_config: design.canvas_config,
              canvas_images: design.canvas_images || [],
              text_items: design.text_items || [],
              thumbnail: design.thumbnail_path || null,
            }];
          }
          
          // Migrar a deviceConfigs
          const legacyOrientation = design.canvas_config?.orientation || 'vertical';
          const legacyDevice = design.canvas_type || 'vertical';
          const legacyKey = `${legacyDevice}-${legacyOrientation}`;
          
          deviceConfigs.value = {
            [legacyKey]: {
              device: legacyDevice,
              orientation: legacyOrientation,
              width: design.canvas_config?.width || 1179,
              height: design.canvas_config?.height || 2556,
              pages: legacyPages
            }
          };
          
          currentDeviceKey.value = legacyKey;
          selectedDevice.value = legacyDevice;
          canvasOrientation.value = legacyOrientation;
          
          // Cargar primera página
          if (legacyPages.length > 0) {
            currentPageIndex.value = 0;
            const firstPage = legacyPages[0];
            canvasImages.value = firstPage.canvas_images || [];
            textItems.value = firstPage.text_items || [];
            blankCanvasConfig.value = firstPage.canvas_config;
            
            // Sincronizar backgroundColor desde el config cargado
            if (firstPage.canvas_config?.layout_config?.canvas?.background) {
              backgroundColor.value = firstPage.canvas_config.layout_config.canvas.background;
            }
          }
        }

        // Actualizar URL con el ID encriptado
        updateUrlWithDesignId(designId);

        showSavedDesignsModal.value = false;
        showNotification('Diseño cargado correctamente');
      } catch (error) {
        showNotification('Error al cargar diseño', 'error');
        console.error('Error cargando diseño:', error);
      }
    };

    const duplicateDesign = async (designId) => {
      if (!confirm('¿Duplicar este diseño?')) return;

      try {
        const response = await axios.post(`/api/designs/${designId}/duplicate`);
        showNotification('Diseño duplicado correctamente');
        loadSavedDesigns(); // Recargar lista
      } catch (error) {
        showNotification('Error al duplicar diseño', 'error');
        console.error('Error duplicando diseño:', error);
      }
    };

    const deleteDesign = async (designId) => {
      if (!confirm('¿Estás seguro de eliminar este diseño?')) return;

      try {
        await axios.delete(`/api/designs/${designId}`);
        showNotification('Diseño eliminado correctamente');
        loadSavedDesigns(); // Recargar lista
      } catch (error) {
        showNotification('Error al eliminar diseño', 'error');
        console.error('Error eliminando diseño:', error);
      }
    };

    const getCanvasTypeLabel = (type) => {
      const labels = {
        vertical: 'Vertical',
        horizontal: 'Horizontal',
        template: 'Template'
      };
      return labels[type] || type;
    };

    const formatDate = (dateString) => {
      const date = new Date(dateString);
      const now = new Date();
      const diffMs = now - date;
      const diffMins = Math.floor(diffMs / 60000);
      const diffHours = Math.floor(diffMs / 3600000);
      const diffDays = Math.floor(diffMs / 86400000);

      if (diffMins < 1) return 'Hace un momento';
      if (diffMins < 60) return `Hace ${diffMins} min`;
      if (diffHours < 24) return `Hace ${diffHours}h`;
      if (diffDays < 7) return `Hace ${diffDays} días`;
      
      return date.toLocaleDateString('es-ES', { day: 'numeric', month: 'short' });
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

      // Simular generación de IA - Agregar nuevo texto con contenido generado
      setTimeout(() => {
        const generatedText = {
          id: textIdCounter.value++,
          content: 'Tu nueva app favorita',
          fontSize: 32,
          fontFamily: 'Arial',
          color: '#000000',
          fontWeight: 'bold',
          fontStyle: 'normal',
          rotation: 0,
          x: undefined,
          y: undefined,
        };
        textItems.value.push(generatedText);
        selectedTextIndex.value = textItems.value.length - 1;
        markAsChanged();
        showNotification('Texto generado con IA');
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

    // Watch para cargar diseños cuando se abre el modal
    watch(showSavedDesignsModal, (newVal) => {
      if (newVal) {
        loadSavedDesigns();
      }
    });

    // Watch para actualizar el background del canvas cuando cambia el color
    watch(backgroundColor, (newColor) => {
      if (blankCanvasConfig.value?.layout_config?.canvas) {
        blankCanvasConfig.value.layout_config.canvas.background = newColor;
      }
    });

    // Watchers para detectar cambios
    watch([textItems, canvasImages, blankCanvasConfig, backgroundColor], () => {
      markAsChanged();
    }, { deep: true });

    watch(pages, () => {
      markAsChanged();
    }, { deep: true });

    onMounted(async () => {
      loadTemplates();
      loadMockups();
      loadMockupCategories();
      
      // Verificar si hay un diseño en la URL
      const designIdFromUrl = getDesignIdFromUrl();
      
      if (designIdFromUrl) {
        // Cargar el diseño desde la URL
        console.log('Cargando diseño desde URL:', designIdFromUrl);
        await loadDesign(designIdFromUrl);
      } else {
        // Mostrar modal para nuevo proyecto
        startNewProject();
      }

      // Cerrar menú de dispositivos al hacer click fuera
      document.addEventListener('click', (e) => {
        if (showDeviceMenu.value && !e.target.closest('.control-dropdown')) {
          showDeviceMenu.value = false;
        }
      });
    });

    onUnmounted(() => {
      // Limpiar intervalo de auto-guardado
      stopAutoSave();
      
      // Limpiar debounce timer
      if (autoSaveDebounceTimer.value) {
        clearTimeout(autoSaveDebounceTimer.value);
      }
    });

    return {
      // Refs
      canvasRef,
      fileInput,

      // Estado
      projectName,
      canvasMode,
      canvasOrientation,
      selectedDevice,
      customWidth,
      customHeight,
      showDeviceMenu,
      devicePresets,
      blankCanvasConfig,
      showTemplateModal,
      showSavedDesignsModal,
      showPagesModal,
      showProjectNameModal,
      showCustomDimensionsModal,
      tempProjectName,
      tempCustomWidth,
      tempCustomHeight,
      customPresets,
      savedDesigns,
      loadingDesigns,
      deviceConfigs,
      currentDeviceKey,
      pages,
      currentPageIndex,
      galleryImages,
      canvasImages,
      templates,
      selectedTemplate,
      templateFilter,
      mockups,
      mockupCategories,
      mockupCategoryFilter,
      loadingMockups,
      textItems,
      selectedTextIndex,
      selectedLanguage,
      backgroundColor,
      exportFormat,
      notification,
      hasUnsavedChanges,
      isSaving,
      lastSavedAt,

      // Constantes
      presetColors,
      languages,

      // Computed
      canUsePremium,
      canUseAI,
      filteredTemplates,

      // Métodos
      markAsChanged,
      setOrientation,
      applyDevicePreset,
      applyCustomSize,
      selectDeviceFromMenu,
      hasDevicePages,
      getDevicePageCount,
      switchToDevice,
      toggleCustomOrientation,
      confirmCustomDimensions,
      selectCustomPreset,
      startNewProject,
      confirmProjectName,
      cancelProjectName,
      createBlankCanvas,
      openTemplateModal,
      closeTemplateModal,
      addNewText,
      removeText,
      insertImageToCanvas,
      insertMockupToCanvas,
      loadMockups,
      loadMockupCategories,
      removeImage,
      selectTemplate,
      handleUpload,
      handleFileSelect,
      handleSave,
      handleExport,
      handleGenerateAI,
      handleTranslate,
      handleRendered,
      handleRenderError,
      loadDesign,
      duplicateDesign,
      deleteDesign,
      getCanvasTypeLabel,
      formatDate,
      addNewPage,
      switchToPage,
      duplicatePage,
      deletePage,
      renamePage,
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

.pages-controls {
  display: flex;
  align-items: center;
  gap: 0.5rem;
  margin-left: 1rem;
  padding-left: 1rem;
  border-left: 1px solid var(--border);
}

.btn-icon-text {
  display: flex;
  align-items: center;
  gap: 0.5rem;
  padding: 0.5rem 0.75rem;
  background: var(--bg-surface-light);
  border: 1px solid var(--border);
  border-radius: 6px;
  color: var(--text-primary);
  font-size: 0.875rem;
  cursor: pointer;
  transition: all 0.2s ease;
  font-weight: 500;
}

.btn-icon-text:hover {
  background: #475569;
  border-color: var(--primary);
  transform: translateY(-1px);
}

.btn-icon-text svg {
  opacity: 0.8;
}

.btn-add-page {
  background: var(--primary);
  border-color: var(--primary);
  color: white;
}

.btn-add-page:hover {
  background: var(--primary-hover);
  border-color: var(--primary-hover);
}

.btn-add-page svg {
  opacity: 1;
}

.header-center {
  display: flex;
  align-items: center;
  gap: 1rem;
}

.save-indicator {
  display: flex;
  align-items: center;
  font-size: 0.75rem;
  padding: 0.375rem 0.75rem;
  border-radius: 6px;
  background: var(--bg-surface-light);
}

.save-indicator span {
  display: flex;
  align-items: center;
  gap: 0.375rem;
}

.save-indicator .saving {
  color: var(--info);
}

.save-indicator .unsaved {
  color: var(--warning);
}

.save-indicator .saved {
  color: var(--success);
}

.save-indicator svg {
  flex-shrink: 0;
}

@keyframes spin {
  from { transform: rotate(0deg); }
  to { transform: rotate(360deg); }
}

.spin {
  animation: spin 1s linear infinite;
}

.project-name-wrapper {
  position: relative;
  display: flex;
  align-items: center;
  gap: 0.5rem;
}

.project-name-input-inline {
  background: transparent;
  border: none;
  color: var(--text-primary);
  font-size: 0.95rem;
  font-weight: 500;
  padding: 0.5rem 0.75rem;
  border-radius: 6px;
  min-width: 200px;
  max-width: 400px;
  transition: all 0.2s ease;
}

.project-name-input-inline:hover {
  background: var(--bg-surface-light);
}

.project-name-input-inline:focus {
  outline: none;
  background: var(--bg-surface-light);
  border: 1px solid var(--primary);
  padding: calc(0.5rem - 1px) calc(0.75rem - 1px);
}

.project-name-input-inline::placeholder {
  color: var(--text-secondary);
}

.edit-icon {
  color: var(--text-secondary);
  opacity: 0;
  transition: opacity 0.2s ease;
  pointer-events: none;
}

.project-name-wrapper:hover .edit-icon {
  opacity: 0.5;
}

.project-name {
  color: var(--text-secondary);
  font-size: 0.875rem;
}

.btn-pages {
  display: flex;
  align-items: center;
  gap: 0.5rem;
  padding: 0.5rem 0.75rem;
  background: var(--bg-surface-light);
  border: 1px solid var(--border);
  border-radius: 8px;
  color: var(--text-primary);
  cursor: pointer;
  transition: all 0.2s ease;
  position: relative;
}

.btn-pages:hover {
  background: #475569;
  border-color: var(--primary);
}

.btn-pages svg {
  opacity: 0.7;
}

.pages-count {
  position: absolute;
  top: -6px;
  right: -6px;
  background: var(--primary);
  color: white;
  font-size: 0.625rem;
  font-weight: 600;
  min-width: 18px;
  height: 18px;
  display: flex;
  align-items: center;
  justify-content: center;
  border-radius: 9px;
  padding: 0 4px;
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
   Canvas Icons
   ======================================== */
.canvas-controls {
  display: flex;
  gap: 0.5rem;
  align-items: center;
  padding-right: 0.75rem;
  margin-right: 0.75rem;
  border-right: 1px solid var(--border);
}

.control-dropdown {
  position: relative;
}

.btn-icon.has-dropdown {
  display: flex;
  align-items: center;
  gap: 0.25rem;
}

.dropdown-arrow {
  width: 10px;
  height: 10px;
  opacity: 0.6;
}

.tooltip {
  position: absolute;
  top: calc(100% + 8px);
  left: 50%;
  transform: translateX(-50%);
  padding: 0.5rem 0.75rem;
  background: rgba(15, 23, 42, 0.95);
  color: var(--text-primary);
  font-size: 0.75rem;
  white-space: nowrap;
  border-radius: 6px;
  pointer-events: none;
  opacity: 0;
  visibility: hidden;
  transition: opacity 0.2s, visibility 0.2s;
  z-index: 1000;
}

.control-dropdown:hover .tooltip {
  opacity: 1;
  visibility: visible;
}

.dropdown-menu {
  position: absolute;
  top: calc(100% + 8px);
  left: 0;
  min-width: 220px;
  background: var(--bg-surface);
  border: 1px solid var(--border);
  border-radius: 8px;
  box-shadow: 0 8px 24px rgba(0, 0, 0, 0.4);
  z-index: 100;
  max-height: 400px;
  overflow-y: auto;
}

.dropdown-section {
  padding: 0.5rem;
  border-bottom: 1px solid var(--border);
}

.dropdown-section:last-child {
  border-bottom: none;
}

.dropdown-label {
  font-size: 0.75rem;
  color: var(--text-secondary);
  font-weight: 600;
  text-transform: uppercase;
  letter-spacing: 0.05em;
  padding: 0.375rem 0.5rem;
  margin-bottom: 0.25rem;
}

.dropdown-section button {
  width: 100%;
  text-align: left;
  padding: 0.625rem 0.75rem;
  background: transparent;
  border: none;
  color: var(--text-primary);
  font-size: 0.875rem;
  border-radius: 6px;
  cursor: pointer;
  transition: all 0.2s;
  display: flex;
  align-items: center;
  justify-content: space-between;
  gap: 0.5rem;
}

.dropdown-section button:hover {
  background: var(--bg-surface-light);
}

.dropdown-section button.active {
  background: var(--primary);
  color: white;
}

.device-name {
  flex: 1;
}

.device-indicator {
  display: flex;
  align-items: center;
  gap: 0.375rem;
}

.check-icon {
  color: #10b981;
  stroke: #10b981;
}

.dropdown-section button.active .check-icon {
  color: white;
  stroke: white;
}

.page-count {
  font-size: 0.75rem;
  font-weight: 600;
  color: #10b981;
  background: rgba(16, 185, 129, 0.1);
  padding: 0.125rem 0.5rem;
  border-radius: 12px;
}

.dropdown-section button.active .page-count {
  color: white;
  background: rgba(255, 255, 255, 0.2);
}

.btn-create-custom {
  width: 100%;
  display: flex;
  align-items: center;
  justify-content: center;
  gap: 0.5rem;
  padding: 0.75rem 1rem;
  background: transparent;
  border: 2px dashed var(--border);
  border-radius: 8px;
  color: var(--text-secondary);
  font-size: 0.875rem;
  font-weight: 500;
  cursor: pointer;
  transition: all 0.2s;
}

.btn-create-custom:hover {
  border-color: var(--primary);
  color: var(--primary);
  background: rgba(99, 102, 241, 0.05);
}

.custom-dimensions-form {
  display: flex;
  align-items: flex-end;
  gap: 1rem;
  margin-bottom: 0.5rem;
}

.custom-dimensions-form .form-group {
  flex: 1;
  margin-bottom: 0;
}

.dimension-separator {
  font-size: 1.5rem;
  color: var(--text-secondary);
  font-weight: 500;
  margin-bottom: 0.5rem;
}

.input-text {
  width: 100%;
  background: var(--bg-surface-light);
  border: 1px solid var(--border);
  color: var(--text-primary);
  padding: 0.625rem 0.875rem;
  border-radius: 6px;
  font-size: 0.875rem;
}

.input-text:focus {
  outline: none;
  border-color: var(--primary);
  box-shadow: 0 0 0 3px rgba(99, 102, 241, 0.1);
}

.form-hint {
  display: block;
  margin-top: 0.5rem;
  font-size: 0.75rem;
  color: var(--text-secondary);
}

.btn-icon.active {
  background: var(--primary);
  color: white;
}

.canvas-icons .btn-icon {
  position: relative;
}

.canvas-icons .btn-icon::after {
  content: attr(title);
  position: absolute;
  bottom: -35px;
  left: 50%;
  transform: translateX(-50%);
  padding: 0.375rem 0.75rem;
  background: rgba(15, 23, 42, 0.95);
  color: var(--text-primary);
  font-size: 0.75rem;
  white-space: nowrap;
  border-radius: 6px;
  pointer-events: none;
  opacity: 0;
  transition: opacity 0.2s ease;
  z-index: 1000;
}

.canvas-icons .btn-icon:hover::after {
  opacity: 1;
}

/* ========================================
   Gallery Grid
   ======================================== */
.gallery-grid {
  display: grid;
  grid-template-columns: repeat(2, 1fr);
  gap: 0.5rem;
}

.gallery-item {
  position: relative;
  aspect-ratio: 1;
  border-radius: 8px;
  overflow: hidden;
  cursor: pointer;
  border: 2px solid transparent;
  transition: all 0.2s ease;
}

.gallery-item:hover {
  border-color: var(--primary);
  transform: scale(1.02);
}

.gallery-item img {
  width: 100%;
  height: 100%;
  object-fit: cover;
}

.gallery-item-overlay {
  position: absolute;
  inset: 0;
  display: flex;
  align-items: center;
  justify-content: center;
  background: rgba(99, 102, 241, 0.8);
  opacity: 0;
  transition: opacity 0.2s ease;
}

.gallery-item:hover .gallery-item-overlay {
  opacity: 1;
}

.gallery-item-overlay svg {
  color: white;
}

.gallery-delete {
  position: absolute;
  top: 4px;
  right: 4px;
  display: flex;
  align-items: center;
  justify-content: center;
  width: 24px;
  height: 24px;
  background: rgba(239, 68, 68, 0.9);
  border: none;
  border-radius: 4px;
  color: white;
  cursor: pointer;
  opacity: 0;
  transition: opacity 0.2s ease;
  z-index: 10;
}

.gallery-item:hover .gallery-delete {
  opacity: 1;
}

.gallery-delete:hover {
  background: #dc2626;
}

.mockup-name {
  position: absolute;
  bottom: 0;
  left: 0;
  right: 0;
  padding: 4px 6px;
  background: rgba(15, 23, 42, 0.9);
  color: var(--text-primary);
  font-size: 10px;
  text-align: center;
  white-space: nowrap;
  overflow: hidden;
  text-overflow: ellipsis;
  opacity: 0;
  transition: opacity 0.2s ease;
}

.gallery-item:hover .mockup-name {
  opacity: 1;
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
  aspect-ratio: 9/16; /* Default vertical */
}

.template-item[data-orientation="horizontal"] {
  aspect-ratio: 16/9;
}

.template-item[data-orientation="both"] {
  aspect-ratio: 1/1;
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
   Modal
   ======================================== */
.modal-overlay {
  position: fixed;
  inset: 0;
  background: rgba(0, 0, 0, 0.7);
  backdrop-filter: blur(4px);
  display: flex;
  align-items: center;
  justify-content: center;
  z-index: 1000;
  animation: fadeIn 0.2s ease;
}

.modal-content {
  background: var(--bg-surface);
  border: 1px solid var(--border);
  border-radius: 12px;
  width: 90%;
  max-width: 800px;
  max-height: 80vh;
  overflow: hidden;
  display: flex;
  flex-direction: column;
  animation: slideUp 0.3s ease;
}

.modal-large {
  max-width: 1200px;
}

.modal-header {
  display: flex;
  align-items: center;
  justify-content: space-between;
  padding: 1.5rem;
  border-bottom: 1px solid var(--border);
}

.modal-header h2 {
  font-size: 1.25rem;
  font-weight: 600;
  color: var(--text-primary);
}

.modal-body {
  padding: 2rem;
  overflow-y: auto;
  flex: 1;
}

.modal-footer {
  display: flex;
  align-items: center;
  justify-content: flex-end;
  gap: 0.75rem;
  padding: 1.5rem;
  border-top: 1px solid var(--border);
  background: var(--bg);
}

.modal-container {
  background: var(--bg-surface);
  border: 1px solid var(--border);
  border-radius: 12px;
  width: 90%;
  max-width: 500px;
  overflow: hidden;
  display: flex;
  flex-direction: column;
  animation: slideUp 0.3s ease;
}

.form-group {
  display: flex;
  flex-direction: column;
  gap: 0.5rem;
}

.form-group label {
  font-size: 0.875rem;
  font-weight: 500;
  color: var(--text-primary);
}

.input-text {
  background: var(--bg);
  border: 1px solid var(--border);
  color: var(--text-primary);
  padding: 0.75rem 1rem;
  border-radius: 8px;
  font-size: 1rem;
  transition: all 0.2s;
}

.input-text:focus {
  outline: none;
  border-color: var(--primary);
  box-shadow: 0 0 0 3px rgba(99, 102, 241, 0.1);
}

.form-hint {
  font-size: 0.75rem;
  color: var(--text-secondary);
}

.text-center {
  text-align: center;
}

.text-secondary {
  color: var(--text-secondary);
}

@keyframes fadeIn {
  from {
    opacity: 0;
  }
  to {
    opacity: 1;
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

/* ========================================
   Diseños Guardados
   ======================================== */
.designs-grid {
  display: grid;
  grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
  gap: 1.5rem;
}

.design-card {
  background: var(--bg);
  border: 1px solid var(--border);
  border-radius: 8px;
  overflow: hidden;
  cursor: pointer;
  transition: all 0.2s ease;
  display: flex;
  flex-direction: column;
}

.design-card:hover {
  border-color: var(--primary);
  transform: translateY(-2px);
  box-shadow: 0 4px 12px rgba(99, 102, 241, 0.2);
}

.design-thumbnail {
  width: 100%;
  height: 200px;
  background: var(--bg-surface);
  display: flex;
  align-items: center;
  justify-content: center;
  overflow: hidden;
}

.design-thumbnail img {
  width: 100%;
  height: 100%;
  object-fit: cover;
}

.design-placeholder {
  color: var(--text-secondary);
  opacity: 0.3;
}

.design-info {
  padding: 1rem;
  flex: 1;
}

.design-info h3 {
  font-size: 1rem;
  font-weight: 600;
  color: var(--text-primary);
  margin-bottom: 0.5rem;
  white-space: nowrap;
  overflow: hidden;
  text-overflow: ellipsis;
}

.design-meta {
  display: flex;
  align-items: center;
  gap: 0.75rem;
  font-size: 0.875rem;
  color: var(--text-secondary);
}

.design-type {
  padding: 0.25rem 0.5rem;
  background: var(--bg-surface);
  border-radius: 4px;
  font-size: 0.75rem;
  text-transform: uppercase;
  letter-spacing: 0.5px;
}

.design-date {
  font-size: 0.75rem;
}

.design-actions {
  display: flex;
  gap: 0.5rem;
  padding: 0.75rem 1rem;
  border-top: 1px solid var(--border);
}

.btn-sm {
  padding: 0.5rem;
  font-size: 0.875rem;
}

.btn-danger {
  color: #ef4444;
}

.btn-danger:hover {
  background: rgba(239, 68, 68, 0.1);
}

.btn-danger:disabled {
  opacity: 0.4;
  cursor: not-allowed;
}

/* ========================================
   Modal de Páginas
   ======================================== */
.pages-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 1.5rem;
  padding-bottom: 1rem;
  border-bottom: 1px solid var(--border);
}

.pages-grid {
  display: grid;
  grid-template-columns: repeat(auto-fill, minmax(200px, 1fr));
  gap: 1rem;
}

.page-card {
  background: var(--bg);
  border: 2px solid var(--border);
  border-radius: 8px;
  overflow: hidden;
  cursor: pointer;
  transition: all 0.2s ease;
  display: flex;
  flex-direction: column;
}

.page-card:hover {
  border-color: var(--primary);
  transform: translateY(-2px);
}

.page-card.active {
  border-color: var(--primary);
  background: rgba(99, 102, 241, 0.1);
}

.page-thumbnail {
  width: 100%;
  height: 150px;
  background: var(--bg-surface);
  display: flex;
  align-items: center;
  justify-content: center;
  overflow: hidden;
  position: relative;
}

.page-number {
  position: absolute;
  top: 8px;
  left: 8px;
  background: var(--primary);
  color: white;
  font-size: 0.75rem;
  font-weight: 600;
  width: 24px;
  height: 24px;
  display: flex;
  align-items: center;
  justify-content: center;
  border-radius: 50%;
  z-index: 1;
}

.page-thumbnail img {
  width: 100%;
  height: 100%;
  object-fit: cover;
}

.page-placeholder {
  color: var(--text-secondary);
  opacity: 0.3;
}

.page-info {
  padding: 0.75rem;
  flex: 1;
}

.page-info h4 {
  font-size: 0.875rem;
  font-weight: 600;
  color: var(--text-primary);
  margin-bottom: 0.25rem;
  white-space: nowrap;
  overflow: hidden;
  text-overflow: ellipsis;
}

.page-type {
  font-size: 0.75rem;
  color: var(--text-secondary);
}

.page-actions {
  display: flex;
  gap: 0.25rem;
  padding: 0.5rem;
  border-top: 1px solid var(--border);
  justify-content: center;
}

.py-8 {
  padding-top: 2rem;
  padding-bottom: 2rem;
}

.text-sm {
  font-size: 0.875rem;
}

.mt-2 {
  margin-top: 0.5rem;
}

/* Estilos para textos dinámicos */
.text-items-list {
  display: flex;
  flex-direction: column;
  gap: 0.875rem;
}

.text-item-card {
  background: var(--bg-surface);
  border: 2px solid var(--border);
  border-radius: 8px;
  padding: 0.875rem;
  transition: all 0.2s;
  cursor: pointer;
}

.text-item-card:hover {
  border-color: var(--primary-light);
}

.text-item-card.active {
  border-color: var(--primary);
  background: linear-gradient(to bottom, var(--surface), var(--primary-light) / 0.05);
}

.text-item-header {
  display: flex;
  align-items: center;
  gap: 0.625rem;
}

.text-item-number {
  background: var(--primary);
  color: white;
  width: 24px;
  height: 24px;
  border-radius: 50%;
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 0.75rem;
  font-weight: 600;
  flex-shrink: 0;
}

.text-item-input {
  flex: 1;
  min-width: 0;
  max-width: 100%;
  padding: 0.5rem 0.75rem;
  border: 1px solid var(--border);
  border-radius: 6px;
  font-size: 0.875rem;
  background: var(--bg-surface-light);
  color: var(--text-primary);
  box-sizing: border-box;
}

.text-item-input:focus {
  outline: none;
  border-color: var(--primary);
  background: var(--bg-surface-light);
}

.text-item-input::placeholder {
  color: var(--text-secondary);
  opacity: 0.6;
}

.btn-delete-text {
  width: 28px;
  height: 28px;
  border-radius: 4px;
  background: var(--danger);
  color: white;
  border: none;
  font-size: 1.25rem;
  font-weight: bold;
  cursor: pointer;
  display: flex;
  align-items: center;
  justify-content: center;
  transition: all 0.2s;
  flex-shrink: 0;
}

.btn-delete-text:hover {
  background: #dc2626;
  transform: scale(1.05);
}

.text-item-controls {
  margin-top: 0.875rem;
  padding-top: 0.875rem;
  border-top: 1px solid var(--border);
  display: flex;
  flex-direction: column;
  gap: 0.625rem;
  width: 100%;
  box-sizing: border-box;
}

.form-group-inline {
  display: flex;
  gap: 0.625rem;
  align-items: center;
  width: 100%;
}

.form-group-inline label {
  font-size: 0.75rem;
  color: var(--text-secondary);
  min-width: 50px;
  max-width: 50px;
  font-weight: 500;
  flex-shrink: 0;
}

.input-small {
  flex: 1;
  min-width: 0;
  max-width: 100%;
  padding: 0.5rem 0.625rem;
  border: 1px solid var(--border);
  border-radius: 6px;
  font-size: 0.875rem;
  background: var(--bg-surface-light);
  color: var(--text-primary);
  box-sizing: border-box;
}

.input-small:focus {
  outline: none;
  border-color: var(--primary);
  background: var(--bg-surface-light);
}

.select-small {
  flex: 1;
  min-width: 0;
  max-width: 100%;
  padding: 0.5rem 0.625rem;
  border: 1px solid var(--border);
  border-radius: 6px;
  font-size: 0.875rem;
  background: var(--bg-surface-light);
  color: var(--text-primary);
  cursor: pointer;
  box-sizing: border-box;
}

.select-small:focus {
  outline: none;
  border-color: var(--primary);
}

.select-small option {
  background: var(--bg-surface);
  color: var(--text-primary);
  padding: 0.5rem;
}

.input-color-small {
  width: 40px;
  height: 32px;
  border: 1px solid var(--border);
  border-radius: 4px;
  cursor: pointer;
  padding: 2px;
}

.text-style-buttons {
  display: flex;
  gap: 0.625rem;
}

.btn-style {
  flex: 1;
  padding: 0.5rem 0.75rem;
  border: 1px solid var(--border);
  border-radius: 6px;
  background: var(--bg-surface);
  color: var(--text-primary);
  font-weight: 600;
  cursor: pointer;
  transition: all 0.2s;
}

.btn-style:hover {
  background: var(--bg-surface-light);
  border-color: var(--text-secondary);
}

.btn-style.active {
  background: var(--primary);
  color: white;
  border-color: var(--primary);
}
</style>
