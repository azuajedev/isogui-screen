/**
 * Idogui Screen - Aplicación Vue 3
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
            user: window.Idogui?.user || null,
            isLoading: false,
        };
    },
    mounted() {
        console.log('Idogui Screen inicializado');
    },
});

// Configuración global
app.config.globalProperties.$api = window.Idogui?.apiUrl || '/api';
app.config.globalProperties.$storage = window.Idogui?.storageUrl || '/storage';

// Montar aplicación
app.mount('#app');
