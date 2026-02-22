# AI Chat Application (WhatsApp Clone)

## 🔧 Setup & Installation

### 1. Database Setup
The database schema has been automatically imported.
- **Database Name**: `chat_app`
- **Credentials**: User `root`, Password `` (Default XAMPP)

If you need to re-import manually:
```bash
mysql -u root -e "CREATE DATABASE IF NOT EXISTS chat_app"
mysql -u root -D chat_app < database.sql
```

### 2. Backend Configuration (PHP)
- Located in: `c:\xampp\htdocs\php chat1`
- Entry Point: `public/index.php`
- Configuration: `.env` file contains DB and API keys.

**Important**: 
- Make sure your XAMPP Apache server is running.
- Update `AI_API_KEY` in `.env` with your actual OpenAI API key.

### 3. Frontend Setup (React)
- Located in: `frontend/`
- The development server has been started.

If you need to restart it:
```bash
cd frontend
npm run dev
```
Access the app at: `http://localhost:5173`

## 🏗 Architecture

### Backend (Pure PHP MVC)
- **Framework**: Custom MVC (No libraries)
- **Routing**: `core/Router.php` handles routes and CORS.
- **Database**: `core/Database.php` uses PDO Singleton.
- **Auth**: JWT based (`core/JWTHandler.php`).
- **Structure**:
  - `app/controllers`: Logic for Auth, Chat, and AI.
  - `app/models`: Database interactions.
  - `public`: Web root.

### Frontend (React + Vite)
- **Styling**: Pure CSS (WhatsApp Web lookalike).
- **State**: Context API for Authentication.
- **HTTP**: Axios with Interceptors for JWT.

## 🚀 Features
- **Auth**: Login/Register with JWT.
- **Chat**: Real-time-like interface, history preservation.
- **AI**: Integrates with OpenAI API (Context aware - sends last 10 messages).
- **UI**: Responsive, Glassmorphism-inspired, clean WhatsApp style.
