-- Users table
-- ALTER TABLE users DROP COLUMN phone_number, DROP COLUMN country_code, ADD COLUMN email VARCHAR(100) NOT NULL UNIQUE AFTER user_id;
CREATE TABLE IF NOT EXISTS users (
    user_id INT AUTO_INCREMENT PRIMARY KEY,
    email VARCHAR(100) NOT NULL UNIQUE,
    name VARCHAR(50),
    about VARCHAR(139) DEFAULT 'Hey there! I am using WhatsApp.',
    avatar_url VARCHAR(255),
    is_online BOOLEAN DEFAULT FALSE,
    last_seen TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Devices table for multi-device support
CREATE TABLE IF NOT EXISTS devices (
    device_id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    device_uuid VARCHAR(64) NOT NULL, -- Unique identifier for the device
    device_name VARCHAR(50), -- e.g., "Chrome Windows", "iPhone 13"
    push_token TEXT, -- For FCM/APNS
    last_active TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(user_id) ON DELETE CASCADE,
    UNIQUE KEY unique_device_per_user (user_id, device_uuid)
);

-- Contacts (Address book sync)
CREATE TABLE IF NOT EXISTS contacts (
    user_id INT NOT NULL,
    contact_phone VARCHAR(20) NOT NULL, -- Normalized phone number
    local_name VARCHAR(100), -- Name as saved in user's phone
    contact_user_id INT, -- Linked if the contact is registered
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(user_id) ON DELETE CASCADE,
    FOREIGN KEY (contact_user_id) REFERENCES users(user_id) ON DELETE SET NULL,
    UNIQUE KEY unique_contact (user_id, contact_phone)
);

-- Chats (Groups and Individual)
CREATE TABLE IF NOT EXISTS chats (
    chat_id INT AUTO_INCREMENT PRIMARY KEY,
    type ENUM('individual', 'group') NOT NULL DEFAULT 'individual',
    subject VARCHAR(100), -- Group Name
    description TEXT, -- Group Description
    icon_url VARCHAR(255),
    created_by INT, -- Creator User ID
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (created_by) REFERENCES users(user_id) ON DELETE SET NULL
);

-- Chat Participants
CREATE TABLE IF NOT EXISTS chat_participants (
    chat_id INT NOT NULL,
    user_id INT NOT NULL,
    role ENUM('member', 'admin') DEFAULT 'member',
    joined_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (chat_id) REFERENCES chats(chat_id) ON DELETE CASCADE,
    FOREIGN KEY (user_id) REFERENCES users(user_id) ON DELETE CASCADE,
    PRIMARY KEY (chat_id, user_id)
);

-- Messages
CREATE TABLE IF NOT EXISTS messages (
    message_id INT AUTO_INCREMENT PRIMARY KEY,
    chat_id INT NOT NULL,
    sender_id INT NOT NULL,
    type ENUM('text', 'image', 'video', 'audio', 'document', 'system') DEFAULT 'text',
    content TEXT, -- Encrypted content or plain text depending on implementation phase
    media_url VARCHAR(255),
    reply_to_message_id INT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (chat_id) REFERENCES chats(chat_id) ON DELETE CASCADE,
    FOREIGN KEY (sender_id) REFERENCES users(user_id) ON DELETE CASCADE,
    FOREIGN KEY (reply_to_message_id) REFERENCES messages(message_id) ON DELETE SET NULL
);

-- Message Status (The ticks: sent, delivered, read)
CREATE TABLE IF NOT EXISTS message_status (
    status_id INT AUTO_INCREMENT PRIMARY KEY,
    message_id INT NOT NULL,
    user_id INT NOT NULL, -- The recipient
    status ENUM('sent', 'delivered', 'read') DEFAULT 'sent',
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (message_id) REFERENCES messages(message_id) ON DELETE CASCADE,
    FOREIGN KEY (user_id) REFERENCES users(user_id) ON DELETE CASCADE,
    UNIQUE KEY unique_message_status (message_id, user_id)
);

-- Removed OTP verification table as we're using simple email registration
