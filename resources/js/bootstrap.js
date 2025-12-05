/**
 * Idogui Screen - Bootstrap
 * 
 * Configura las dependencias globales de la aplicación.
 * Incluye Axios para peticiones HTTP con CSRF token automático.
 */

import axios from 'axios';

// Configurar Axios como global
window.axios = axios;

// Headers por defecto
window.axios.defaults.headers.common['X-Requested-With'] = 'XMLHttpRequest';
window.axios.defaults.headers.common['Accept'] = 'application/json';

// Habilitar envío de cookies con credenciales
window.axios.defaults.withCredentials = true;

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
            // Mostrar mensaje y redirigir a login si no está autenticado
            if (!window.redirectingToLogin) {
                window.redirectingToLogin = true;
                alert('Tu sesión ha expirado. Por favor inicia sesión nuevamente.');
                window.location.href = '/login';
            }
        }
        return Promise.reject(error);
    }
);
