<template>
  <div class="mockup-canvas" ref="canvasContainer">
    <!-- Canvas de renderizado -->
    <canvas 
      ref="canvas" 
      :width="canvasWidth" 
      :height="canvasHeight"
      :style="{ 
        cursor: canvasCursor,
        transform: `translate(${offset.x}px, ${offset.y}px) scale(${zoom})`,
        transformOrigin: 'center center'
      }"
      @mousedown="handleMouseDown"
    ></canvas>

    <!-- Controles de zoom -->
    <div class="canvas-controls">
      <button @click="zoomIn" title="Acercar">
        <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
          <circle cx="11" cy="11" r="8"/>
          <line x1="21" y1="21" x2="16.65" y2="16.65"/>
          <line x1="11" y1="8" x2="11" y2="14"/>
          <line x1="8" y1="11" x2="14" y2="11"/>
        </svg>
      </button>
      <span class="zoom-level">{{ Math.round(zoom * 100) }}%</span>
      <button @click="zoomOut" title="Alejar">
        <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
          <circle cx="11" cy="11" r="8"/>
          <line x1="21" y1="21" x2="16.65" y2="16.65"/>
          <line x1="8" y1="11" x2="14" y2="11"/>
        </svg>
      </button>
      <button @click="resetZoom" title="Restablecer">
        <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
          <path d="M3 12a9 9 0 1 0 9-9 9.75 9.75 0 0 0-6.74 2.74L3 8"/>
          <path d="M3 3v5h5"/>
        </svg>
      </button>
    </div>

    <!-- Controles de navegación (paneo) -->
    <div class="pan-controls" v-if="zoom > 0.3">
      <button @click="panUp" title="Mover arriba" class="pan-btn pan-up">
        <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
          <polyline points="18 15 12 9 6 15"/>
        </svg>
      </button>
      <button @click="panLeft" title="Mover izquierda" class="pan-btn pan-left">
        <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
          <polyline points="15 18 9 12 15 6"/>
        </svg>
      </button>
      <button @click="panRight" title="Mover derecha" class="pan-btn pan-right">
        <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
          <polyline points="9 18 15 12 9 6"/>
        </svg>
      </button>
      <button @click="panDown" title="Mover abajo" class="pan-btn pan-down">
        <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
          <polyline points="6 9 12 15 18 9"/>
        </svg>
      </button>
    </div>

    <!-- Indicador de carga -->
    <div v-if="isLoading" class="canvas-loading">
      <div class="loading-spinner"></div>
      <span>Renderizando...</span>
    </div>
  </div>
</template>

<script>
import { ref, onMounted, onUnmounted, watch, nextTick } from 'vue';

export default {
  name: 'MockupCanvas',

  props: {
    canvasImages: {
      type: Array,
      default: () => [],
    },
    template: {
      type: Object,
      default: null,
    },
    texts: {
      type: Object,
      default: () => ({
        headline: '',
        subheadline: '',
        cta: '',
      }),
    },
    backgroundColor: {
      type: String,
      default: '#6366f1',
    },
  },

  emits: ['rendered', 'error'],

  setup(props, { emit }) {
    const canvas = ref(null);
    const canvasContainer = ref(null);
    const canvasWidth = ref(1200);
    const canvasHeight = ref(2688);
    const zoom = ref(0.08);
    const minZoom = 0.08; // Zoom mínimo
    const isLoading = ref(false);
    const isDragging = ref(false);
    const dragStart = ref({ x: 0, y: 0 });
    const offset = ref({ x: 0, y: 0 });
    const canvasCursor = ref('default');
    const renderPending = ref(false);
    const isResizing = ref(false);
    const resizeHandle = ref(null); // 'se', 'sw', 'ne', 'nw' para las esquinas
    const isDraggingText = ref(false);
    const selectedTextKey = ref(null);
    const selectedImageIndex = ref(null); // Índice de la imagen seleccionada
    const isShiftPressed = ref(false); // Para mantener relación de aspecto
    let rafId = null; // Request animation frame ID
    
    // Cache del render base (sin la imagen que se está moviendo)
    let baseCanvas = null;
    let baseNeedsUpdate = true;
    let isRendering = false; // Flag para evitar renders concurrentes
    
    // Posiciones de textos (editables)
    const textPositions = ref({
      headline: null,
      subheadline: null,
      cta: null
    });

    // Cargar imagen del screenshot
    const loadImage = (url) => {
      return new Promise((resolve, reject) => {
        const img = new Image();
        // Solo usar crossOrigin para URLs externas
        if (url && !url.startsWith('blob:') && !url.startsWith('data:')) {
          img.crossOrigin = 'anonymous';
        }
        img.onload = () => resolve(img);
        img.onerror = (e) => {
          console.warn('Error cargando imagen, intentando sin CORS:', url);
          // Reintentar sin crossOrigin si falla
          const retryImg = new Image();
          retryImg.onload = () => resolve(retryImg);
          retryImg.onerror = reject;
          retryImg.src = url;
        };
        img.src = url;
      });
    };

    // Preparar el cache del fondo (llamar al inicio del drag/resize)
    const prepareBaseCache = () => {
      if (!canvas.value) return;
      
      if (!baseCanvas) {
        baseCanvas = document.createElement('canvas');
        baseCanvas.width = canvasWidth.value;
        baseCanvas.height = canvasHeight.value;
      }
      
      const baseCtx = baseCanvas.getContext('2d', { alpha: false, willReadFrequently: false });
      
      // Dibujar fondo
      const bgColor = props.template?.layout_config?.canvas?.background || props.backgroundColor;
      baseCtx.fillStyle = bgColor;
      baseCtx.fillRect(0, 0, canvasWidth.value, canvasHeight.value);
      
      // Dibujar todas las imágenes EXCEPTO la que se está moviendo
      if (props.canvasImages && props.canvasImages.length > 0) {
        // Ordenar solo una vez
        const sortedImages = [...props.canvasImages].sort((a, b) => a.zIndex - b.zIndex);
        const selectedImg = props.canvasImages[selectedImageIndex.value];
        
        for (const img of sortedImages) {
          if (img === selectedImg) continue; // Skip la imagen en movimiento
          
          if (img._cachedImg) {
            baseCtx.drawImage(img._cachedImg, img.x, img.y, img.width, img.height);
          }
        }
      }
      
      baseNeedsUpdate = false;
    };
    
    // Render ultra rápido usando cache del fondo y otras imágenes
    const renderWithCache = () => {
      if (!canvas.value || !baseCanvas) return;
      
      const ctx = canvas.value.getContext('2d', { alpha: false });
      
      // Copiar el base al canvas principal (operación nativa super rápida)
      ctx.drawImage(baseCanvas, 0, 0);
      
      // Dibujar SOLO la imagen que se está moviendo (1 sola operación)
      if (selectedImageIndex.value !== null && props.canvasImages[selectedImageIndex.value]) {
        const img = props.canvasImages[selectedImageIndex.value];
        if (img._cachedImg) {
          ctx.drawImage(img._cachedImg, img.x, img.y, img.width, img.height);
        }
      }
    };

    // Renderizar el mockup en el canvas
    const render = async (showLoading = false, fastRender = false) => {
      baseNeedsUpdate = true; // Marcar que el cache necesita actualización
      if (!canvas.value) return;

      if (showLoading) {
        isLoading.value = true;
      }
      
      // Usar dimensiones del template si está disponible
      if (props.template?.layout_config?.canvas) {
        const newWidth = props.template.layout_config.canvas.width || 1200;
        const newHeight = props.template.layout_config.canvas.height || 2688;
        
        // Ajustar zoom si las dimensiones cambian significativamente
        if (canvasWidth.value !== newWidth || canvasHeight.value !== newHeight) {
          canvasWidth.value = newWidth;
          canvasHeight.value = newHeight;
          
          // IMPORTANTE: Cuando cambian las dimensiones del canvas, Vue actualiza el DOM
          // y eso borra el contenido. Necesitamos esperar y re-renderizar.
          await nextTick();
          
          // Calcular zoom óptimo después de que el DOM se actualice
          zoom.value = calculateOptimalZoom();
        }
      }

      const ctx = canvas.value.getContext('2d', { alpha: false });

      try {
        // Limpiar canvas (más rápido con fillRect que clearRect)
        const bgColor = props.template?.layout_config?.canvas?.background || props.backgroundColor;
        ctx.fillStyle = bgColor;
        ctx.fillRect(0, 0, canvasWidth.value, canvasHeight.value);

        // Aplicar gradiente si está configurado (skip en fast render)
        if (!fastRender && props.template?.layout_config?.canvas?.gradient) {
          const gradient = props.template.layout_config.canvas.gradient;
          const grd = gradient.direction === 'vertical'
            ? ctx.createLinearGradient(0, 0, 0, canvasHeight.value)
            : ctx.createLinearGradient(0, 0, canvasWidth.value, 0);

          grd.addColorStop(0, gradient.start || props.backgroundColor);
          grd.addColorStop(1, gradient.end || props.backgroundColor);
          ctx.fillStyle = grd;
          ctx.fillRect(0, 0, canvasWidth.value, canvasHeight.value);
        }

        // Renderizar todas las imágenes del canvas
        if (props.canvasImages && props.canvasImages.length > 0) {
          // Ordenar por zIndex
          const sortedImages = [...props.canvasImages].sort((a, b) => a.zIndex - b.zIndex);
          
          for (const img of sortedImages) {
            // Cargar y cachear cada imagen
            if (!img._cachedImg || img._cachedImg._src !== img.url) {
              try {
                const loadedImg = await loadImage(img.url);
                loadedImg._src = img.url;
                img._cachedImg = loadedImg;
                
                // Guardar la relación de aspecto original de la imagen
                if (!img.originalAspectRatio) {
                  img.originalAspectRatio = loadedImg.naturalWidth / loadedImg.naturalHeight;
                }
              } catch (error) {
                console.error('Error cargando imagen:', img.url, error);
                continue; // Saltar esta imagen si falla
              }
            }
            
            // Verificar que la imagen esté cargada y sea válida antes de dibujar
            if (!img._cachedImg || !(img._cachedImg instanceof HTMLImageElement)) {
              console.warn('Imagen no válida, saltando:', img.url);
              continue;
            }
            
            // Verificar que la imagen esté completamente cargada
            if (!img._cachedImg.complete || img._cachedImg.naturalWidth === 0) {
              console.warn('Imagen no completamente cargada, saltando:', img.url);
              continue;
            }
            
            // Dibujar la imagen (optimizado para fast render)
            try {
              if (fastRender && !img.rotation) {
                // Modo rápido: sin transformaciones
                ctx.drawImage(img._cachedImg, img.x, img.y, img.width, img.height);
              } else {
                // Modo normal: con rotación
                ctx.save();
                ctx.translate(img.x + img.width / 2, img.y + img.height / 2);
                ctx.rotate((img.rotation || 0) * Math.PI / 180);
                ctx.drawImage(img._cachedImg, -img.width / 2, -img.height / 2, img.width, img.height);
                ctx.restore();
              }
            } catch (error) {
              console.error('Error dibujando imagen:', img.url, error);
              continue;
            }
            
            // Dibujar handles de resize solo si no estamos en modo fast render
            if (!fastRender && !isDragging.value && !isResizing.value) {
              drawResizeHandles(ctx, img.x, img.y, img.width, img.height);
            }
          }
        }

        // Textos (solo si no es fast render)
        if (!fastRender) {
          const textsConfig = props.template?.layout_config?.texts || {};

          // Headline
          if (props.texts.headline && textsConfig.headline) {
            const cfg = textsConfig.headline;
            const x = textPositions.value.headline?.x ?? cfg.x ?? 50;
            const y = textPositions.value.headline?.y ?? cfg.y ?? 80;
            
            // Inicializar posición si no existe
            if (!textPositions.value.headline) {
              textPositions.value.headline = { x, y, width: 0, height: 0 };
            }
            
            ctx.font = `${cfg.weight || 'bold'} ${cfg.size || 48}px Inter, sans-serif`;
            ctx.fillStyle = cfg.color || '#ffffff';
            ctx.textAlign = cfg.align || 'left';
            ctx.fillText(props.texts.headline, x, y);
            
            // Calcular dimensiones del texto para detección de clicks
            const metrics = ctx.measureText(props.texts.headline);
            textPositions.value.headline.width = metrics.width;
            textPositions.value.headline.height = cfg.size || 48;
          }

          // Subheadline
          if (props.texts.subheadline && textsConfig.subheadline) {
            const cfg = textsConfig.subheadline;
            const x = textPositions.value.subheadline?.x ?? cfg.x ?? 50;
            const y = textPositions.value.subheadline?.y ?? cfg.y ?? 130;
            
            if (!textPositions.value.subheadline) {
              textPositions.value.subheadline = { x, y, width: 0, height: 0 };
            }
            
            ctx.font = `${cfg.weight || 'normal'} ${cfg.size || 24}px Inter, sans-serif`;
            ctx.fillStyle = cfg.color || 'rgba(255,255,255,0.8)';
            ctx.textAlign = cfg.align || 'left';
            ctx.fillText(props.texts.subheadline, x, y);
            
            const metrics = ctx.measureText(props.texts.subheadline);
            textPositions.value.subheadline.width = metrics.width;
            textPositions.value.subheadline.height = cfg.size || 24;
          }

          // CTA
          if (props.texts.cta && textsConfig.cta) {
            const cfg = textsConfig.cta;
            const x = textPositions.value.cta?.x ?? cfg.x ?? 50;
            const y = textPositions.value.cta?.y ?? cfg.y ?? 580;
            
            if (!textPositions.value.cta) {
              textPositions.value.cta = { x, y, width: 0, height: 0 };
            }

            // Botón de fondo
            if (cfg.background) {
              ctx.fillStyle = cfg.background;
              const padding = cfg.padding || 20;
              const radius = cfg.border_radius || 8;
              const bgX = x - padding;
              const bgY = y - (cfg.size || 18) - padding / 2;
              const bgWidth = ctx.measureText(props.texts.cta).width + padding * 2;
              const bgHeight = (cfg.size || 18) + padding;

              // Polyfill para roundRect en navegadores antiguos
              ctx.beginPath();
              if (ctx.roundRect) {
                ctx.roundRect(bgX, bgY, bgWidth, bgHeight, radius);
              } else {
                // Fallback manual para navegadores sin soporte
                ctx.moveTo(bgX + radius, bgY);
                ctx.lineTo(bgX + bgWidth - radius, bgY);
                ctx.arcTo(bgX + bgWidth, bgY, bgX + bgWidth, bgY + radius, radius);
                ctx.lineTo(bgX + bgWidth, bgY + bgHeight - radius);
                ctx.arcTo(bgX + bgWidth, bgY + bgHeight, bgX + bgWidth - radius, bgY + bgHeight, radius);
                ctx.lineTo(bgX + radius, bgY + bgHeight);
                ctx.arcTo(bgX, bgY + bgHeight, bgX, bgY + bgHeight - radius, radius);
                ctx.lineTo(bgX, bgY + radius);
                ctx.arcTo(bgX, bgY, bgX + radius, bgY, radius);
                ctx.closePath();
              }
              ctx.fill();
            }

            ctx.font = `${cfg.weight || '600'} ${cfg.size || 18}px Inter, sans-serif`;
            ctx.fillStyle = cfg.color || '#ffffff';
            ctx.textAlign = cfg.align || 'left';
            ctx.fillText(props.texts.cta, x, y);
            
            const metrics = ctx.measureText(props.texts.cta);
            textPositions.value.cta.width = metrics.width;
            textPositions.value.cta.height = cfg.size || 18;
          }
          
          // Dibujar indicadores de textos si no se está arrastrando
          if (!isDragging.value && !isResizing.value && !isDraggingText.value) {
            drawTextIndicators(ctx, textsConfig);
          }
        } // Cierre del if (!fastRender)

        if (!fastRender) {
          emit('rendered', canvas.value.toDataURL('image/png'));
        }
      } catch (error) {
        console.error('Error renderizando mockup:', error);
        emit('error', error);
      } finally {
        if (showLoading) {
          isLoading.value = false;
        }
      }
    };

    // Calcular zoom óptimo para que el canvas quepa en el contenedor
    const calculateOptimalZoom = () => {
      if (!canvasContainer.value) return minZoom;
      
      const containerWidth = canvasContainer.value.clientWidth;
      const containerHeight = canvasContainer.value.clientHeight;
      
      // Calcular zoom para que quepa el ancho (80% para dejar margen)
      const zoomByWidth = (containerWidth * 0.80) / canvasWidth.value;
      // Calcular zoom para que quepa el alto (75% para dejar espacio a los controles)
      const zoomByHeight = (containerHeight * 0.75) / canvasHeight.value;
      
      // Usar el menor para que quepa completamente
      const optimalZoom = Math.min(zoomByWidth, zoomByHeight);
      
      // No permitir menos del mínimo
      return Math.max(optimalZoom, minZoom);
    };

    // Controles de zoom
    const zoomIn = () => {
      zoom.value = Math.min(zoom.value + 0.05, 1);
    };

    const zoomOut = () => {
      zoom.value = Math.max(zoom.value - 0.05, minZoom);
    };

    const resetZoom = () => {
      // Calcular zoom óptimo basado en el tamaño del contenedor
      zoom.value = calculateOptimalZoom();
      offset.value = { x: 0, y: 0 };
    };
    
    // Controles de paneo (mover el canvas)
    const panStep = 100; // pixels por movimiento
    const panUp = () => {
      offset.value.y = Math.min(offset.value.y + panStep, canvasHeight.value * zoom.value / 2);
    };
    
    const panDown = () => {
      offset.value.y = Math.max(offset.value.y - panStep, -canvasHeight.value * zoom.value / 2);
    };
    
    const panLeft = () => {
      offset.value.x = Math.min(offset.value.x + panStep, canvasWidth.value * zoom.value / 2);
    };
    
    const panRight = () => {
      offset.value.x = Math.max(offset.value.x - panStep, -canvasWidth.value * zoom.value / 2);
    };

    // Dibujar handles de resize
    const drawResizeHandles = (ctx, x, y, width, height) => {
      // Tamaño constante en pantalla independiente del zoom
      const handleSize = 12 / zoom.value;
      const handles = [
        { x: x + width, y: y + height, cursor: 'nwse-resize', id: 'se' },
        { x: x, y: y + height, cursor: 'nesw-resize', id: 'sw' },
        { x: x + width, y: y, cursor: 'nesw-resize', id: 'ne' },
        { x: x, y: y, cursor: 'nwse-resize', id: 'nw' },
      ];

      ctx.fillStyle = '#6366f1';
      ctx.strokeStyle = '#ffffff';
      ctx.lineWidth = 2 / zoom.value;

      handles.forEach(handle => {
        ctx.fillRect(handle.x - handleSize/2, handle.y - handleSize/2, handleSize, handleSize);
        ctx.strokeRect(handle.x - handleSize/2, handle.y - handleSize/2, handleSize, handleSize);
      });
    };
    
    // Dibujar indicadores de textos
    const drawTextIndicators = (ctx, textsConfig) => {
      ctx.save();
      ctx.strokeStyle = '#22c55e';
      ctx.lineWidth = 2;
      ctx.setLineDash([5, 5]);
      
      Object.entries(textPositions.value).forEach(([key, pos]) => {
        if (pos && pos.width > 0) {
          // Dibujar rectángulo alrededor del texto
          const padding = 8;
          ctx.strokeRect(
            pos.x - padding, 
            pos.y - pos.height - padding, 
            pos.width + padding * 2, 
            pos.height + padding * 2
          );
        }
      });
      
      ctx.restore();
    };

    // Obtener el handle bajo el mouse
    const getResizeHandle = (mouseX, mouseY) => {
      if (!props.screenshotUrl || !screenshotPosition.value.width) return null;
      const { x, y, width, height } = screenshotPosition.value;
      const handleSize = 12;
      const tolerance = handleSize;

      const handles = [
        { x: x + width, y: y + height, cursor: 'nwse-resize', id: 'se' },
        { x: x, y: y + height, cursor: 'nesw-resize', id: 'sw' },
        { x: x + width, y: y, cursor: 'nesw-resize', id: 'ne' },
        { x: x, y: y, cursor: 'nwse-resize', id: 'nw' },
      ];

      for (const handle of handles) {
        if (Math.abs(mouseX - handle.x) <= tolerance && Math.abs(mouseY - handle.y) <= tolerance) {
          return handle;
        }
      }
      return null;
    };

    // Verificar si el mouse está sobre el screenshot
    const isMouseOverScreenshot = (mouseX, mouseY) => {
      if (!props.screenshotUrl || !screenshotPosition.value.width) return false;
      const { x, y, width, height } = screenshotPosition.value;
      return mouseX >= x && mouseX <= x + width && mouseY >= y && mouseY <= y + height;
    };
    
    // Verificar si el mouse está sobre un texto
    const getTextUnderMouse = (mouseX, mouseY) => {
      const padding = 8;
      for (const [key, pos] of Object.entries(textPositions.value)) {
        if (pos && pos.width > 0) {
          const x1 = pos.x - padding;
          const y1 = pos.y - pos.height - padding;
          const x2 = pos.x + pos.width + padding;
          const y2 = pos.y + padding;
          
          if (mouseX >= x1 && mouseX <= x2 && mouseY >= y1 && mouseY <= y2) {
            return key;
          }
        }
      }
      return null;
    };

    // Manejo de arrastre del screenshot
    const handleMouseDown = (e) => {
      if (!canvas.value) return;
      const rect = canvas.value.getBoundingClientRect();
      const canvasDisplayWidth = canvasWidth.value * zoom.value;
      const canvasDisplayHeight = canvasHeight.value * zoom.value;
      const offsetX = (rect.width - canvasDisplayWidth) / 2;
      const offsetY = (rect.height - canvasDisplayHeight) / 2;
      
      const mouseX = (e.clientX - rect.left - offsetX) / zoom.value;
      const mouseY = (e.clientY - rect.top - offsetY) / zoom.value;
      
      // Verificar si está sobre un texto
      const textKey = getTextUnderMouse(mouseX, mouseY);
      if (textKey && props.texts[textKey]) {
        isDraggingText.value = true;
        selectedTextKey.value = textKey;
        const pos = textPositions.value[textKey];
        dragStart.value = { 
          x: mouseX - pos.x, 
          y: mouseY - pos.y 
        };
        canvasCursor.value = 'move';
        return;
      }
      
      // Verificar imágenes del canvas (de mayor a menor zIndex)
      if (props.canvasImages && props.canvasImages.length > 0) {
        const sortedImages = [...props.canvasImages].sort((a, b) => b.zIndex - a.zIndex);
        
        for (let i = 0; i < sortedImages.length; i++) {
          const img = sortedImages[i];
          const imgIndex = props.canvasImages.indexOf(img);
          
          // Verificar handles de resize primero (tamaño constante en pantalla)
          const handleSize = 8 / zoom.value;
          const corners = [
            { id: 'nw', x: img.x, y: img.y, cursor: 'nwse-resize' },
            { id: 'ne', x: img.x + img.width, y: img.y, cursor: 'nesw-resize' },
            { id: 'sw', x: img.x, y: img.y + img.height, cursor: 'nesw-resize' },
            { id: 'se', x: img.x + img.width, y: img.y + img.height, cursor: 'nwse-resize' }
          ];
          
          for (const corner of corners) {
            const dx = mouseX - corner.x;
            const dy = mouseY - corner.y;
            const distance = Math.sqrt(dx * dx + dy * dy);
            
            if (distance <= handleSize) {
              isResizing.value = true;
              resizeHandle.value = corner.id;
              selectedImageIndex.value = imgIndex;
              dragStart.value = { x: mouseX, y: mouseY };
              dragStart.value.initialPos = { ...img };
              canvasCursor.value = corner.cursor;
              
              // Preparar el cache del fondo ANTES de empezar el resize
              prepareBaseCache();
              return;
            }
          }
          
          // Verificar si está dentro de la imagen
          if (mouseX >= img.x && mouseX <= img.x + img.width &&
              mouseY >= img.y && mouseY <= img.y + img.height) {
            isDragging.value = true;
            selectedImageIndex.value = imgIndex;
            dragStart.value = { 
              x: mouseX - img.x, 
              y: mouseY - img.y 
            };
            canvasCursor.value = 'grabbing';
            
            // Preparar el cache del fondo ANTES de empezar el drag
            prepareBaseCache();
            return;
          }
        }
      }
    };

    const handleMouseMove = (e) => {
      if (!canvas.value) return;
      
      const rect = canvas.value.getBoundingClientRect();
      const canvasDisplayWidth = canvasWidth.value * zoom.value;
      const canvasDisplayHeight = canvasHeight.value * zoom.value;
      const offsetX = (rect.width - canvasDisplayWidth) / 2;
      const offsetY = (rect.height - canvasDisplayHeight) / 2;
      
      const mouseX = (e.clientX - rect.left - offsetX) / zoom.value;
      const mouseY = (e.clientY - rect.top - offsetY) / zoom.value;
      
      if (isDraggingText.value) {
        // Mover texto
        const textKey = selectedTextKey.value;
        if (textKey && textPositions.value[textKey]) {
          textPositions.value[textKey].x = mouseX - dragStart.value.x;
          textPositions.value[textKey].y = mouseY - dragStart.value.y;
          
          if (!renderPending.value) {
            renderPending.value = true;
            requestAnimationFrame(() => {
              render();
              renderPending.value = false;
            });
          }
        }
      } else if (isResizing.value && selectedImageIndex.value !== null) {
        // Resize de imagen del canvas
        const img = props.canvasImages[selectedImageIndex.value];
        
        // Usar la relación de aspecto original de la imagen, no del resize actual
        const aspectRatio = img.originalAspectRatio || (img.width / img.height);
        
        if (isShiftPressed.value) {
          // Mantener relación de aspecto original de la imagen
          // Calcular basado en la posición actual del mouse
          switch (resizeHandle.value) {
            case 'se':
              const newWidth = Math.max(50, mouseX - img.x);
              img.width = newWidth;
              img.height = newWidth / aspectRatio;
              break;
            case 'sw':
              const newWidthSW = Math.max(50, img.x + img.width - mouseX);
              const oldRight = img.x + img.width;
              img.width = newWidthSW;
              img.height = newWidthSW / aspectRatio;
              img.x = oldRight - img.width;
              break;
            case 'ne':
              const newWidthNE = Math.max(50, mouseX - img.x);
              const oldBottom = img.y + img.height;
              img.width = newWidthNE;
              img.height = newWidthNE / aspectRatio;
              img.y = oldBottom - img.height;
              break;
            case 'nw':
              const newWidthNW = Math.max(50, img.x + img.width - mouseX);
              const oldRightNW = img.x + img.width;
              const oldBottomNW = img.y + img.height;
              img.width = newWidthNW;
              img.height = newWidthNW / aspectRatio;
              img.x = oldRightNW - img.width;
              img.y = oldBottomNW - img.height;
              break;
          }
        } else {
          // Resize libre basado en posición del mouse
          switch (resizeHandle.value) {
            case 'se':
              img.width = Math.max(50, mouseX - img.x);
              img.height = Math.max(50, mouseY - img.y);
              break;
            case 'sw':
              const oldRightSW = img.x + img.width;
              img.width = Math.max(50, img.x + img.width - mouseX);
              img.height = Math.max(50, mouseY - img.y);
              img.x = oldRightSW - img.width;
              break;
            case 'ne':
              const oldBottomNE = img.y + img.height;
              img.width = Math.max(50, mouseX - img.x);
              img.height = Math.max(50, img.y + img.height - mouseY);
              img.y = oldBottomNE - img.height;
              break;
            case 'nw':
              const oldRightNW2 = img.x + img.width;
              const oldBottomNW2 = img.y + img.height;
              img.width = Math.max(50, img.x + img.width - mouseX);
              img.height = Math.max(50, img.y + img.height - mouseY);
              img.x = oldRightNW2 - img.width;
              img.y = oldBottomNW2 - img.height;
              break;
          }
        }
        
        // Render con RAF optimizado
        if (!rafId) {
          rafId = requestAnimationFrame(() => {
            if (!isRendering) {
              isRendering = true;
              renderWithCache();
              isRendering = false;
            }
            rafId = null;
          });
        }
      } else if (isDragging.value && selectedImageIndex.value !== null) {
        // Mover imagen del canvas
        const img = props.canvasImages[selectedImageIndex.value];
        img.x = mouseX - dragStart.value.x;
        img.y = mouseY - dragStart.value.y;
        
        // Render con RAF optimizado
        if (!rafId) {
          rafId = requestAnimationFrame(() => {
            if (!isRendering) {
              isRendering = true;
              renderWithCache();
              isRendering = false;
            }
            rafId = null;
          });
        }
      } else if (e.target === canvas.value) {
        // Actualizar cursor según posición del mouse
        const textKey = getTextUnderMouse(mouseX, mouseY);
        if (textKey) {
          canvasCursor.value = 'move';
        } else if (props.canvasImages && props.canvasImages.length > 0) {
          let cursorSet = false;
          const sortedImages = [...props.canvasImages].sort((a, b) => b.zIndex - a.zIndex);
          
          for (const img of sortedImages) {
            const handleSize = 8 / zoom.value;
            const corners = [
              { x: img.x, y: img.y, cursor: 'nwse-resize' },
              { x: img.x + img.width, y: img.y, cursor: 'nesw-resize' },
              { x: img.x, y: img.y + img.height, cursor: 'nesw-resize' },
              { x: img.x + img.width, y: img.y + img.height, cursor: 'nwse-resize' }
            ];
            
            for (const corner of corners) {
              const dx = mouseX - corner.x;
              const dy = mouseY - corner.y;
              const distance = Math.sqrt(dx * dx + dy * dy);
              
              if (distance <= handleSize) {
                canvasCursor.value = corner.cursor;
                cursorSet = true;
                break;
              }
            }
            
            if (cursorSet) break;
            
            if (mouseX >= img.x && mouseX <= img.x + img.width &&
                mouseY >= img.y && mouseY <= img.y + img.height) {
              canvasCursor.value = 'grab';
              cursorSet = true;
              break;
            }
          }
          
          if (!cursorSet) {
            canvasCursor.value = 'default';
          }
        } else {
          canvasCursor.value = 'default';
        }
      }
    };

    const handleMouseUp = () => {
      if (isDragging.value || isResizing.value || isDraggingText.value) {
        // Cancelar RAF pendiente
        if (rafId) {
          cancelAnimationFrame(rafId);
          rafId = null;
        }
        
        isDragging.value = false;
        isResizing.value = false;
        isDraggingText.value = false;
        resizeHandle.value = null;
        selectedTextKey.value = null;
        selectedImageIndex.value = null;
        isRendering = false;
        
        // Limpiar cache y render final completo
        baseCanvas = null;
        baseNeedsUpdate = true;
        render();
      }
      canvasCursor.value = 'default';
    };

    // Observar cambios en las props para re-renderizar
    watch(
      () => props.template,
      (newTemplate, oldTemplate) => {
        nextTick(() => render(true));
      },
      { deep: true }
    );

    // Watch para otros cambios
    watch(
      () => [props.canvasImages, props.texts, props.backgroundColor],
      () => {
        nextTick(() => render(false));
      },
      { deep: true }
    );

    // Manejo de teclas
    const handleKeyDown = (e) => {
      // Detectar tecla Shift
      if (e.key === 'Shift') {
        isShiftPressed.value = true;
        // Re-renderizar si estamos resizing para mostrar el cambio
        if (isResizing.value) {
          render();
        }
        return;
      }
      
      // Solo actuar si el zoom es suficiente para necesitar navegación
      if (zoom.value <= 0.3) return;
      
      switch(e.key) {
        case 'ArrowUp':
          e.preventDefault();
          panUp();
          break;
        case 'ArrowDown':
          e.preventDefault();
          panDown();
          break;
        case 'ArrowLeft':
          e.preventDefault();
          panLeft();
          break;
        case 'ArrowRight':
          e.preventDefault();
          panRight();
          break;
      }
    };

    const handleKeyUp = (e) => {
      if (e.key === 'Shift') {
        isShiftPressed.value = false;
        // Re-renderizar si estamos resizing para mostrar el cambio
        if (isResizing.value) {
          render();
        }
      }
    };

    onMounted(() => {
      // Calcular zoom óptimo inicial
      nextTick(() => {
        zoom.value = calculateOptimalZoom();
        render(true);
      });
      
      // Agregar event listeners globales para mejor manejo del drag
      document.addEventListener('mousemove', handleMouseMove);
      document.addEventListener('mouseup', handleMouseUp);
      document.addEventListener('keydown', handleKeyDown);
      document.addEventListener('keyup', handleKeyUp);
      
      // Recalcular zoom si la ventana cambia de tamaño
      window.addEventListener('resize', () => {
        zoom.value = calculateOptimalZoom();
      });
    });

    onUnmounted(() => {
      // Limpiar event listeners
      document.removeEventListener('mousemove', handleMouseMove);
      document.removeEventListener('mouseup', handleMouseUp);
      document.removeEventListener('keydown', handleKeyDown);
      document.removeEventListener('keyup', handleKeyUp);
      window.removeEventListener('resize', calculateOptimalZoom);
    });

    return {
      canvas,
      canvasContainer,
      canvasWidth,
      canvasHeight,
      zoom,
      offset,
      isLoading,
      canvasCursor,
      zoomIn,
      zoomOut,
      resetZoom,
      panUp,
      panDown,
      panLeft,
      panRight,
      handleMouseDown,
      handleMouseMove,
      handleMouseUp,
      render,
    };
  },
};
</script>

<style scoped>
.mockup-canvas {
  position: relative;
  display: flex;
  align-items: center;
  justify-content: center;
  background: #0f172a;
  border-radius: 12px;
  overflow: hidden;
  height: calc(100vh - 120px);
  width: 100%;
}

canvas {
  border-radius: 8px;
  transition: transform 0.2s ease;
}

.canvas-controls {
  position: absolute;
  bottom: 16px;
  left: 50%;
  transform: translateX(-50%);
  display: flex;
  align-items: center;
  gap: 8px;
  padding: 8px 16px;
  background: rgba(30, 41, 59, 0.9);
  backdrop-filter: blur(8px);
  border-radius: 999px;
  border: 1px solid #334155;
}

.canvas-controls button {
  display: flex;
  align-items: center;
  justify-content: center;
  width: 32px;
  height: 32px;
  background: transparent;
  border: none;
  border-radius: 8px;
  color: #94a3b8;
  cursor: pointer;
  transition: all 0.2s ease;
}

.canvas-controls button:hover {
  background: #334155;
  color: #f8fafc;
}

.zoom-level {
  font-size: 0.75rem;
  color: #94a3b8;
  min-width: 40px;
  text-align: center;
}

.canvas-loading {
  position: absolute;
  inset: 0;
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  gap: 12px;
  background: rgba(15, 23, 42, 0.8);
  backdrop-filter: blur(4px);
}

.loading-spinner {
  width: 32px;
  height: 32px;
  border: 3px solid #334155;
  border-top-color: #6366f1;
  border-radius: 50%;
  animation: spin 1s linear infinite;
}

@keyframes spin {
  to { transform: rotate(360deg); }
}

.canvas-loading span {
  color: #94a3b8;
  font-size: 0.875rem;
}

.pan-controls {
  position: absolute;
  top: 16px;
  right: 16px;
  display: grid;
  grid-template-columns: repeat(3, 40px);
  grid-template-rows: repeat(3, 40px);
  gap: 4px;
}

.pan-btn {
  display: flex;
  align-items: center;
  justify-content: center;
  background: rgba(30, 41, 59, 0.9);
  backdrop-filter: blur(8px);
  border: 1px solid #334155;
  border-radius: 8px;
  color: #94a3b8;
  cursor: pointer;
  transition: all 0.2s ease;
}

.pan-btn:hover {
  background: #334155;
  color: #f8fafc;
  border-color: #475569;
}

.pan-up {
  grid-column: 2;
  grid-row: 1;
}

.pan-left {
  grid-column: 1;
  grid-row: 2;
}

.pan-right {
  grid-column: 3;
  grid-row: 2;
}

.pan-down {
  grid-column: 2;
  grid-row: 3;
}
</style>
