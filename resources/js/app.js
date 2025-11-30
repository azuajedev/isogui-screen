/**
 * IsoGUI Screen - Aplicación Vue 3
 * 
 * Punto de entrada principal para la aplicación de frontend.
 * Configura Vue 3 con Composition API y componentes globales.
 */

import './bootstrap';

import { createApp } from 'vue';
import MockupEditor from './components/MockupEditor.vue';

// Crear aplicación Vue
const app = createApp({
    components: {
        MockupEditor,
    },
    data() {
        return {
            user: window.IsoGUI?.user || null,
            isLoading: false,
        };
    },
    mounted() {
        console.log('IsoGUI Screen inicializado');
    },
});

// Configuración global
app.config.globalProperties.$api = window.IsoGUI?.apiUrl || '/api';
app.config.globalProperties.$storage = window.IsoGUI?.storageUrl || '/storage';

// Montar aplicación
app.mount('#app');
