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
      type: Array,
      default: () => [],
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
    const isRotating = ref(false);
    const rotationStart = ref({ angle: 0, mouseAngle: 0 });
    const isDraggingText = ref(false);
    const selectedTextKey = ref(null);
    const selectedImageIndex = ref(null); // Índice de la imagen seleccionada
    const selectedTextItem = ref(null); // Texto seleccionado para rotación
    const selectedTextForResize = ref(null); // Texto seleccionado para resize
    const isResizingText = ref(false);
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
          
          for (let i = 0; i < sortedImages.length; i++) {
            const img = sortedImages[i];
            const imgIndex = props.canvasImages.indexOf(img);
            
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
            
            // Dibujar la imagen
            try {
              ctx.save();
              if (img.rotation && img.rotation !== 0) {
                // Con rotación
                ctx.translate(img.x + img.width / 2, img.y + img.height / 2);
                ctx.rotate(img.rotation * Math.PI / 180);
                ctx.drawImage(img._cachedImg, -img.width / 2, -img.height / 2, img.width, img.height);
              } else {
                // Sin rotación (más simple)
                ctx.drawImage(img._cachedImg, img.x, img.y, img.width, img.height);
              }
              ctx.restore();
            } catch (error) {
              console.error('Error dibujando imagen:', img.url, error);
              continue;
            }
            
            // Dibujar handles solo si esta imagen está seleccionada
            if (!fastRender && !isDragging.value && !isResizing.value && !isRotating.value && selectedImageIndex.value === imgIndex) {
              drawResizeHandles(ctx, img.x, img.y, img.width, img.height, img.rotation || 0);
            }
          }
        }

        // Textos (siempre renderizar, pero handles solo si no es fast render)
        const textsConfig = props.template?.layout_config?.texts || {};

        // Renderizar textos dinámicos
        if (props.texts && Array.isArray(props.texts)) {
          props.texts.forEach((textItem, index) => {
            if (!textItem.content) return;
            
            // Posición por defecto escalonada verticalmente
            const defaultX = 50;
            const defaultY = 80 + (index * 60);
            
            const x = textItem.x ?? defaultX;
            const y = textItem.y ?? defaultY;
            const rotation = textItem.rotation || 0;
            
            // Aplicar estilos del texto
            const fontStyle = textItem.fontStyle || 'normal';
            const fontWeight = textItem.fontWeight || 'normal';
            const fontSize = textItem.fontSize || 24;
            const fontFamily = textItem.fontFamily || 'Arial';
            const color = textItem.color || '#000000';
            
            // Medir texto antes de rotar
            ctx.font = `${fontStyle} ${fontWeight} ${fontSize}px ${fontFamily}, sans-serif`;
            const metrics = ctx.measureText(textItem.content);
            const textWidth = metrics.width;
            const textHeight = fontSize;
            
            // Aplicar rotación si existe
            ctx.save();
            if (rotation !== 0) {
              ctx.translate(x + textWidth / 2, y - textHeight / 2);
              ctx.rotate(rotation * Math.PI / 180);
              ctx.translate(-textWidth / 2, textHeight / 2);
            } else {
              ctx.translate(x, y);
            }
            
            ctx.fillStyle = color;
            ctx.textAlign = 'left';
            ctx.fillText(textItem.content, 0, 0);
            ctx.restore();
            
            // Guardar dimensiones para detección de clicks
            if (!textPositions.value[`text_${textItem.id}`]) {
              textPositions.value[`text_${textItem.id}`] = {};
            }
            textPositions.value[`text_${textItem.id}`].x = x;
            textPositions.value[`text_${textItem.id}`].y = y;
            textPositions.value[`text_${textItem.id}`].width = textWidth;
            textPositions.value[`text_${textItem.id}`].height = textHeight;
            textPositions.value[`text_${textItem.id}`].rotation = rotation;
            
            // Dibujar handles solo si no es fast render y este texto está seleccionado
            if (!fastRender) {
              const isSelected = selectedTextKey.value === `text_${textItem.id}` || selectedTextItem.value?.id === textItem.id;
              if (!isDragging.value && !isResizing.value && !isRotating.value && !isResizingText.value && isSelected) {
                drawTextHandles(ctx, x, y, textWidth, textHeight, rotation);
              }
            }
          });
        }
        
        // Dibujar indicadores de textos solo si no es fast render y no se está arrastrando
        if (!fastRender && !isDragging.value && !isResizing.value && !isDraggingText.value) {
          drawTextIndicators(ctx, textsConfig);
        }

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
    const drawResizeHandles = (ctx, x, y, width, height, rotation = 0) => {
      // Tamaño constante en pantalla independiente del zoom
      const handleSize = 12 / zoom.value;
      
      // Centro de la imagen para rotación
      const centerX = x + width / 2;
      const centerY = y + height / 2;
      
      // Posiciones originales de las esquinas (relativas al centro)
      const corners = [
        { dx: width / 2, dy: height / 2, id: 'se' },    // sureste
        { dx: -width / 2, dy: height / 2, id: 'sw' },   // suroeste
        { dx: width / 2, dy: -height / 2, id: 'ne' },   // noreste
        { dx: -width / 2, dy: -height / 2, id: 'nw' },  // noroeste
      ];
      
      ctx.save();
      ctx.fillStyle = '#6366f1';
      ctx.strokeStyle = '#ffffff';
      ctx.lineWidth = 2 / zoom.value;
      
      // Aplicar rotación si existe
      if (rotation && rotation !== 0) {
        const rad = rotation * Math.PI / 180;
        const cos = Math.cos(rad);
        const sin = Math.sin(rad);
        
        corners.forEach(corner => {
          // Rotar la posición del handle
          const rotatedX = corner.dx * cos - corner.dy * sin;
          const rotatedY = corner.dx * sin + corner.dy * cos;
          
          const handleX = centerX + rotatedX;
          const handleY = centerY + rotatedY;
          
          ctx.fillRect(handleX - handleSize/2, handleY - handleSize/2, handleSize, handleSize);
          ctx.strokeRect(handleX - handleSize/2, handleY - handleSize/2, handleSize, handleSize);
        });
      } else {
        // Sin rotación, dibujar en las esquinas directas
        corners.forEach(corner => {
          const handleX = centerX + corner.dx;
          const handleY = centerY + corner.dy;
          
          ctx.fillRect(handleX - handleSize/2, handleY - handleSize/2, handleSize, handleSize);
          ctx.strokeRect(handleX - handleSize/2, handleY - handleSize/2, handleSize, handleSize);
        });
      }
      
      ctx.restore();
      
      // Dibujar handle de rotación (arriba del centro)
      drawRotateHandle(ctx, x, y, width, height, rotation, 'image');
    };
    
    // Dibujar handle de rotación
    const drawRotateHandle = (ctx, x, y, width, height, rotation = 0, type = 'image') => {
      const handleSize = 10 / zoom.value;
      const lineLength = 30 / zoom.value;
      
      // Centro del elemento
      const centerX = x + width / 2;
      const centerY = y + (type === 'text' ? -height / 2 : height / 2);
      
      // Calcular posición del handle rotado (arriba del centro)
      let handleX = centerX;
      let handleY = centerY - (type === 'text' ? height / 2 : height / 2) - lineLength;
      let lineStartY = centerY - (type === 'text' ? height / 2 : height / 2);
      
      // Si hay rotación, rotar el handle
      if (rotation && rotation !== 0) {
        const rad = rotation * Math.PI / 180;
        const cos = Math.cos(rad);
        const sin = Math.sin(rad);
        
        // Distancia desde el centro al handle
        const distanceY = -(type === 'text' ? height / 2 : height / 2) - lineLength;
        
        // Rotar la posición del handle
        handleX = centerX + (0 * cos - distanceY * sin);
        handleY = centerY + (0 * sin + distanceY * cos);
        
        // Punto de inicio de la línea (borde superior del elemento)
        const lineDistanceY = -(type === 'text' ? height / 2 : height / 2);
        lineStartY = centerY + lineDistanceY * cos;
        const lineStartX = centerX - lineDistanceY * sin;
        
        ctx.save();
        
        // Dibujar línea conectora
        ctx.strokeStyle = '#6366f1';
        ctx.lineWidth = 2 / zoom.value;
        ctx.setLineDash([]);
        ctx.beginPath();
        ctx.moveTo(lineStartX, lineStartY);
        ctx.lineTo(handleX, handleY);
        ctx.stroke();
      } else {
        ctx.save();
        
        // Dibujar línea conectora sin rotación
        ctx.strokeStyle = '#6366f1';
        ctx.lineWidth = 2 / zoom.value;
        ctx.setLineDash([]);
        ctx.beginPath();
        ctx.moveTo(centerX, lineStartY);
        ctx.lineTo(handleX, handleY);
        ctx.stroke();
      }
      
      // Dibujar círculo de rotación
      ctx.fillStyle = '#22c55e';
      ctx.strokeStyle = '#ffffff';
      ctx.lineWidth = 2 / zoom.value;
      ctx.beginPath();
      ctx.arc(handleX, handleY, handleSize / 2, 0, Math.PI * 2);
      ctx.fill();
      ctx.stroke();
      
      // Dibujar icono de rotación dentro del círculo
      ctx.strokeStyle = '#ffffff';
      ctx.lineWidth = 1.5 / zoom.value;
      const iconRadius = handleSize / 3;
      ctx.beginPath();
      ctx.arc(handleX, handleY, iconRadius, 0.2, Math.PI * 1.8);
      ctx.stroke();
      // Flecha
      const arrowSize = iconRadius / 2;
      ctx.beginPath();
      ctx.moveTo(handleX - iconRadius * 0.7, handleY - iconRadius * 0.7);
      ctx.lineTo(handleX - iconRadius * 0.7 - arrowSize, handleY - iconRadius * 0.7);
      ctx.lineTo(handleX - iconRadius * 0.7, handleY - iconRadius * 0.7 - arrowSize);
      ctx.stroke();
      
      ctx.restore();
    };
    
    // Dibujar handles completos para texto (resize + rotación)
    const drawTextHandles = (ctx, x, y, width, height, rotation = 0) => {
      // Dibujar borde de selección
      ctx.save();
      ctx.strokeStyle = '#6366f1';
      ctx.lineWidth = 2 / zoom.value;
      ctx.setLineDash([5 / zoom.value, 5 / zoom.value]);
      ctx.strokeRect(x - 4 / zoom.value, y - height - 4 / zoom.value, width + 8 / zoom.value, height + 8 / zoom.value);
      ctx.restore();
      
      // Dibujar handles de resize en las esquinas
      const handleSize = 12 / zoom.value;
      const corners = [
        { x: x + width, y: y, cursor: 'nwse-resize', id: 'se' },
        { x: x, y: y, cursor: 'nesw-resize', id: 'sw' },
        { x: x + width, y: y - height, cursor: 'nesw-resize', id: 'ne' },
        { x: x, y: y - height, cursor: 'nwse-resize', id: 'nw' },
      ];

      ctx.fillStyle = '#6366f1';
      ctx.strokeStyle = '#ffffff';
      ctx.lineWidth = 2 / zoom.value;

      corners.forEach(handle => {
        ctx.fillRect(handle.x - handleSize/2, handle.y - handleSize/2, handleSize, handleSize);
        ctx.strokeRect(handle.x - handleSize/2, handle.y - handleSize/2, handleSize, handleSize);
      });
      
      // Dibujar handle de rotación
      drawRotateHandle(ctx, x, y, width, height, rotation, 'text');
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
      if (!props.texts || !Array.isArray(props.texts)) return null;
      
      const padding = 8;
      // Recorrer en reversa para priorizar los textos superiores (últimos dibujados)
      for (let i = props.texts.length - 1; i >= 0; i--) {
        const textItem = props.texts[i];
        const textKey = `text_${textItem.id}`;
        const pos = textPositions.value[textKey];
        
        if (pos && pos.width > 0) {
          const x1 = pos.x - padding;
          const y1 = pos.y - pos.height - padding;
          const x2 = pos.x + pos.width + padding;
          const y2 = pos.y + padding;
          
          if (mouseX >= x1 && mouseX <= x2 && mouseY >= y1 && mouseY <= y2) {
            return textKey;
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
      
      // Verificar si está sobre un texto (recorrer en reversa para priorizar los últimos)
      if (props.texts && Array.isArray(props.texts)) {
        for (let i = props.texts.length - 1; i >= 0; i--) {
          const textItem = props.texts[i];
          const textKey = `text_${textItem.id}`;
          const pos = textPositions.value[textKey];
          
          if (pos) {
            // Si el texto está seleccionado, verificar handles
            const isSelected = selectedTextKey.value === textKey || selectedTextItem.value?.id === textItem.id;
            
            if (isSelected) {
              // Verificar handle de rotación primero
              const rotateHandleSize = 10 / zoom.value;
              const lineLength = 30 / zoom.value;
              const rotateCenterX = pos.x + pos.width / 2;
              const rotateCenterY = pos.y - pos.height - lineLength;
              let dx = mouseX - rotateCenterX;
              let dy = mouseY - rotateCenterY;
              let distance = Math.sqrt(dx * dx + dy * dy);
              
              if (distance <= rotateHandleSize) {
                isRotating.value = true;
                selectedTextItem.value = textItem;
                selectedTextKey.value = textKey;
                const centerX = pos.x + pos.width / 2;
                const centerY = pos.y - pos.height / 2;
                const angle = Math.atan2(mouseY - centerY, mouseX - centerX) * 180 / Math.PI;
                rotationStart.value = {
                  angle: textItem.rotation || 0,
                  mouseAngle: angle
                };
                canvasCursor.value = 'crosshair';
                return;
              }
              
              // Verificar handles de resize
              const resizeHandleSize = 12 / zoom.value;
              const corners = [
                { id: 'nw', x: pos.x, y: pos.y - pos.height, cursor: 'nwse-resize' },
                { id: 'ne', x: pos.x + pos.width, y: pos.y - pos.height, cursor: 'nesw-resize' },
                { id: 'sw', x: pos.x, y: pos.y, cursor: 'nesw-resize' },
                { id: 'se', x: pos.x + pos.width, y: pos.y, cursor: 'nwse-resize' }
              ];
              
              for (const corner of corners) {
                dx = mouseX - corner.x;
                dy = mouseY - corner.y;
                distance = Math.sqrt(dx * dx + dy * dy);
                
                if (distance <= resizeHandleSize / 2) {
                  isResizingText.value = true;
                  selectedTextForResize.value = textItem;
                  selectedTextKey.value = textKey;
                  resizeHandle.value = corner.id;
                  dragStart.value = { 
                    x: mouseX, 
                    y: mouseY,
                    initialFontSize: textItem.fontSize || 24,
                    initialWidth: pos.width,
                    initialHeight: pos.height
                  };
                  canvasCursor.value = corner.cursor;
                  return;
                }
              }
            }
            
            // Verificar si está dentro del texto (para selección o movimiento)
            if (mouseX >= pos.x && mouseX <= pos.x + pos.width &&
                mouseY >= pos.y - pos.height && mouseY <= pos.y) {
              if (isSelected) {
                // Si ya está seleccionado, mover
                isDraggingText.value = true;
                selectedTextKey.value = textKey;
                selectedTextItem.value = textItem;
                dragStart.value = { 
                  x: mouseX - pos.x, 
                  y: mouseY - pos.y 
                };
                canvasCursor.value = 'move';
              } else {
                // Si no está seleccionado, seleccionar
                selectedTextKey.value = textKey;
                selectedTextItem.value = textItem;
                render();
              }
              return;
            }
          }
        }
      }
      
      // Verificar imágenes del canvas (de mayor a menor zIndex)
      if (props.canvasImages && props.canvasImages.length > 0) {
        const sortedImages = [...props.canvasImages].sort((a, b) => b.zIndex - a.zIndex);
        
        for (let i = 0; i < sortedImages.length; i++) {
          const img = sortedImages[i];
          const imgIndex = props.canvasImages.indexOf(img);
          
          // Si la imagen está seleccionada, verificar handles
          const isSelected = selectedImageIndex.value === imgIndex;
          
          if (isSelected) {
            const centerX = img.x + img.width / 2;
            const centerY = img.y + img.height / 2;
            const rotation = img.rotation || 0;
            
            // Verificar handle de rotación primero (siempre en la parte superior rotada)
            const rotateHandleSize = 10 / zoom.value;
            const lineLength = 30 / zoom.value;
            
            let rotateCenterX = centerX;
            let rotateCenterY = centerY - img.height / 2 - lineLength;
            
            // Si hay rotación, rotar la posición del handle
            if (rotation && rotation !== 0) {
              const rad = rotation * Math.PI / 180;
              const cos = Math.cos(rad);
              const sin = Math.sin(rad);
              const distanceY = -img.height / 2 - lineLength;
              rotateCenterX = centerX + (0 * cos - distanceY * sin);
              rotateCenterY = centerY + (0 * sin + distanceY * cos);
            }
            
            let dx = mouseX - rotateCenterX;
            let dy = mouseY - rotateCenterY;
            let distance = Math.sqrt(dx * dx + dy * dy);
            
            if (distance <= rotateHandleSize) {
              isRotating.value = true;
              selectedImageIndex.value = imgIndex;
              const angle = Math.atan2(mouseY - centerY, mouseX - centerX) * 180 / Math.PI;
              rotationStart.value = {
                angle: rotation,
                mouseAngle: angle
              };
              canvasCursor.value = 'crosshair';
              return;
            }
            
            // Verificar handles de resize (en las esquinas rotadas)
            const handleSize = 12 / zoom.value;
            
            // Posiciones de las esquinas relativas al centro
            const cornerOffsets = [
              { dx: -img.width / 2, dy: -img.height / 2, id: 'nw', cursor: 'nwse-resize' },
              { dx: img.width / 2, dy: -img.height / 2, id: 'ne', cursor: 'nesw-resize' },
              { dx: -img.width / 2, dy: img.height / 2, id: 'sw', cursor: 'nesw-resize' },
              { dx: img.width / 2, dy: img.height / 2, id: 'se', cursor: 'nwse-resize' }
            ];
            
            // Aplicar rotación a las esquinas
            if (rotation && rotation !== 0) {
              const rad = rotation * Math.PI / 180;
              const cos = Math.cos(rad);
              const sin = Math.sin(rad);
              
              for (const corner of cornerOffsets) {
                const rotatedX = corner.dx * cos - corner.dy * sin;
                const rotatedY = corner.dx * sin + corner.dy * cos;
                const handleX = centerX + rotatedX;
                const handleY = centerY + rotatedY;
                
                dx = mouseX - handleX;
                dy = mouseY - handleY;
                distance = Math.sqrt(dx * dx + dy * dy);
                
                if (distance <= handleSize / 2) {
                  isResizing.value = true;
                  resizeHandle.value = corner.id;
                  selectedImageIndex.value = imgIndex;
                  dragStart.value = { x: mouseX, y: mouseY };
                  dragStart.value.initialPos = { ...img };
                  canvasCursor.value = corner.cursor;
                  return;
                }
              }
            } else {
              // Sin rotación, usar posiciones directas
              for (const corner of cornerOffsets) {
                const handleX = centerX + corner.dx;
                const handleY = centerY + corner.dy;
                
                dx = mouseX - handleX;
                dy = mouseY - handleY;
                distance = Math.sqrt(dx * dx + dy * dy);
                
                if (distance <= handleSize / 2) {
                  isResizing.value = true;
                  resizeHandle.value = corner.id;
                  selectedImageIndex.value = imgIndex;
                  dragStart.value = { x: mouseX, y: mouseY };
                  dragStart.value.initialPos = { ...img };
                  canvasCursor.value = corner.cursor;
                  return;
                }
              }
            }
          }
          
          // Verificar si está dentro de la imagen
          if (mouseX >= img.x && mouseX <= img.x + img.width &&
              mouseY >= img.y && mouseY <= img.y + img.height) {
            if (isSelected) {
              // Si ya está seleccionada, arrastrar
              isDragging.value = true;
              selectedImageIndex.value = imgIndex;
              dragStart.value = { 
                x: mouseX - img.x, 
                y: mouseY - img.y 
              };
              canvasCursor.value = 'grabbing';
            } else {
              // Si no está seleccionada, seleccionar
              selectedImageIndex.value = imgIndex;
              render();
            }
            return;
          }
        }
      }
      
      // Si llegamos aquí, el click fue en el canvas vacío -> deseleccionar todo
      if (selectedImageIndex.value !== null || selectedTextKey.value !== null || selectedTextItem.value !== null) {
        selectedImageIndex.value = null;
        selectedTextKey.value = null;
        selectedTextItem.value = null;
        selectedTextForResize.value = null;
        render();
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
      
      if (isRotating.value) {
        // Rotar imagen o texto
        if (selectedImageIndex.value !== null) {
          const img = props.canvasImages[selectedImageIndex.value];
          const centerX = img.x + img.width / 2;
          const centerY = img.y + img.height / 2;
          const currentAngle = Math.atan2(mouseY - centerY, mouseX - centerX) * 180 / Math.PI;
          let newRotation = rotationStart.value.angle + (currentAngle - rotationStart.value.mouseAngle);
          
          // Snap a ángulos de 15 grados si se mantiene Shift
          if (isShiftPressed.value) {
            newRotation = Math.round(newRotation / 15) * 15;
          }
          
          // Normalizar entre 0 y 360
          newRotation = ((newRotation % 360) + 360) % 360;
          img.rotation = newRotation;
        } else if (selectedTextItem.value) {
          const pos = textPositions.value[`text_${selectedTextItem.value.id}`];
          if (pos) {
            const centerX = pos.x + pos.width / 2;
            const centerY = pos.y - pos.height / 2;
            const currentAngle = Math.atan2(mouseY - centerY, mouseX - centerX) * 180 / Math.PI;
            let newRotation = rotationStart.value.angle + (currentAngle - rotationStart.value.mouseAngle);
            
            // Snap a ángulos de 15 grados si se mantiene Shift
            if (isShiftPressed.value) {
              newRotation = Math.round(newRotation / 15) * 15;
            }
            
            // Normalizar entre 0 y 360
            newRotation = ((newRotation % 360) + 360) % 360;
            selectedTextItem.value.rotation = newRotation;
          }
        }
        
        if (!renderPending.value) {
          renderPending.value = true;
          requestAnimationFrame(() => {
            render();
            renderPending.value = false;
          });
        }
      } else if (isDraggingText.value) {
        // Mover texto
        const textKey = selectedTextKey.value;
        if (textKey && textPositions.value[textKey]) {
          const newX = mouseX - dragStart.value.x;
          const newY = mouseY - dragStart.value.y;
          
          textPositions.value[textKey].x = newX;
          textPositions.value[textKey].y = newY;
          
          // Actualizar la posición en el textItem original
          if (props.texts && Array.isArray(props.texts)) {
            const textId = parseInt(textKey.replace('text_', ''));
            const textItem = props.texts.find(t => t.id === textId);
            if (textItem) {
              textItem.x = newX;
              textItem.y = newY;
            }
          }
          
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
        const rotation = img.rotation || 0;
        
        // Centro actual de la imagen
        const centerX = dragStart.value.initialPos.x + dragStart.value.initialPos.width / 2;
        const centerY = dragStart.value.initialPos.y + dragStart.value.initialPos.height / 2;
        
        // Calcular el vector desde el centro hasta el mouse
        const deltaX = mouseX - centerX;
        const deltaY = mouseY - centerY;
        
        // Si hay rotación, necesitamos transformar el movimiento del mouse al espacio rotado
        let localDeltaX = deltaX;
        let localDeltaY = deltaY;
        
        if (rotation && rotation !== 0) {
          const rad = -rotation * Math.PI / 180; // Negativo para invertir
          const cos = Math.cos(rad);
          const sin = Math.sin(rad);
          localDeltaX = deltaX * cos - deltaY * sin;
          localDeltaY = deltaX * sin + deltaY * cos;
        }
        
        // Usar la relación de aspecto original de la imagen
        const aspectRatio = img.originalAspectRatio || (dragStart.value.initialPos.width / dragStart.value.initialPos.height);
        
        // Calcular el desplazamiento del mouse desde el inicio del resize
        const mouseDeltaX = mouseX - dragStart.value.x;
        const mouseDeltaY = mouseY - dragStart.value.y;
        
        // Transformar el delta del mouse al espacio rotado si es necesario
        let localMouseDeltaX = mouseDeltaX;
        let localMouseDeltaY = mouseDeltaY;
        
        if (rotation && rotation !== 0) {
          const rad = -rotation * Math.PI / 180;
          const cos = Math.cos(rad);
          const sin = Math.sin(rad);
          localMouseDeltaX = mouseDeltaX * cos - mouseDeltaY * sin;
          localMouseDeltaY = mouseDeltaX * sin + mouseDeltaY * cos;
        }
        
        // Calcular nuevo tamaño basado en el handle y el delta del mouse
        let newWidth = dragStart.value.initialPos.width;
        let newHeight = dragStart.value.initialPos.height;
        
        if (isShiftPressed.value) {
          // Mantener relación de aspecto
          switch (resizeHandle.value) {
            case 'se':
              newWidth = Math.max(50, dragStart.value.initialPos.width + localMouseDeltaX * 2);
              newHeight = newWidth / aspectRatio;
              break;
            case 'sw':
              newWidth = Math.max(50, dragStart.value.initialPos.width - localMouseDeltaX * 2);
              newHeight = newWidth / aspectRatio;
              break;
            case 'ne':
              newWidth = Math.max(50, dragStart.value.initialPos.width + localMouseDeltaX * 2);
              newHeight = newWidth / aspectRatio;
              break;
            case 'nw':
              newWidth = Math.max(50, dragStart.value.initialPos.width - localMouseDeltaX * 2);
              newHeight = newWidth / aspectRatio;
              break;
          }
        } else {
          // Resize libre
          switch (resizeHandle.value) {
            case 'se':
              newWidth = Math.max(50, dragStart.value.initialPos.width + localMouseDeltaX * 2);
              newHeight = Math.max(50, dragStart.value.initialPos.height + localMouseDeltaY * 2);
              break;
            case 'sw':
              newWidth = Math.max(50, dragStart.value.initialPos.width - localMouseDeltaX * 2);
              newHeight = Math.max(50, dragStart.value.initialPos.height + localMouseDeltaY * 2);
              break;
            case 'ne':
              newWidth = Math.max(50, dragStart.value.initialPos.width + localMouseDeltaX * 2);
              newHeight = Math.max(50, dragStart.value.initialPos.height - localMouseDeltaY * 2);
              break;
            case 'nw':
              newWidth = Math.max(50, dragStart.value.initialPos.width - localMouseDeltaX * 2);
              newHeight = Math.max(50, dragStart.value.initialPos.height - localMouseDeltaY * 2);
              break;
          }
        }
        
        // Aplicar el nuevo tamaño manteniendo el centro fijo
        img.width = newWidth;
        img.height = newHeight;
        img.x = centerX - newWidth / 2;
        img.y = centerY - newHeight / 2;
        
        // Render con RAF optimizado
        if (!rafId) {
          rafId = requestAnimationFrame(() => {
            if (!isRendering) {
              isRendering = true;
              render();
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
              render();
              isRendering = false;
            }
            rafId = null;
          });
        }
      } else if (isResizingText.value && selectedTextForResize.value) {
        // Resize de texto (escalar fontSize)
        const deltaX = mouseX - dragStart.value.x;
        const deltaY = mouseY - dragStart.value.y;
        const distance = Math.sqrt(deltaX * deltaX + deltaY * deltaY);
        const initialDistance = Math.sqrt(
          dragStart.value.initialWidth * dragStart.value.initialWidth + 
          dragStart.value.initialHeight * dragStart.value.initialHeight
        );
        
        // Calcular factor de escala basado en la distancia
        let scaleFactor = distance / initialDistance;
        
        // Aplicar límites razonables
        scaleFactor = Math.max(0.3, Math.min(5, scaleFactor));
        
        // Calcular nuevo tamaño de fuente
        const newFontSize = Math.round(dragStart.value.initialFontSize * scaleFactor);
        selectedTextForResize.value.fontSize = Math.max(8, Math.min(200, newFontSize));
        
        // Render optimizado
        if (!renderPending.value) {
          renderPending.value = true;
          requestAnimationFrame(() => {
            render();
            renderPending.value = false;
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
            // Verificar handle de rotación
            const rotateHandleSize = 10 / zoom.value;
            const lineLength = 30 / zoom.value;
            const rotateCenterX = img.x + img.width / 2;
            const rotateCenterY = img.y - lineLength;
            let dx = mouseX - rotateCenterX;
            let dy = mouseY - rotateCenterY;
            let distance = Math.sqrt(dx * dx + dy * dy);
            
            if (distance <= rotateHandleSize) {
              canvasCursor.value = 'crosshair';
              cursorSet = true;
              break;
            }
            
            // Verificar handles de resize
            const handleSize = 8 / zoom.value;
            const corners = [
              { x: img.x, y: img.y, cursor: 'nwse-resize' },
              { x: img.x + img.width, y: img.y, cursor: 'nesw-resize' },
              { x: img.x, y: img.y + img.height, cursor: 'nesw-resize' },
              { x: img.x + img.width, y: img.y + img.height, cursor: 'nwse-resize' }
            ];
            
            for (const corner of corners) {
              dx = mouseX - corner.x;
              dy = mouseY - corner.y;
              distance = Math.sqrt(dx * dx + dy * dy);
              
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
      if (isDragging.value || isResizing.value || isDraggingText.value || isRotating.value || isResizingText.value) {
        // Cancelar RAF pendiente
        if (rafId) {
          cancelAnimationFrame(rafId);
          rafId = null;
        }
        
        isDragging.value = false;
        isResizing.value = false;
        isDraggingText.value = false;
        isRotating.value = false;
        isResizingText.value = false;
        resizeHandle.value = null;
        
        // NO limpiar selectedTextKey, selectedImageIndex, selectedTextItem
        // para mantener la selección después de soltar
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
