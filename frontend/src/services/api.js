import axios from 'axios';

// Adjust this URL to match your server configuration
const API_URL = 'http://localhost/php%20chat1/public';

const api = axios.create({
    baseURL: API_URL,
    headers: {
        'Content-Type': 'application/json',
    },
});

// Add a request interceptor to include the JWT token
api.interceptors.request.use(
    (config) => {
        const token = localStorage.getItem('token');
        if (token) {
            config.headers['Authorization'] = `Bearer ${token}`;
        }
        return config;
    },
    (error) => {
        return Promise.reject(error);
    }
);

api.interceptors.response.use(
    (response) => response,
    (error) => {
        if (error.response && error.response.status === 401) {
            // Auto logout or refresh logic could go here
            localStorage.removeItem('token');
            // optionally redirect to login
        }
        return Promise.reject(error);
    }
);

export default api;
