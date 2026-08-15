# 🎨 Katalog Sablon Topi Lampung

> Aplikasi e-commerce untuk manajemen pesanan sablon topi dengan fitur custom design, integrasi pembayaran, dan notifikasi otomatis.

[![Laravel](https://img.shields.io/badge/Laravel-12.x-red)](https://laravel.com)
[![PHP](https://img.shields.io/badge/PHP-8.2+-blue)](https://php.net)
[![Livewire](https://img.shields.io/badge/Livewire-3.6+-purple)](https://livewire.laravel.com)
[![TailwindCSS](https://img.shields.io/badge/TailwindCSS-3.x-teal)](https://tailwindcss.com)
[![License](https://img.shields.io/badge/License-MIT-green)](LICENSE)

---

## 📚 Dokumentasi

Dokumentasi lengkap telah dipisah untuk kemudahan akses:

- 📥 **[Installation Guide](installation.md)** — Panduan instalasi lengkap dari setup hingga deployment
- 👤 **[User Guide](user_guide.md)** — Panduan penggunaan sistem untuk admin dan customer admin
- 🧪 **[Testing Report](TESTING_REPORT.md)** — Laporan testing dan quality assurance

**Quick Start:**
```bash
git clone https://github.com/han5474ni/Katalog-Sablon-Topi-Lampung.git
cd Katalog-Sablon-Topi-Lampung
composer install && npm install
cp .env.example .env
php artisan key:generate
php artisan migrate
composer dev
```

Lihat [installation.md](installation.md) untuk instruksi lengkap.

---

## 📋 Fitur Utama

### 🛒 Manajemen Pesanan
- **Tracking Status**: Monitoring pesanan realtime dari pending hingga completed
- **Notifikasi Otomatis**: Email notifications untuk setiap perubahan status
- **Virtual Account**: Integrasi Midtrans payment gateway
- **Batas Waktu Pembayaran**: Auto-expiration untuk unpaid orders
- **Export Data**: Export laporan pesanan ke Excel

### 🎨 Custom Design
- **Upload Design**: Customer dapat upload design custom (PNG, JPG, AI, PDF)
- **Preview Produk**: Preview design di mockup produk
- **Multiple Uploads**: Support multiple design files
- **Kalkulasi Harga**: Auto-calculate custom design pricing
- **Design Review**: Admin approval workflow untuk custom designs

### 📦 Manajemen Stok
- **Realtime Tracking**: Monitor stok produk secara realtime
- **Auto Reduction**: Pengurangan stok otomatis saat order approved
- **Low Stock Alerts**: Notifikasi saat stok < 10 items
- **Stock History**: Track perubahan stok dengan activity logs

### 📊 Dashboard Admin
- **Analytics Overview**: Statistik penjualan dan revenue
- **Order Management**: Kelola semua pesanan dalam satu dashboard
- **Product Management**: CRUD produk dengan image gallery
- **User Management**: Kelola customer dan admin users
- **Laporan Penjualan**: Export reports dengan filter custom

### 💬 Chatbot & Customer Support
- **N8N Integration**: AI chatbot untuk customer inquiries
- **Product Context**: Chat dengan konteks produk spesifik
- **Admin Chat**: Manual takeover dari chatbot ke admin
- **Chat History**: Archive dan search conversation history

### 👥 User Management
- **Multi-Role**: Super Admin, Admin, Customer roles
- **OAuth Google**: Login dengan Google account
- **Email Verification**: Verifikasi email untuk keamanan
- **Profile Management**: Update profile, avatar, password
- **Activity Tracking**: Log semua user activities

---

## 🛠️ Teknologi

### Backend
- **PHP** 8.2+
- **Laravel** 12.x
- **Livewire** 3.6+ & **Volt** 1.7+
- **MySQL/MariaDB** (Database)
- **Laravel Sanctum** (API authentication)
- **Laravel Socialite** (Google OAuth)

### Frontend
- **TailwindCSS** 3.x (Styling)
- **Alpine.js** (via Livewire)
- **Bootstrap** 5.3+ (Components)
- **Chart.js** (Data visualization)
- **Vite** 6.x (Build tool)

### Integrasi & Services
- **Midtrans** Payment Gateway (Virtual Account)
- **Google OAuth** (Social login)
- **Gmail SMTP** (Email notifications)
- **N8N** (Chatbot automation)
- **Maatwebsite Excel** (Export functionality)
- **Intervention Image** (Image processing)

---

## 💻 Persyaratan Sistem

### Development
```
PHP         >= 8.2
Composer    >= 2.0
Node.js     >= 18.x
NPM         >= 9.x
MySQL       >= 5.7 atau MariaDB >= 10.3
```

### PHP Extensions
```
BCMath, Ctype, cURL, DOM, Fileinfo, Filter, Hash, 
Mbstring, OpenSSL, PDO, Session, Tokenizer, XML, GD/Imagick
```

### Production (Additional)
```
Nginx/Apache Web Server
SSL Certificate (Let's Encrypt)
Redis (recommended untuk caching)
Supervisor (untuk queue workers)
```

Lihat [installation.md](installation.md) untuk detail instalasi.

---

## 🚀 Quick Start

### Development Setup

```bash
# 1. Clone dan install dependencies
git clone https://github.com/han5474ni/Katalog-Sablon-Topi-Lampung.git
cd Katalog-Sablon-Topi-Lampung
composer install
npm install

# 2. Setup environment
cp .env.example .env
php artisan key:generate

# 3. Database setup
php artisan migrate
php artisan db:seed  # Optional: seed dummy data

# 4. Storage dan assets
php artisan storage:link
npm run build

# 5. Jalankan aplikasi
composer dev  # Recommended: runs server + queue + logs + vite
# atau
php artisan serve  # Simple server only
```

Akses aplikasi di: **http://localhost:8000**

---

## 🔒 Keamanan

### Fitur Keamanan Terintegrasi

✅ **Authentication & Authorization**
- Multi-guard system (Web, Admin, API)
- Role-based access control (RBAC)

## 🚀 Deployment

### Shared Hosting

```bash
# 1. Build assets
npm run build

# 2. Optimize Laravel
php artisan config:cache
php artisan route:cache
php artisan view:cache

# 3. Upload via FTP ke public_html

# 4. Set permissions
chmod -R 755 storage bootstrap/cache

# 5. Update .env di server
# 6. Migrate database
php artisan migrate --force
```

### VPS (Ubuntu/Nginx)

**Install Dependencies:**
```bash
sudo apt update && sudo apt upgrade -y
sudo apt install -y php8.2 php8.2-fpm php8.2-mysql \
  php8.2-mbstring php8.2-xml php8.2-bcmath php8.2-gd \
  nginx mysql-server composer git nodejs npm
```

**Clone & Setup:**
```bash
cd /var/www
git clone https://github.com/han5474ni/Katalog-Sablon-Topi-Lampung.git
cd Katalog-Sablon-Topi-Lampung

composer install --no-dev --optimize-autoloader
npm install && npm run build

cp .env.example .env
php artisan key:generate
php artisan migrate --force

sudo chown -R www-data:www-data .
sudo chmod -R 755 .
sudo chmod -R 775 storage bootstrap/cache
```


## 🧪 Testing

```bash
# Jalankan semua tests
php artisan test

# Test specific feature
php artisan test --filter=OrderTest

# Test dengan coverage
php artisan test --coverage

# Parallel testing
php artisan test --parallel
```

Lihat [TESTING_REPORT.md](TESTING_REPORT.md) untuk test results dan coverage.

---

## 📝 API Documentation

### Public Endpoints

```bash
GET  /api/custom-design-prices              # Get custom design prices
GET  /api/product-custom-design-prices/{id} # Get product-specific prices
GET  /api/product/{id}/stock                # Get product stock info
```

### Authenticated Endpoints

```bash
# Notifications
GET  /api/notifications                     # Get all notifications
GET  /api/notifications/unread-count        # Get unread count
POST /api/notifications/{id}/read           # Mark as read
POST /api/notifications/read-all            # Mark all as read

# Chatbot
GET  /api/chatbot/history                   # Get chat history
POST /api/chatbot/send                      # Send message
GET  /api/chatbot/unread-count              # Get unread messages
POST /api/chatbot/mark-read                 # Mark messages as read
```

---

## 🤝 Kontribusi

Kontribusi sangat diterima! Ikuti langkah berikut:

1. **Fork** repository ini
2. Buat **branch** fitur (`git checkout -b feature/AmazingFeature`)
3. **Commit** perubahan (`git commit -m 'feat: Add AmazingFeature'`)
4. **Push** ke branch (`git push origin feature/AmazingFeature`)
5. Buat **Pull Request**
---

Project ini dibangun dengan:
- [Laravel](https://laravel.com) - PHP Framework
- [Livewire](https://livewire.laravel.com) - Full-stack framework
- [TailwindCSS](https://tailwindcss.com) - Utility-first CSS
- [Midtrans](https://midtrans.com) - Payment Gateway
- [N8N](https://n8n.io) - Workflow Automation

---

<div align="center">

**Dibuat dengan ❤️ untuk kemudahan manajemen bisnis sablon topi**

[⬆ Back to Top](#-katalog-sablon-topi-lampung)

</div>
