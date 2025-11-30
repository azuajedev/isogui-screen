<template>
  <div class="mockup-canvas" ref="canvasContainer">
    <!-- Canvas de renderizado -->
    <canvas 
      ref="canvas" 
      :width="canvasWidth" 
      :height="canvasHeight"
      @mousedown="handleMouseDown"
      @mousemove="handleMouseMove"
      @mouseup="handleMouseUp"
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

    <!-- Indicador de carga -->
    <div v-if="isLoading" class="canvas-loading">
      <div class="loading-spinner"></div>
      <span>Renderizando...</span>
    </div>
  </div>
</template>

<script>
import { ref, onMounted, watch, nextTick } from 'vue';

export default {
  name: 'MockupCanvas',

  props: {
    screenshotUrl: {
      type: String,
      default: null,
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
    const canvasHeight = ref(628);
    const zoom = ref(1);
    const isLoading = ref(false);
    const isDragging = ref(false);
    const dragStart = ref({ x: 0, y: 0 });
    const offset = ref({ x: 0, y: 0 });

    // Cargar imagen del screenshot
    const loadImage = (url) => {
      return new Promise((resolve, reject) => {
        const img = new Image();
        img.crossOrigin = 'anonymous';
        img.onload = () => resolve(img);
        img.onerror = reject;
        img.src = url;
      });
    };

    // Renderizar el mockup en el canvas
    const render = async () => {
      if (!canvas.value) return;

      isLoading.value = true;
      const ctx = canvas.value.getContext('2d');

      try {
        // Limpiar canvas
        ctx.clearRect(0, 0, canvasWidth.value, canvasHeight.value);

        // Fondo
        ctx.fillStyle = props.backgroundColor;
        ctx.fillRect(0, 0, canvasWidth.value, canvasHeight.value);

        // Aplicar gradiente si está configurado
        if (props.template?.layout_config?.canvas?.gradient) {
          const gradient = props.template.layout_config.canvas.gradient;
          const grd = gradient.direction === 'vertical'
            ? ctx.createLinearGradient(0, 0, 0, canvasHeight.value)
            : ctx.createLinearGradient(0, 0, canvasWidth.value, 0);

          grd.addColorStop(0, gradient.start || props.backgroundColor);
          grd.addColorStop(1, gradient.end || props.backgroundColor);
          ctx.fillStyle = grd;
          ctx.fillRect(0, 0, canvasWidth.value, canvasHeight.value);
        }

        // Screenshot
        if (props.screenshotUrl) {
          const screenshotImg = await loadImage(props.screenshotUrl);
          const config = props.template?.layout_config?.screenshot || {};

          const x = config.x || 100;
          const y = config.y || 100;
          const width = config.width || 400;
          const height = config.height || 400;

          // Sombra
          if (config.shadow?.enabled) {
            ctx.shadowColor = config.shadow.color || 'rgba(0,0,0,0.3)';
            ctx.shadowBlur = config.shadow.blur || 20;
            ctx.shadowOffsetX = config.shadow.offset_x || 0;
            ctx.shadowOffsetY = config.shadow.offset_y || 10;
          }

          // Dibujar screenshot
          ctx.drawImage(screenshotImg, x, y, width, height);

          // Resetear sombra
          ctx.shadowColor = 'transparent';
          ctx.shadowBlur = 0;
          ctx.shadowOffsetX = 0;
          ctx.shadowOffsetY = 0;
        }

        // Textos
        const textsConfig = props.template?.layout_config?.texts || {};

        // Headline
        if (props.texts.headline && textsConfig.headline) {
          const cfg = textsConfig.headline;
          ctx.font = `${cfg.weight || 'bold'} ${cfg.size || 48}px Inter, sans-serif`;
          ctx.fillStyle = cfg.color || '#ffffff';
          ctx.textAlign = cfg.align || 'left';
          ctx.fillText(props.texts.headline, cfg.x || 50, cfg.y || 80);
        }

        // Subheadline
        if (props.texts.subheadline && textsConfig.subheadline) {
          const cfg = textsConfig.subheadline;
          ctx.font = `${cfg.weight || 'normal'} ${cfg.size || 24}px Inter, sans-serif`;
          ctx.fillStyle = cfg.color || 'rgba(255,255,255,0.8)';
          ctx.textAlign = cfg.align || 'left';
          ctx.fillText(props.texts.subheadline, cfg.x || 50, cfg.y || 130);
        }

        // CTA
        if (props.texts.cta && textsConfig.cta) {
          const cfg = textsConfig.cta;

          // Botón de fondo
          if (cfg.background) {
            ctx.fillStyle = cfg.background;
            const padding = cfg.padding || 20;
            ctx.beginPath();
            ctx.roundRect(
              cfg.x - padding,
              cfg.y - cfg.size - padding / 2,
              ctx.measureText(props.texts.cta).width + padding * 2,
              cfg.size + padding,
              cfg.border_radius || 8
            );
            ctx.fill();
          }

          ctx.font = `${cfg.weight || '600'} ${cfg.size || 18}px Inter, sans-serif`;
          ctx.fillStyle = cfg.color || '#ffffff';
          ctx.textAlign = cfg.align || 'left';
          ctx.fillText(props.texts.cta, cfg.x || 50, cfg.y || 580);
        }

        emit('rendered', canvas.value.toDataURL('image/png'));
      } catch (error) {
        console.error('Error renderizando mockup:', error);
        emit('error', error);
      } finally {
        isLoading.value = false;
      }
    };

    // Controles de zoom
    const zoomIn = () => {
      zoom.value = Math.min(zoom.value + 0.1, 2);
    };

    const zoomOut = () => {
      zoom.value = Math.max(zoom.value - 0.1, 0.25);
    };

    const resetZoom = () => {
      zoom.value = 1;
      offset.value = { x: 0, y: 0 };
    };

    // Manejo de arrastre
    const handleMouseDown = (e) => {
      isDragging.value = true;
      dragStart.value = { x: e.clientX - offset.value.x, y: e.clientY - offset.value.y };
    };

    const handleMouseMove = (e) => {
      if (!isDragging.value) return;
      offset.value = {
        x: e.clientX - dragStart.value.x,
        y: e.clientY - dragStart.value.y,
      };
    };

    const handleMouseUp = () => {
      isDragging.value = false;
    };

    // Observar cambios en las props para re-renderizar
    watch(
      () => [props.screenshotUrl, props.template, props.texts, props.backgroundColor],
      () => {
        nextTick(render);
      },
      { deep: true }
    );

    onMounted(() => {
      render();
    });

    return {
      canvas,
      canvasContainer,
      canvasWidth,
      canvasHeight,
      zoom,
      isLoading,
      zoomIn,
      zoomOut,
      resetZoom,
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
  min-height: 400px;
}

canvas {
  max-width: 100%;
  height: auto;
  border-radius: 8px;
  cursor: grab;
  transition: transform 0.2s ease;
}

canvas:active {
  cursor: grabbing;
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
</style>
