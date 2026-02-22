import React, { useState } from 'react';
import { useAuth } from '../context/AuthContext';
import { useNavigate } from 'react-router-dom';

const Auth = () => {
    const [formData, setFormData] = useState({
        name: '',
        email: '',
        password: '',
        confirmPassword: ''
    });
    const [error, setError] = useState('');
    const [isLogin, setIsLogin] = useState(false);

    const { register, login } = useAuth();
    const navigate = useNavigate();

    const validateEmail = (email) => {
        const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
        return emailRegex.test(email);
    };

    const validateForm = () => {
        if (!formData.name.trim()) {
            setError('Please enter your name');
            return false;
        }
        if (!validateEmail(formData.email)) {
            setError('Please enter a valid email address');
            return false;
        }
        if (!formData.password) {
            setError('Please enter a password');
            return false;
        }
        if (formData.password.length < 6) {
            setError('Password must be at least 6 characters');
            return false;
        }
        if (formData.password !== formData.confirmPassword) {
            setError('Passwords do not match');
            return false;
        }
        return true;
    };

    const handleSubmit = async (e) => {
        e.preventDefault();
        setError('');

        if (isLogin) {
            // Login mode
            if (!validateEmail(formData.email)) {
                setError('Please enter a valid email address');
                return;
            }
            if (!formData.password) {
                setError('Please enter a password');
                return;
            }

            try {
                await login(formData.email, formData.password);
                navigate('/chat');
            } catch (err) {
                setError(err.response?.data?.message || 'Login failed');
            }
        } else {
            // Registration mode
            if (!validateForm()) return;

            try {
                await register({
                    email: formData.email,
                    name: formData.name,
                    password: formData.password
                });
                navigate('/chat');
            } catch (err) {
                setError(err.response?.data?.message || 'Registration failed');
            }
        }
    };

    const handleInputChange = (e) => {
        setFormData({
            ...formData,
            [e.target.name]: e.target.value
        });
    };

    return (
        <div className="login-container">
            <div className="auth-form" style={{ maxWidth: '450px' }}>
                <div style={{ textAlign: 'center', marginBottom: '30px' }}>
                    <img src="https://upload.wikimedia.org/wikipedia/commons/6/6b/WhatsApp.svg" alt="WhatsApp" width="60" />
                    <h2 style={{ margin: '10px 0', color: '#333' }}>
                        {isLogin ? 'Welcome Back' : 'Create Account'}
                    </h2>
                    <p style={{ color: '#666', fontSize: '14px' }}>
                        {isLogin ? 'Sign in to continue to WhatsApp' : 'Join millions of people using WhatsApp'}
                    </p>
                </div>

                {error && (
                    <div style={{
                        color: '#dc3545',
                        background: '#f8d7da',
                        border: '1px solid #f5c6cb',
                        padding: '10px',
                        borderRadius: '5px',
                        marginBottom: '20px',
                        textAlign: 'center'
                    }}>
                        {error}
                    </div>
                )}

                <form onSubmit={handleSubmit}>
                    {!isLogin && (
                        <div style={{ marginBottom: '15px' }}>
                            <input
                                type="text"
                                name="name"
                                value={formData.name}
                                onChange={handleInputChange}
                                placeholder="Full Name"
                                style={{
                                    width: '100%',
                                    padding: '12px',
                                    borderRadius: '8px',
                                    border: '1px solid #ddd',
                                    fontSize: '16px',
                                    outline: 'none',
                                    transition: 'border-color 0.3s'
                                }}
                                onFocus={(e) => e.target.style.borderColor = '#00a884'}
                                onBlur={(e) => e.target.style.borderColor = '#ddd'}
                                required
                            />
                        </div>
                    )}

                    <div style={{ marginBottom: '15px' }}>
                        <input
                            type="email"
                            name="email"
                            value={formData.email}
                            onChange={handleInputChange}
                            placeholder="Email Address"
                            style={{
                                width: '100%',
                                padding: '12px',
                                borderRadius: '8px',
                                border: '1px solid #ddd',
                                fontSize: '16px',
                                outline: 'none',
                                transition: 'border-color 0.3s'
                            }}
                            onFocus={(e) => e.target.style.borderColor = '#00a884'}
                            onBlur={(e) => e.target.style.borderColor = '#ddd'}
                            required
                        />
                    </div>

                    <div style={{ marginBottom: '15px' }}>
                        <input
                            type="password"
                            name="password"
                            value={formData.password}
                            onChange={handleInputChange}
                            placeholder="Password"
                            style={{
                                width: '100%',
                                padding: '12px',
                                borderRadius: '8px',
                                border: '1px solid #ddd',
                                fontSize: '16px',
                                outline: 'none',
                                transition: 'border-color 0.3s'
                            }}
                            onFocus={(e) => e.target.style.borderColor = '#00a884'}
                            onBlur={(e) => e.target.style.borderColor = '#ddd'}
                            required
                        />
                    </div>

                    {!isLogin && (
                        <div style={{ marginBottom: '20px' }}>
                            <input
                                type="password"
                                name="confirmPassword"
                                value={formData.confirmPassword}
                                onChange={handleInputChange}
                                placeholder="Confirm Password"
                                style={{
                                    width: '100%',
                                    padding: '12px',
                                    borderRadius: '8px',
                                    border: '1px solid #ddd',
                                    fontSize: '16px',
                                    outline: 'none',
                                    transition: 'border-color 0.3s'
                                }}
                                onFocus={(e) => e.target.style.borderColor = '#00a884'}
                                onBlur={(e) => e.target.style.borderColor = '#ddd'}
                                required
                            />
                        </div>
                    )}

                    <button
                        type="submit"
                        style={{
                            width: '100%',
                            padding: '12px',
                            background: '#00a884',
                            color: 'white',
                            border: 'none',
                            borderRadius: '8px',
                            fontSize: '16px',
                            fontWeight: 'bold',
                            cursor: 'pointer',
                            transition: 'background-color 0.3s'
                        }}
                        onMouseOver={(e) => e.target.style.backgroundColor = '#007f6f'}
                        onMouseOut={(e) => e.target.style.backgroundColor = '#00a884'}
                    >
                        {isLogin ? 'Sign In' : 'Create Account'}
                    </button>
                </form>

                <div style={{ textAlign: 'center', marginTop: '20px' }}>
                    <span style={{ color: '#666', fontSize: '14px' }}>
                        {isLogin ? "Don't have an account? " : "Already have an account? "}
                        <button
                            type="button"
                            onClick={() => {
                                setIsLogin(!isLogin);
                                setError('');
                                setFormData({
                                    name: '',
                                    email: '',
                                    password: '',
                                    confirmPassword: ''
                                });
                            }}
                            style={{
                                background: 'none',
                                border: 'none',
                                color: '#00a884',
                                cursor: 'pointer',
                                fontSize: '14px',
                                fontWeight: 'bold',
                                textDecoration: 'underline'
                            }}
                        >
                            {isLogin ? 'Sign Up' : 'Sign In'}
                        </button>
                    </span>
                </div>

                <div style={{ textAlign: 'center', marginTop: '20px', paddingTop: '20px', borderTop: '1px solid #eee' }}>
                    <p style={{ color: '#999', fontSize: '12px' }}>
                        By signing up, you agree to our Terms of Service and Privacy Policy
                    </p>
                </div>
            </div>
        </div>
    );
};

export default Auth;
