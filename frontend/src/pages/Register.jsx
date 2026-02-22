import React, { useState } from 'react';
import { useAuth } from '../context/AuthContext';
import { useNavigate, Link } from 'react-router-dom';

const Register = () => {
    const [email, setEmail] = useState('');
    const [error, setError] = useState('');

    const { register } = useAuth();
    const navigate = useNavigate();

    const validateEmail = (email) => {
        const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
        return emailRegex.test(email);
    };

    const handleRegister = async (e) => {
        e.preventDefault();
        setError('');

        if (!validateEmail(email)) {
            setError('Please enter a valid email address');
            return;
        }

        try {
            await register({ email });
            navigate('/chat');
        } catch (err) {
            setError(err.response?.data?.message || 'Registration failed');
        }
    };

    return (
        <div className="login-container">
            <div className="auth-form" style={{ maxWidth: '400px' }}>
                <div style={{ textAlign: 'center', marginBottom: '30px' }}>
                    <img src="https://upload.wikimedia.org/wikipedia/commons/6/6b/WhatsApp.svg" alt="WhatsApp" width="60" />
                    <h2 style={{ margin: '10px 0', color: '#333' }}>Create Account</h2>
                    <p style={{ color: '#666', fontSize: '14px' }}>
                        Join millions of people using WhatsApp
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

                <form onSubmit={handleRegister}>
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
                        Create Account
                    </button>
                </form>

                <div style={{ textAlign: 'center', marginTop: '20px' }}>
                    <span style={{ color: '#666', fontSize: '14px' }}>
                        Already have an account?{' '}
                        <Link
                            to="/login"
                            style={{
                                color: '#00a884',
                                textDecoration: 'none',
                                fontWeight: 'bold'
                            }}
                        >
                            Sign In
                        </Link>
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

export default Register;
