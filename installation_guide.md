***Note:** This document was created for those who want to try this repository's project locally on their desktop.*

# How to install

## Requirements
To run this program you must have already installed:
1. PHP 8.2+
2. Composer
3. Node.js 20.19+
4. npm
5. git

Optional: svelte

## Installation

### Git 
- Windows: download from https://git-scm.com/download/win then install.
- macOS: `brew install git`
- Linux: `sudo apt update && sudo apt install git -y`

### PHP + Composer
**Windows**
1. Download PHP 8.2/8.3 from https://windows.php.net/download/ then extract it to `C:\php`.
2. Add `C:\php` to the `PATH` Environment Variable.
3. Download and run the Composer installer from https://getcomposer.org/download/.
**MacOS**
```bash
brew install php composer
```
**Linux (Debian/Ubuntu)**
```bash
sudo apt update
sudo apt install php8.3 php8.3-cli php8.3-sqlite3 php8.3-mbstring php8.3-xml php8.3-curl unzip -y
curl -sS https://getcomposer.org/installer | php
sudo mv composer.phar /usr/local/bin/composer
```

### Node.js + npm
**Windows**
```powershell
nvm install 22.12.0
nvm use 22.12.0
```

**macOS/Linux**
```bash
curl -o- https://raw.githubusercontent.com/nvm-sh/nvm/v0.40.0/install.sh | bash
nvm install 22
nvm use 22
```


## Setup

### 1. Clone the Repository
```bash
git clone https://github.com/ahmadfaiz122/kata-faiz-kumpul-di-FC
cd kata-faiz-kumpul-di-FC
```

### 2. Set Up the Backend
**1. Install composer dependencies**
```bash
cd backend
composer install
```
**2. Create the .env file**
```env
APP_NAME=SkillSwapp
APP_ENV=local
APP_KEY=
APP_DEBUG=true
APP_URL=http://localhost:8000

FRONTEND_URL=http://localhost:5173

DB_CONNECTION=sqlite

SESSION_DRIVER=database
CACHE_STORE=database
QUEUE_CONNECTION=database

GOOGLE_CLIENT_ID=
GOOGLE_CLIENT_SECRET=
GOOGLE_REDIRECT_URI=http://localhost:8000/auth/google/callback
```
**3. Generate the app key**
```bash
php artisan key:generate
```
**4. Migrate the database**
```bash
php artisan migrate
```
Note: Running migrate should automatically create the database
**4. Set up Google OAuth**
 *Obtain GOOGLE_CLIENT_ID and GOOGLE_CLIENT_SECRET*
1. Open Google Cloud Console and sign in using your Google account.
2. In the top-left corner, click the project selector menu and create a new project (name and organization are up to you)
3. In the top-left corner, select the Navigation menu -> API & Services -> OAuth consent screen -> select the Clients tab
4. Select *Create Client*
5. Fill the form:
	- Application type: Web Application
	- Name: Up to you
	- Authorized Javascript Origins: `http://localhost:5173/`
	- Authorized Redirect URIs: `http://localhost:8000/auth/google/callback`
  *In the `.env` file add:*
  ```bash
GOOGLE_CLIENT_ID=(the GOOGLE_CLIENT_ID you obtained)
GOOGLE_CLIENT_SECRET=(the GOOGLE_CLIENT_SECRET you obtained)
GOOGLE_REDIRECT_URI=http://localhost:8000/auth/google/callback
  ```

### 3. Set Up the Frontend
**1. Install npm dependencies**
```bash
cd frontend
npm install
```
**2. Create the .env file**
```env
VITE_API_URL=http://localhost:8000
```
**3. Run the frontend**
```bash
npm run dev
```
The frontend will run at **http://localhost:5173**


## Running the Application Day-to-Day (after the initial setup is complete)
```bash
# Terminal 1
cd backend
php artisan serve

# Terminal 2
cd frontend
npm run dev
```

Open `http://localhost:5173` in your browser.
