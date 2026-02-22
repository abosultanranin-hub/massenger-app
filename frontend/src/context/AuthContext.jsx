import React, { createContext, useContext, useEffect, useMemo, useState } from 'react';
import api from '../services/api';

const AuthContext = createContext(null);

export const AuthProvider = ({ children }) => {
    const [user, setUser] = useState(null);
    const [loading, setLoading] = useState(true);
    const [onlineUsers, setOnlineUsers] = useState(new Set());

    useEffect(() => {
        try {
            const storedUser = localStorage.getItem('user');
            const storedToken = localStorage.getItem('token');
            if (storedUser && storedToken) {
                setUser(JSON.parse(storedUser));
            }
        } catch {
            localStorage.removeItem('user');
            localStorage.removeItem('token');
        } finally {
            setLoading(false);
        }

    }, []);

    // Handle Online Status Ping and Tab Close
    useEffect(() => {
        let interval;
        if (user) {
            const sendPing = () => {
                api.post('/user/ping').catch(err => console.error('Ping failed:', err));
            };

            // Send immediately
            sendPing();

            // Then every 60s
            interval = setInterval(sendPing, 60000);

            return () => {
                if (interval) clearInterval(interval);
            };
        }
    }, [user]);

    const register = async ({ email }) => {
        const response = await api.post('/auth/register', { email });
        const { token, user_id, is_new_user } = response.data;

        const userObj = { user_id, email, is_new_user };
        localStorage.setItem('token', token);
        localStorage.setItem('user', JSON.stringify(userObj));
        setUser(userObj);
        return userObj;
    };

    const login = async ({ email }) => {
        const response = await api.post('/auth/login', { email });
        const { token, user_id, is_new_user } = response.data;

        const userObj = { user_id, email, is_new_user };
        localStorage.setItem('token', token);
        localStorage.setItem('user', JSON.stringify(userObj));
        setUser(userObj);
        return userObj;
    };

    const logout = () => {
        localStorage.removeItem('token');
        localStorage.removeItem('user');
        setUser(null);
    };

    const value = useMemo(() => ({
        user,
        loading,
        register,
        login,
        logout,
        setUser
    }), [user, loading]);

    return (
        <AuthContext.Provider value={value}>
            {children}
        </AuthContext.Provider>
    );
};

export const useAuth = () => {
    const ctx = useContext(AuthContext);
    if (!ctx) throw new Error('useAuth must be used within AuthProvider');
    return ctx;
};
