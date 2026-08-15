# 📥 Installation Guide

Panduan lengkap instalasi **Katalog Sablon Topi Lampung** dari awal hingga siap digunakan.

---

## 📋 Persyaratan Sistem

### Minimum Requirements
- **PHP** >= 8.2
- **Composer** >= 2.0
- **Node.js** >= 18.x
- **NPM** >= 9.x
- **MySQL** >= 5.7 atau **MariaDB** >= 10.3
- **Web Server**: Apache/Nginx

### PHP Extensions Required
```
BCMath, Ctype, cURL, DOM, Fileinfo, Filter, Hash, 
Mbstring, OpenSSL, PDO, Session, Tokenizer, XML, GD/Imagick
```

### Tools Development (Recommended)
- **Git** >= 2.0
- **Laragon/XAMPP/Wamp** (Windows)
- **VS Code** atau IDE favorit
- **PhpMyAdmin/TablePlus** (Database management)

---

## 🚀 Instalasi Development

### 1. Clone Repository

```bash
git clone https://github.com/han5474ni/Katalog-Sablon-Topi-Lampung.git
cd Katalog-Sablon-Topi-Lampung
```

### 2. Install Dependencies

```bash
# Install PHP dependencies
composer install

# Install Node.js dependencies
npm install
```

**Troubleshooting:**
- Jika `composer install` error, pastikan PHP extensions sudah aktif
- Jika `npm install` gagal, coba hapus `node_modules` dan `package-lock.json` lalu ulangi

### 3. Setup Environment File

```bash
# Windows
copy .env.example .env

# Linux/Mac
cp .env.example .env

# Generate application key
php artisan key:generate
```

### 4. Konfigurasi Database

#### 4.1. Buat Database
Buat database baru melalui PhpMyAdmin atau command line:

```sql
CREATE DATABASE katalog_sablon_topi CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
```

#### 4.2. Konfigurasi File .env
Edit file `.env` dan sesuaikan dengan konfigurasi database Anda:

```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=katalog_sablon_topi
DB_USERNAME=root
DB_PASSWORD=
```

#### 4.3. Jalankan Migrations
```bash
# Jalankan migrations untuk membuat tabel
php artisan migrate

# (Optional) Seed data dummy untuk testing
php artisan db:seed
```

### 5. Storage Configuration

```bash
# Buat symbolic link untuk storage
php artisan storage:link

# Pastikan folder storage memiliki permission yang benar
# Windows: biasanya tidak perlu diatur
# Linux/Mac:
chmod -R 775 storage bootstrap/cache
```

### 6. Build Assets

```bash
# Development mode (dengan hot reload)
npm run dev

# Production mode (optimized)
npm run build
```

### 7. Jalankan Aplikasi

#### Opsi 1: Laravel Development Server
```bash
php artisan serve
```
Akses aplikasi di: **http://localhost:8000**

#### Opsi 2: Composer Dev Script (Recommended)
```bash
composer dev
```
Script ini akan menjalankan:
- PHP development server
- Queue listener
- Laravel Pail (logs)
- Vite dev server

Secara bersamaan dengan warna yang berbeda untuk memudahkan monitoring.

---

## 🔧 Konfigurasi Tambahan

### Google OAuth Setup

1. Buka [Google Cloud Console](https://console.cloud.google.com/)
2. Buat project baru atau pilih project existing
3. Aktifkan **Google+ API** atau **Google OAuth API**
4. Buat **OAuth 2.0 Credentials**:
   - Application type: Web application
   - Authorized redirect URIs: `http://localhost:8000/auth/google/callback`
5. Copy **Client ID** dan **Client Secret**
6. Tambahkan ke file `.env`:

```env
GOOGLE_CLIENT_ID=your-client-id
GOOGLE_CLIENT_SECRET=your-client-secret
GOOGLE_REDIRECT_URI=http://localhost:8000/auth/google/callback
```

### Email Configuration (Gmail SMTP)

1. Aktifkan **2-Factor Authentication** di Google Account
2. Generate **App Password**:
   - Buka: https://myaccount.google.com/apppasswords
   - Pilih "Mail" dan "Other (Custom name)"
   - Copy password yang digenerate
3. Tambahkan ke `.env`:

```env
MAIL_MAILER=smtp
MAIL_HOST=smtp.gmail.com
MAIL_PORT=587
MAIL_USERNAME=your-email@gmail.com
MAIL_PASSWORD=your-generated-app-password
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS=your-email@gmail.com
MAIL_FROM_NAME="${APP_NAME}"
```

### Midtrans Payment Gateway 

Untuk testing payment gateway:

```env
MIDTRANS_SERVER_KEY=your-server-key
MIDTRANS_CLIENT_KEY=your-client-key
MIDTRANS_IS_PRODUCTION=false
MIDTRANS_IS_SANITIZED=true
MIDTRANS_IS_3DS=true
```

Dapatkan credentials di [Midtrans Dashboard](https://dashboard.midtrans.com/)

---

## ✅ Testing Instalasi

### 1. Jalankan Tests
```bash
php artisan test
```

### 2. Check Application
- Akses: `http://localhost:8000`
- Login: gunakan kredensial dari seeder (jika ada)
- Test fitur utama: register, login, browse products

### 3. Verify Configuration
```bash
# Check environment
php artisan about

# Check routes
php artisan route:list

# Clear cache jika ada masalah
php artisan optimize:clear
```

---


## 📚 Next Steps

Setelah instalasi selesai:

1. ✅ **Setup Admin Account**: Buat akun admin melalui seeder atau manual
2. 📖 **Baca User Guide**: Lihat [user_guide.md](user_guide.md) untuk panduan penggunaan
3. 🔒 **Security Checklist**: Review security best practices di [README.md](README.md#-keamanan)
4. 🚀 **Deploy ke Production**: Lihat panduan deployment di [README.md](README.md#-deployment)

---

## 📞 Butuh Bantuan?

- 📖 **Dokumentasi Lengkap**: [README.md](README.md)
- 🐛 **Report Issues**: [GitHub Issues](https://github.com/han5474ni/Katalog-Sablon-Topi-Lampung/issues)
- 💬 **Community Support**: Diskusi dengan developer lain

---

**Happy Coding! 🎉**
