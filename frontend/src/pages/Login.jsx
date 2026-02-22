import React, { useState } from 'react';
import { useAuth } from '../context/AuthContext';
import { useNavigate, Link } from 'react-router-dom';

const Login = () => {
    const [email, setEmail] = useState('');
    const [error, setError] = useState('');

    const { login } = useAuth();
    const navigate = useNavigate();

    const validateEmail = (value) => {
        const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
        return emailRegex.test(value);
    };

    const handleLogin = async (e) => {
        e.preventDefault();
        setError('');

        if (!validateEmail(email)) {
            setError('Please enter a valid email address');
            return;
        }

        try {
            await login({ email });
            navigate('/chat');
        } catch (err) {
            setError(err.response?.data?.message || 'Login failed');
        }
    };

    return (
        <div className="login-container">
            <div className="auth-form" style={{ maxWidth: '400px' }}>
                <div style={{ textAlign: 'center', marginBottom: '30px' }}>
                    <img src="https://upload.wikimedia.org/wikipedia/commons/6/6b/WhatsApp.svg" alt="WhatsApp" width="60" />
                    <h2 style={{ margin: '10px 0', color: '#333' }}>Sign In</h2>
                    <p style={{ color: '#666', fontSize: '14px' }}>
                        Sign in with your email to continue
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

                <form onSubmit={handleLogin}>
                    <div style={{ marginBottom: '20px' }}>
                        <input
                            type="email"
                            value={email}
                            onChange={(e) => setEmail(e.target.value)}
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
                        Sign In
                    </button>
                </form>

                <div style={{ textAlign: 'center', marginTop: '20px' }}>
                    <span style={{ color: '#666', fontSize: '14px' }}>
                        Don&apos;t have an account?{' '}
                        <Link
                            to="/register"
                            style={{
                                color: '#00a884',
                                textDecoration: 'none',
                                fontWeight: 'bold'
                            }}
                        >
                            Create one
                        </Link>
                    </span>
                </div>
            </div>
        </div>
    );
};

export default Login;

