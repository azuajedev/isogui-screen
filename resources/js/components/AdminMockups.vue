<template>
  <div class="admin-mockups">
    <div class="admin-header">
      <div class="header-left">
        <h1>Administración de Mockups</h1>
        <p class="subtitle">Gestiona las imágenes prediseñadas compartidas del sistema</p>
      </div>
      <button class="btn btn-primary" @click="showUploadModal = true">
        <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
          <path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/>
          <polyline points="17 8 12 3 7 8"/>
          <line x1="12" y1="3" x2="12" y2="15"/>
        </svg>
        Subir Mockup
      </button>
    </div>

    <!-- Estadísticas -->
    <div class="stats-grid">
      <div class="stat-card">
        <div class="stat-icon" style="background: #22c55e20; color: #22c55e;">
          <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
            <polyline points="22 12 18 12 15 21 9 3 6 12 2 12"></polyline>
          </svg>
        </div>
        <div class="stat-content">
          <div class="stat-label">Total Mockups</div>
          <div class="stat-value">{{ stats.total || 0 }}</div>
        </div>
      </div>

      <div class="stat-card">
        <div class="stat-icon" style="background: #6366f120; color: #6366f1;">
          <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
            <polyline points="22 12 18 12 15 21 9 3 6 12 2 12"></polyline>
          </svg>
        </div>
        <div class="stat-content">
          <div class="stat-label">Activos</div>
          <div class="stat-value">{{ stats.active || 0 }}</div>
        </div>
      </div>

      <div class="stat-card">
        <div class="stat-icon" style="background: #f59e0b20; color: #f59e0b;">
          <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
            <circle cx="12" cy="12" r="10"></circle>
            <line x1="12" y1="8" x2="12" y2="12"></line>
            <line x1="12" y1="16" x2="12.01" y2="16"></line>
          </svg>
        </div>
        <div class="stat-content">
          <div class="stat-label">Inactivos</div>
          <div class="stat-value">{{ stats.inactive || 0 }}</div>
        </div>
      </div>
    </div>

    <!-- Filtros -->
    <div class="filters">
      <select v-model="filterCategory" class="filter-select" @change="loadMockups">
        <option value="">Todas las categorías</option>
        <option value="device-frames">Device Frames</option>
        <option value="ui-elements">UI Elements</option>
        <option value="backgrounds">Backgrounds</option>
        <option value="general">General</option>
      </select>

      <select v-model="filterStatus" class="filter-select" @change="loadMockups">
        <option value="">Todos los estados</option>
        <option value="1">Activos</option>
        <option value="0">Inactivos</option>
      </select>

      <select v-model="orderBy" class="filter-select" @change="loadMockups">
        <option value="created_at">Más recientes</option>
        <option value="usage_count">Más usados</option>
        <option value="name">Nombre</option>
      </select>
    </div>

    <!-- Lista de Mockups -->
    <div v-if="loading" class="loading-state">
      <p>Cargando mockups...</p>
    </div>

    <div v-else class="mockups-grid">
      <div 
        v-for="mockup in mockups" 
        :key="mockup.id"
        class="mockup-card"
        :class="{ 'inactive': !mockup.is_active }"
      >
        <div class="mockup-image">
          <img :src="mockup.thumbnail_url" :alt="mockup.name">
          <div class="mockup-badge" :class="mockup.is_active ? 'badge-active' : 'badge-inactive'">
            {{ mockup.is_active ? 'Activo' : 'Inactivo' }}
          </div>
        </div>

        <div class="mockup-info">
          <h3 class="mockup-name">{{ mockup.name }}</h3>
          <p class="mockup-meta">
            <span class="category-tag">{{ mockup.category }}</span>
            <span class="dimensions">{{ mockup.width }} × {{ mockup.height }}</span>
          </p>
          <p class="mockup-usage">
            <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
              <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"></path>
              <circle cx="12" cy="12" r="3"></circle>
            </svg>
            {{ mockup.usage_count }} usos
          </p>
        </div>

        <div class="mockup-actions">
          <button 
            class="btn-action btn-toggle" 
            @click="toggleMockup(mockup)"
            :title="mockup.is_active ? 'Desactivar' : 'Activar'"
          >
            <svg v-if="mockup.is_active" xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
              <path d="M17.94 17.94A10.07 10.07 0 0 1 12 20c-7 0-11-8-11-8a18.45 18.45 0 0 1 5.06-5.94M9.9 4.24A9.12 9.12 0 0 1 12 4c7 0 11 8 11 8a18.5 18.5 0 0 1-2.16 3.19m-6.72-1.07a3 3 0 1 1-4.24-4.24"></path>
              <line x1="1" y1="1" x2="23" y2="23"></line>
            </svg>
            <svg v-else xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
              <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"></path>
              <circle cx="12" cy="12" r="3"></circle>
            </svg>
          </button>

          <button 
            class="btn-action btn-edit" 
            @click="editMockup(mockup)"
            title="Editar"
          >
            <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
              <path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"></path>
              <path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"></path>
            </svg>
          </button>

          <button 
            class="btn-action btn-delete" 
            @click="confirmDelete(mockup)"
            title="Eliminar permanentemente"
          >
            <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
              <polyline points="3 6 5 6 21 6"></polyline>
              <path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"></path>
            </svg>
          </button>
        </div>
      </div>

      <div v-if="mockups.length === 0" class="empty-state">
        <svg xmlns="http://www.w3.org/2000/svg" width="64" height="64" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
          <rect x="3" y="3" width="18" height="18" rx="2" ry="2"/>
          <circle cx="8.5" cy="8.5" r="1.5"/>
          <polyline points="21 15 16 10 5 21"/>
        </svg>
        <p>No hay mockups con estos filtros</p>
      </div>
    </div>

    <!-- Modal: Subir Mockup -->
    <div v-if="showUploadModal" class="modal-overlay" @click.self="closeUploadModal">
      <div class="modal">
        <div class="modal-header">
          <h2>Subir Nuevo Mockup</h2>
          <button class="btn-close" @click="closeUploadModal">×</button>
        </div>

        <form @submit.prevent="uploadMockup" class="upload-form">
          <div class="form-group">
            <label>Nombre *</label>
            <input 
              v-model="newMockup.name" 
              type="text" 
              placeholder="Ej: iPhone 15 Pro Frame"
              required
            >
          </div>

          <div class="form-group">
            <label>Categoría *</label>
            <select v-model="newMockup.category" required>
              <option value="">Selecciona una categoría</option>
              <option value="device-frames">Device Frames</option>
              <option value="ui-elements">UI Elements</option>
              <option value="backgrounds">Backgrounds</option>
              <option value="general">General</option>
            </select>
          </div>

          <div class="form-row">
            <div class="form-group">
              <label>Ancho (px)</label>
              <input 
                v-model.number="newMockup.width" 
                type="number" 
                placeholder="Auto"
              >
            </div>

            <div class="form-group">
              <label>Alto (px)</label>
              <input 
                v-model.number="newMockup.height" 
                type="number" 
                placeholder="Auto"
              >
            </div>
          </div>

          <div class="form-group">
            <label>Imagen Principal * (PNG, JPG - Max 10MB)</label>
            <input 
              type="file" 
              @change="handleFileSelect" 
              accept="image/*"
              required
            >
            <div v-if="filePreview" class="file-preview">
              <img :src="filePreview" alt="Preview">
            </div>
          </div>

          <div class="form-group">
            <label>Thumbnail (Opcional - Max 2MB)</label>
            <input 
              type="file" 
              @change="handleThumbnailSelect" 
              accept="image/*"
            >
            <div v-if="thumbnailPreview" class="file-preview">
              <img :src="thumbnailPreview" alt="Thumbnail Preview">
            </div>
          </div>

          <div class="modal-actions">
            <button type="button" class="btn btn-secondary" @click="closeUploadModal">
              Cancelar
            </button>
            <button type="submit" class="btn btn-primary" :disabled="uploading">
              {{ uploading ? 'Subiendo...' : 'Subir Mockup' }}
            </button>
          </div>
        </form>
      </div>
    </div>

    <!-- Modal: Editar Mockup -->
    <div v-if="showEditModal" class="modal-overlay" @click.self="closeEditModal">
      <div class="modal">
        <div class="modal-header">
          <h2>Editar Mockup</h2>
          <button class="btn-close" @click="closeEditModal">×</button>
        </div>

        <form @submit.prevent="updateMockup" class="upload-form">
          <div class="form-group">
            <label>Nombre *</label>
            <input 
              v-model="editingMockup.name" 
              type="text" 
              required
            >
          </div>

          <div class="form-group">
            <label>Categoría *</label>
            <select v-model="editingMockup.category" required>
              <option value="device-frames">Device Frames</option>
              <option value="ui-elements">UI Elements</option>
              <option value="backgrounds">Backgrounds</option>
              <option value="general">General</option>
            </select>
          </div>

          <div class="form-row">
            <div class="form-group">
              <label>Ancho (px)</label>
              <input 
                v-model.number="editingMockup.width" 
                type="number"
              >
            </div>

            <div class="form-group">
              <label>Alto (px)</label>
              <input 
                v-model.number="editingMockup.height" 
                type="number"
              >
            </div>
          </div>

          <div class="modal-actions">
            <button type="button" class="btn btn-secondary" @click="closeEditModal">
              Cancelar
            </button>
            <button type="submit" class="btn btn-primary" :disabled="updating">
              {{ updating ? 'Guardando...' : 'Guardar Cambios' }}
            </button>
          </div>
        </form>
      </div>
    </div>

    <!-- Modal: Confirmar Eliminación -->
    <div v-if="showDeleteModal" class="modal-overlay" @click.self="showDeleteModal = false">
      <div class="modal modal-small">
        <div class="modal-header">
          <h2>Confirmar Eliminación</h2>
          <button class="btn-close" @click="showDeleteModal = false">×</button>
        </div>

        <div class="modal-body">
          <p>¿Estás seguro de que deseas eliminar permanentemente el mockup <strong>"{{ mockupToDelete?.name }}"</strong>?</p>
          <p class="warning-text">
            <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
              <circle cx="12" cy="12" r="10"></circle>
              <line x1="12" y1="8" x2="12" y2="12"></line>
              <line x1="12" y1="16" x2="12.01" y2="16"></line>
            </svg>
            Esta acción no se puede deshacer. Los archivos se eliminarán del servidor. Los diseños que ya usan este mockup no se verán afectados.
          </p>
        </div>

        <div class="modal-actions">
          <button type="button" class="btn btn-secondary" @click="showDeleteModal = false">
            Cancelar
          </button>
          <button type="button" class="btn btn-danger" @click="deleteMockup" :disabled="deleting">
            {{ deleting ? 'Eliminando...' : 'Eliminar Permanentemente' }}
          </button>
        </div>
      </div>
    </div>

    <!-- Notificación -->
    <div v-if="notification.show" class="notification" :class="`notification-${notification.type}`">
      {{ notification.message }}
    </div>
  </div>
</template>

<script>
import { ref, onMounted } from 'vue';
import axios from 'axios';

export default {
  name: 'AdminMockups',

  setup() {
    const mockups = ref([]);
    const stats = ref({});
    const loading = ref(false);
    const uploading = ref(false);
    const updating = ref(false);
    const deleting = ref(false);

    // Filtros
    const filterCategory = ref('');
    const filterStatus = ref('');
    const orderBy = ref('created_at');

    // Modales
    const showUploadModal = ref(false);
    const showEditModal = ref(false);
    const showDeleteModal = ref(false);

    // Formularios
    const newMockup = ref({
      name: '',
      category: '',
      width: null,
      height: null,
      file: null,
      thumbnail: null,
    });

    const editingMockup = ref(null);
    const mockupToDelete = ref(null);

    // Previews
    const filePreview = ref(null);
    const thumbnailPreview = ref(null);

    // Notificaciones
    const notification = ref({
      show: false,
      message: '',
      type: 'success'
    });

    const showNotification = (message, type = 'success') => {
      notification.value = { show: true, message, type };
      setTimeout(() => {
        notification.value.show = false;
      }, 3000);
    };

    // Cargar mockups
    const loadMockups = async () => {
      try {
        loading.value = true;
        
        const params = {
          order_by: orderBy.value,
          order_direction: 'desc'
        };

        if (filterCategory.value) {
          params.category = filterCategory.value;
        }

        if (filterStatus.value !== '') {
          params.is_active = filterStatus.value;
        }

        const response = await axios.get('/api/admin/mockups', { params });
        mockups.value = response.data;
        
        loading.value = false;
      } catch (error) {
        console.error('Error al cargar mockups:', error);
        showNotification('Error al cargar mockups', 'error');
        loading.value = false;
      }
    };

    // Cargar estadísticas
    const loadStats = async () => {
      try {
        const response = await axios.get('/api/admin/mockups/stats');
        stats.value = response.data;
      } catch (error) {
        console.error('Error al cargar estadísticas:', error);
      }
    };

    // Manejar selección de archivo
    const handleFileSelect = (event) => {
      const file = event.target.files[0];
      if (file) {
        newMockup.value.file = file;
        filePreview.value = URL.createObjectURL(file);
      }
    };

    const handleThumbnailSelect = (event) => {
      const file = event.target.files[0];
      if (file) {
        newMockup.value.thumbnail = file;
        thumbnailPreview.value = URL.createObjectURL(file);
      }
    };

    // Subir mockup
    const uploadMockup = async () => {
      try {
        uploading.value = true;

        const formData = new FormData();
        formData.append('name', newMockup.value.name);
        formData.append('category', newMockup.value.category);
        formData.append('file', newMockup.value.file);
        
        if (newMockup.value.thumbnail) {
          formData.append('thumbnail', newMockup.value.thumbnail);
        }

        if (newMockup.value.width) {
          formData.append('width', newMockup.value.width);
        }

        if (newMockup.value.height) {
          formData.append('height', newMockup.value.height);
        }

        await axios.post('/api/admin/mockups', formData, {
          headers: { 'Content-Type': 'multipart/form-data' }
        });

        showNotification('Mockup subido exitosamente');
        closeUploadModal();
        loadMockups();
        loadStats();

        uploading.value = false;
      } catch (error) {
        console.error('Error al subir mockup:', error);
        showNotification('Error al subir mockup', 'error');
        uploading.value = false;
      }
    };

    // Editar mockup
    const editMockup = (mockup) => {
      editingMockup.value = { ...mockup };
      showEditModal.value = true;
    };

    const updateMockup = async () => {
      try {
        updating.value = true;

        await axios.put(`/api/admin/mockups/${editingMockup.value.id}`, {
          name: editingMockup.value.name,
          category: editingMockup.value.category,
          width: editingMockup.value.width,
          height: editingMockup.value.height,
        });

        showNotification('Mockup actualizado exitosamente');
        closeEditModal();
        loadMockups();

        updating.value = false;
      } catch (error) {
        console.error('Error al actualizar mockup:', error);
        showNotification('Error al actualizar mockup', 'error');
        updating.value = false;
      }
    };

    // Toggle activo/inactivo
    const toggleMockup = async (mockup) => {
      try {
        await axios.post(`/api/admin/mockups/${mockup.id}/toggle-active`);
        
        const status = !mockup.is_active ? 'activado' : 'desactivado';
        showNotification(`Mockup ${status} exitosamente`);
        
        loadMockups();
        loadStats();
      } catch (error) {
        console.error('Error al cambiar estado:', error);
        showNotification('Error al cambiar estado del mockup', 'error');
      }
    };

    // Confirmar eliminación
    const confirmDelete = (mockup) => {
      mockupToDelete.value = mockup;
      showDeleteModal.value = true;
    };

    // Eliminar mockup
    const deleteMockup = async () => {
      try {
        deleting.value = true;

        await axios.delete(`/api/admin/mockups/${mockupToDelete.value.id}`);

        showNotification('Mockup eliminado permanentemente');
        showDeleteModal.value = false;
        mockupToDelete.value = null;
        loadMockups();
        loadStats();

        deleting.value = false;
      } catch (error) {
        console.error('Error al eliminar mockup:', error);
        showNotification('Error al eliminar mockup', 'error');
        deleting.value = false;
      }
    };

    // Cerrar modales
    const closeUploadModal = () => {
      showUploadModal.value = false;
      newMockup.value = {
        name: '',
        category: '',
        width: null,
        height: null,
        file: null,
        thumbnail: null,
      };
      filePreview.value = null;
      thumbnailPreview.value = null;
    };

    const closeEditModal = () => {
      showEditModal.value = false;
      editingMockup.value = null;
    };

    onMounted(() => {
      loadMockups();
      loadStats();
    });

    return {
      mockups,
      stats,
      loading,
      uploading,
      updating,
      deleting,
      filterCategory,
      filterStatus,
      orderBy,
      showUploadModal,
      showEditModal,
      showDeleteModal,
      newMockup,
      editingMockup,
      mockupToDelete,
      filePreview,
      thumbnailPreview,
      notification,
      loadMockups,
      handleFileSelect,
      handleThumbnailSelect,
      uploadMockup,
      editMockup,
      updateMockup,
      toggleMockup,
      confirmDelete,
      deleteMockup,
      closeUploadModal,
      closeEditModal,
    };
  },
};
</script>

<style scoped>
/* Variables */
.admin-mockups {
  --primary: #6366f1;
  --primary-hover: #4f46e5;
  --success: #22c55e;
  --warning: #f59e0b;
  --error: #ef4444;
  --bg: #0f172a;
  --bg-surface: #1e293b;
  --bg-surface-light: #334155;
  --text-primary: #f8fafc;
  --text-secondary: #94a3b8;
  --border: #334155;

  min-height: 100vh;
  background: var(--bg);
  color: var(--text-primary);
  padding: 2rem;
}

/* Header */
.admin-header {
  display: flex;
  justify-content: space-between;
  align-items: flex-start;
  margin-bottom: 2rem;
}

.header-left h1 {
  font-size: 2rem;
  font-weight: 700;
  margin: 0 0 0.5rem 0;
}

.subtitle {
  color: var(--text-secondary);
  margin: 0;
}

/* Stats Grid */
.stats-grid {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
  gap: 1rem;
  margin-bottom: 2rem;
}

.stat-card {
  display: flex;
  align-items: center;
  gap: 1rem;
  padding: 1.5rem;
  background: var(--bg-surface);
  border: 1px solid var(--border);
  border-radius: 12px;
}

.stat-icon {
  width: 48px;
  height: 48px;
  display: flex;
  align-items: center;
  justify-content: center;
  border-radius: 8px;
}

.stat-content {
  flex: 1;
}

.stat-label {
  font-size: 0.875rem;
  color: var(--text-secondary);
  margin-bottom: 0.25rem;
}

.stat-value {
  font-size: 1.75rem;
  font-weight: 700;
}

/* Filtros */
.filters {
  display: flex;
  gap: 1rem;
  margin-bottom: 1.5rem;
}

.filter-select {
  padding: 0.625rem 1rem;
  background: var(--bg-surface);
  border: 1px solid var(--border);
  border-radius: 8px;
  color: var(--text-primary);
  font-size: 0.875rem;
  cursor: pointer;
}

/* Mockups Grid */
.mockups-grid {
  display: grid;
  grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
  gap: 1.5rem;
}

.mockup-card {
  background: var(--bg-surface);
  border: 1px solid var(--border);
  border-radius: 12px;
  overflow: hidden;
  transition: transform 0.2s ease, box-shadow 0.2s ease;
}

.mockup-card:hover {
  transform: translateY(-4px);
  box-shadow: 0 8px 24px rgba(0, 0, 0, 0.3);
}

.mockup-card.inactive {
  opacity: 0.6;
}

.mockup-image {
  position: relative;
  width: 100%;
  height: 180px;
  background: var(--bg-surface-light);
  overflow: hidden;
}

.mockup-image img {
  width: 100%;
  height: 100%;
  object-fit: cover;
}

.mockup-badge {
  position: absolute;
  top: 0.5rem;
  right: 0.5rem;
  padding: 0.25rem 0.625rem;
  border-radius: 6px;
  font-size: 0.75rem;
  font-weight: 600;
}

.badge-active {
  background: #22c55e20;
  color: #22c55e;
}

.badge-inactive {
  background: #f59e0b20;
  color: #f59e0b;
}

.mockup-info {
  padding: 1rem;
}

.mockup-name {
  font-size: 1rem;
  font-weight: 600;
  margin: 0 0 0.5rem 0;
}

.mockup-meta {
  display: flex;
  gap: 0.5rem;
  align-items: center;
  margin-bottom: 0.5rem;
}

.category-tag {
  padding: 0.25rem 0.5rem;
  background: var(--primary);
  color: white;
  border-radius: 4px;
  font-size: 0.75rem;
  font-weight: 500;
}

.dimensions {
  color: var(--text-secondary);
  font-size: 0.75rem;
}

.mockup-usage {
  display: flex;
  align-items: center;
  gap: 0.25rem;
  color: var(--text-secondary);
  font-size: 0.875rem;
}

.mockup-actions {
  display: flex;
  gap: 0.5rem;
  padding: 0.75rem 1rem;
  background: var(--bg-surface-light);
  border-top: 1px solid var(--border);
}

.btn-action {
  flex: 1;
  display: flex;
  align-items: center;
  justify-content: center;
  padding: 0.5rem;
  border: 1px solid var(--border);
  border-radius: 6px;
  background: transparent;
  color: var(--text-primary);
  cursor: pointer;
  transition: all 0.2s ease;
}

.btn-action:hover {
  background: var(--bg-surface);
}

.btn-toggle:hover {
  border-color: var(--primary);
  color: var(--primary);
}

.btn-edit:hover {
  border-color: var(--warning);
  color: var(--warning);
}

.btn-delete:hover {
  border-color: var(--error);
  color: var(--error);
}

/* Estados */
.loading-state,
.empty-state {
  text-align: center;
  padding: 3rem;
  color: var(--text-secondary);
}

.empty-state svg {
  margin-bottom: 1rem;
  opacity: 0.5;
}

/* Botones */
.btn {
  display: inline-flex;
  align-items: center;
  gap: 0.5rem;
  padding: 0.625rem 1.25rem;
  border: none;
  border-radius: 8px;
  font-size: 0.875rem;
  font-weight: 500;
  cursor: pointer;
  transition: all 0.2s ease;
}

.btn-primary {
  background: var(--primary);
  color: white;
}

.btn-primary:hover:not(:disabled) {
  background: var(--primary-hover);
}

.btn-secondary {
  background: var(--bg-surface-light);
  color: var(--text-primary);
  border: 1px solid var(--border);
}

.btn-secondary:hover {
  background: var(--bg-surface);
}

.btn-danger {
  background: var(--error);
  color: white;
}

.btn-danger:hover:not(:disabled) {
  background: #dc2626;
}

.btn:disabled {
  opacity: 0.5;
  cursor: not-allowed;
}

/* Modales */
.modal-overlay {
  position: fixed;
  inset: 0;
  background: rgba(0, 0, 0, 0.7);
  display: flex;
  align-items: center;
  justify-content: center;
  z-index: 1000;
  padding: 1rem;
}

.modal {
  background: var(--bg-surface);
  border: 1px solid var(--border);
  border-radius: 12px;
  width: 100%;
  max-width: 600px;
  max-height: 90vh;
  overflow-y: auto;
}

.modal-small {
  max-width: 500px;
}

.modal-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: 1.5rem;
  border-bottom: 1px solid var(--border);
}

.modal-header h2 {
  margin: 0;
  font-size: 1.25rem;
}

.btn-close {
  background: transparent;
  border: none;
  color: var(--text-secondary);
  font-size: 2rem;
  cursor: pointer;
  line-height: 1;
  padding: 0;
  width: 32px;
  height: 32px;
}

.btn-close:hover {
  color: var(--text-primary);
}

.modal-body {
  padding: 1.5rem;
}

.modal-actions {
  display: flex;
  gap: 0.75rem;
  padding: 1.5rem;
  border-top: 1px solid var(--border);
}

.modal-actions .btn {
  flex: 1;
}

/* Formularios */
.upload-form {
  padding: 1.5rem;
}

.form-group {
  margin-bottom: 1.25rem;
}

.form-group label {
  display: block;
  margin-bottom: 0.5rem;
  font-size: 0.875rem;
  font-weight: 500;
  color: var(--text-secondary);
}

.form-group input,
.form-group select {
  width: 100%;
  padding: 0.625rem;
  background: var(--bg);
  border: 1px solid var(--border);
  border-radius: 8px;
  color: var(--text-primary);
  font-size: 0.875rem;
}

.form-group input:focus,
.form-group select:focus {
  outline: none;
  border-color: var(--primary);
}

.form-row {
  display: grid;
  grid-template-columns: 1fr 1fr;
  gap: 1rem;
}

.file-preview {
  margin-top: 0.75rem;
  width: 100%;
  max-width: 200px;
  border-radius: 8px;
  overflow: hidden;
  border: 1px solid var(--border);
}

.file-preview img {
  width: 100%;
  height: auto;
  display: block;
}

.warning-text {
  display: flex;
  align-items: flex-start;
  gap: 0.5rem;
  padding: 1rem;
  background: rgba(239, 68, 68, 0.1);
  border: 1px solid rgba(239, 68, 68, 0.3);
  border-radius: 8px;
  color: var(--text-secondary);
  font-size: 0.875rem;
  margin-top: 1rem;
}

.warning-text svg {
  flex-shrink: 0;
  color: var(--error);
  margin-top: 0.125rem;
}

/* Notificaciones */
.notification {
  position: fixed;
  bottom: 2rem;
  right: 2rem;
  padding: 1rem 1.5rem;
  border-radius: 8px;
  font-weight: 500;
  z-index: 2000;
  animation: slideIn 0.3s ease;
}

.notification-success {
  background: var(--success);
  color: white;
}

.notification-error {
  background: var(--error);
  color: white;
}

@keyframes slideIn {
  from {
    transform: translateX(400px);
    opacity: 0;
  }
  to {
    transform: translateX(0);
    opacity: 1;
  }
}

/* Responsive */
@media (max-width: 768px) {
  .admin-mockups {
    padding: 1rem;
  }

  .admin-header {
    flex-direction: column;
    gap: 1rem;
  }

  .mockups-grid {
    grid-template-columns: 1fr;
  }

  .filters {
    flex-direction: column;
  }

  .form-row {
    grid-template-columns: 1fr;
  }
}
</style>
