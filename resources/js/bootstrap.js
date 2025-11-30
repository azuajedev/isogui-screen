/**
 * IsoGUI Screen - Bootstrap
 * 
 * Configura las dependencias globales de la aplicación.
 * Incluye Axios para peticiones HTTP con CSRF token automático.
 */

import axios from 'axios';

// Configurar Axios como global
window.axios = axios;

// Headers por defecto
window.axios.defaults.headers.common['X-Requested-With'] = 'XMLHttpRequest';

// CSRF Token automático
const token = document.head.querySelector('meta[name="csrf-token"]');
if (token) {
    window.axios.defaults.headers.common['X-CSRF-TOKEN'] = token.content;
} else {
    console.warn('CSRF token no encontrado');
}

// Interceptor para manejar errores de autenticación
window.axios.interceptors.response.use(
    response => response,
    error => {
        if (error.response?.status === 401) {
            // Redirigir a login si no está autenticado
            window.location.href = '/login';
        }
        return Promise.reject(error);
    }
);
