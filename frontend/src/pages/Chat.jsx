import React, { useState, useEffect, useRef } from 'react';
import { useAuth } from '../context/AuthContext';
import api from '../services/api';
import { FaPaperPlane, FaUserCircle, FaSignOutAlt, FaPlus, FaCheck, FaCheckDouble, FaSearch, FaEllipsisV, FaArrowLeft } from 'react-icons/fa';

const MessageBubble = ({ message, isMe }) => {
    return (
        <div className={`message ${isMe ? 'message-out' : 'message-in'}`}>
            <div className="message-content">
                {message.content}
                <div className="message-meta">
                    <span className="message-time">
                        {new Date(message.created_at || Date.now()).toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' })}
                    </span>
                    {isMe && (
                        <span className="message-status">
                            {message.status === 'read' ? <FaCheckDouble color="#53bdeb" /> :
                                message.status === 'delivered' ? <FaCheckDouble color="#8696a0" /> : <FaCheck color="#8696a0" />}
                        </span>
                    )}
                </div>
            </div>
        </div>
    );
};

const Chat = () => {
    const { logout, user } = useAuth();
    const [chats, setChats] = useState([]);
    const [currentChat, setCurrentChat] = useState(null);
    const [messages, setMessages] = useState([]);
    const [newMessage, setNewMessage] = useState('');
    const [showNewChatModal, setShowNewChatModal] = useState(false);
    const [newChatEmail, setNewChatEmail] = useState('');
    const messagesEndRef = useRef(null);
    const chatIntervalRef = useRef(null);

    // Initial load
    useEffect(() => {
        loadChats();
        // Poll for new chats/unread counts every 5 seconds
        const interval = setInterval(loadChats, 5000);
        return () => clearInterval(interval);
    }, []);

    // Load messages when chat is selected
    useEffect(() => {
        if (currentChat) {
            loadMessages(currentChat.chat_id);
            markMessagesAsRead(currentChat.chat_id);

            // Polling for messages in active chat
            if (chatIntervalRef.current) clearInterval(chatIntervalRef.current);
            chatIntervalRef.current = setInterval(() => {
                loadMessages(currentChat.chat_id, true); // silent load
                markMessagesAsRead(currentChat.chat_id);
            }, 3000);
        }
        return () => {
            if (chatIntervalRef.current) clearInterval(chatIntervalRef.current);
        };
    }, [currentChat]);

    // Scroll effect
    useEffect(() => {
        scrollToBottom();
    }, [messages]);

    const scrollToBottom = () => {
        messagesEndRef.current?.scrollIntoView({ behavior: 'smooth' });
    };

    const loadChats = async () => {
        try {
            const res = await api.get('/chats');
            setChats(res.data.data);
        } catch (err) {
            console.error("Failed to load chats", err);
        }
    };

    const loadMessages = async (chatId, silent = false) => {
        try {
            const res = await api.get(`/chats/messages?chat_id=${chatId}`);
            if (res.data.data) {
                // Ideally we merge lists to avoid flickering, but standard react set state is okay usually
                // Here we just simple replace for simplicity
                setMessages(res.data.data);
            }
        } catch (err) {
            console.error(err);
        }
    };

    const markMessagesAsRead = async (chatId) => {
        try {
            await api.post('/chats/read', { chat_id: chatId });
            // Update local chats state to remove badge immediately
            setChats(prevChats => prevChats.map(chat =>
                chat.chat_id === chatId ? { ...chat, unread_count: 0 } : chat
            ));
        } catch (err) {
            console.error("Failed to mark messages as read", err);
        }
    };

    const handleStartChat = async (e) => {
        e.preventDefault();
        try {
            const res = await api.post('/chats', { email: newChatEmail });
            setShowNewChatModal(false);
            setNewChatEmail('');
            loadChats(); // Refresh list
            // Optionally select immediately if we had the full chat object, 
            // but for now let user click it or find it.
            // If it's new it will appear at top.
        } catch (err) {
            alert(err.response?.data?.message || 'Error creating chat');
        }
    };

    const sendMessage = async (e) => {
        e.preventDefault();
        if (!newMessage.trim() || !currentChat) return;

        const tempId = Date.now();
        const content = newMessage;

        // Optimistic update
        const tempMsg = {
            message_id: tempId,
            chat_id: currentChat.chat_id,
            sender_id: user.user_id,
            content: content,
            created_at: new Date().toISOString(),
            status: 'sent'
        };

        setMessages(prev => [...prev, tempMsg]);
        setNewMessage('');

        try {
            await api.post('/chats/messages', {
                chat_id: currentChat.chat_id,
                content: content,
                type: 'text'
            });
            // تحديث قائمة الشات لظهور العلامة الخضراء (عدد الرسائل غير المقروءة من الطرف الآخر)
            loadChats();
        } catch (err) {
            console.error(err);
            // Ideally show error state on message
        }
    };

    // Helper to format time safely
    const formatTime = (dateString) => {
        try {
            return new Date(dateString).toLocaleDateString();
        } catch (e) { return ''; }
    };

    return (
        <div className={`app-container ${currentChat ? 'chat-open' : ''}`}>
            {/* Sidebar */}
            <div className="sidebar">
                <div className="sidebar-header">
                    <div className="header-left">
                        <div className="user-avatar" title={user?.email}>
                            <FaUserCircle size={40} color="#dfe6e9" />
                        </div>
                    </div>
                    <div className="header-right">
                        <button className="icon-btn" onClick={logout} title="Logout">
                            <FaSignOutAlt />
                        </button>
                    </div>
                </div>

                <div className="search-bar">
                    <div className="search-input-wrapper">
                        <FaSearch className="search-icon" />
                        <input type="text" placeholder="Search or start new chat" />
                    </div>
                </div>

                <div className="conversation-list">
                    {chats.map(chat => (
                        <div
                            key={chat.chat_id}
                            className={`conversation-item ${currentChat?.chat_id === chat.chat_id ? 'active' : ''}`}
                            onClick={() => setCurrentChat(chat)}
                        >
                            <div className="chat-avatar">
                                <FaUserCircle size={45} color={chat.display_icon ? '#ccc' : '#dfe6e9'} />
                            </div>
                            <div className="chat-info">
                                <div className="chat-top">
                                    <span className="chat-name">{chat.display_name || chat.other_email}</span>
                                    {chat.last_message_time && (
                                        <span className="chat-date">
                                            {formatTime(chat.last_message_time)}
                                        </span>
                                    )}
                                </div>
                                <div className="chat-bottom">
                                    <span className="last-message">
                                        {chat.last_message ? (chat.last_message.length > 30 ? chat.last_message.substring(0, 30) + '...' : chat.last_message) : 'Start chatting'}
                                    </span>
                                    <span className="chat-badges">
                                        {Number(chat.unread_count) > 0 && <span className="unread-badge">{chat.unread_count}</span>}
                                        {Number(chat.unread_by_other_count) > 0 && (
                                            <span className="unread-by-other-badge" title="لم يقرأها الطرف الآخر">
                                                {chat.unread_by_other_count}
                                            </span>
                                        )}
                                    </span>
                                </div>
                            </div>
                        </div>
                    ))}
                </div>

                {/* Floating Action Button for New Chat */}
                <button className="fab-btn" onClick={() => setShowNewChatModal(true)}>
                    <FaPlus />
                </button>
            </div>

            {/* Chat Window */}
            <div className="chat-window">
                {currentChat ? (
                    <>
                        <div className="chat-header">
                            <button className="back-btn" onClick={() => setCurrentChat(null)}>
                                <FaArrowLeft />
                            </button>
                            <div className="chat-avatar-small">
                                <FaUserCircle size={40} color="#dfe6e9" />
                            </div>
                            <div className="chat-header-info">
                                <h3>{currentChat.display_name || currentChat.other_email}</h3>
                                <span>click here for contact info</span>
                            </div>
                            <div className="chat-header-actions">
                                <FaSearch />
                                <FaEllipsisV />
                            </div>
                        </div>

                        <div className="messages-container">
                            {messages.map((msg, index) => (
                                <MessageBubble
                                    key={index}
                                    message={msg}
                                    isMe={msg.sender_id === user.user_id}
                                />
                            ))}
                            <div ref={messagesEndRef} />
                        </div>

                        <div className="input-area">
                            <form style={{ display: 'flex', width: '100%', alignItems: 'center' }} onSubmit={sendMessage}>
                                <div className="input-wrapper">
                                    <input
                                        type="text"
                                        className="chat-input"
                                        placeholder="Type a message"
                                        value={newMessage}
                                        onChange={(e) => setNewMessage(e.target.value)}
                                    />
                                </div>
                                <button type="submit" className="send-btn">
                                    <FaPaperPlane />
                                </button>
                            </form>
                        </div>
                    </>
                ) : (
                    <div className="welcome-screen">
                        <h2>WhatsApp for Windows</h2>
                        <p>Send and receive messages without keeping your phone online.</p>
                        <p>Use WhatsApp on up to 4 linked devices and 1 phone.</p>
                        <p style={{ marginTop: '20px', fontSize: '12px', color: '#8696a0' }}>
                            <FaCheckDouble style={{ marginRight: '5px' }} /> End-to-end encrypted
                        </p>
                    </div>
                )}
            </div>

            {/* New Chat Modal */}
            {showNewChatModal && (
                <div className="modal-overlay" onClick={() => setShowNewChatModal(false)}>
                    <div className="modal-content" onClick={e => e.stopPropagation()}>
                        <h3>Start New Chat</h3>
                        <form onSubmit={handleStartChat}>
                            <input
                                type="email"
                                placeholder="Email (e.g., user@example.com)"
                                value={newChatEmail}
                                onChange={e => setNewChatEmail(e.target.value)}
                                required
                                style={{ width: '100%', padding: '10px', margin: '15px 0' }}
                            />
                            <div style={{ display: 'flex', justifyContent: 'flex-end', gap: '10px' }}>
                                <button type="button" onClick={() => setShowNewChatModal(false)}>Cancel</button>
                                <button type="submit">Start Chat</button>
                            </div>
                        </form>
                    </div>
                </div>
            )}
        </div>
    );
};

export default Chat;
