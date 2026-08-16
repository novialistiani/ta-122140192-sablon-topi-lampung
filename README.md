# Katalog Sablon Topi Lampung

Sistem e-commerce berbasis web untuk pengelolaan katalog, pemesanan, dan transaksi usaha sablon topi di Lampung.

## 📚 Dokumentasi

- 📥 [Installation Guide](installation.md) — Panduan instalasi lengkap
- 👤 [User Guide](user_guide.md) — Panduan penggunaan sistem untuk admin dan customer

## 🚀 Quick Start

```bash
# 1. Clone dan install dependencies
git clone https://github.com/novialistiani/ta-122140192-sablon-topi-lampung.git
cd ta-122140192-sablon-topi-lampung
composer install
npm install

# 2. Setup environment
cp .env.example .env
php artisan key:generate

# 3. Database setup
php artisan migrate
php artisan db:seed  # Isi akun admin default & data dummy

# 4. Storage dan assets
php artisan storage:link
npm install

# 5. Jalankan aplikasi
composer dev  # Menjalankan server + queue + vite sekaligus
```

Akses aplikasi di: http://127.0.0.1:8000

## 📋 Fitur Utama

### 🛒 Manajemen Pesanan
- Tracking status pesanan realtime dari pending hingga completed
- Notifikasi otomatis untuk setiap perubahan status
- Integrasi Virtual Account (Midtrans Payment Gateway)
- Auto-expiration untuk pesanan yang belum dibayar
- Ekspor laporan pesanan ke Excel

### 🎨 Custom Design
- Customer dapat mengunggah desain custom (PNG, JPG, AI, PDF)
- Kalkulasi otomatis harga custom design
- Alur persetujuan (approval) desain oleh admin

### 📦 Manajemen Stok
- Monitoring stok produk secara realtime
- Pengurangan stok otomatis saat pesanan disetujui
- Notifikasi stok menipis (low stock alert)
- Riwayat perubahan stok (activity log)

### 🧾 Point of Sale (POS)
- Pencatatan transaksi pelanggan walk-in (datang langsung ke toko) oleh admin
- Pengurangan stok otomatis mengikuti transaksi POS
- Terintegrasi dengan data penjualan dan laporan admin

### 📊 Dashboard Admin
- Ringkasan statistik penjualan dan pendapatan
- Manajemen seluruh pesanan dalam satu dashboard
- Manajemen produk (CRUD) dengan galeri gambar
- Manajemen pengguna (customer dan admin)
- Laporan penjualan dengan filter kustom

### 💬 Chatbot & Customer Support
- Chatbot berbasis pencocokan kata kunci (keyword matching) untuk menjawab pertanyaan umum seputar harga, cara pemesanan, custom design, pembayaran, dan pengiriman
- Riwayat percakapan tersimpan

### 👥 Manajemen Pengguna
- Multi-role: Super Admin, Admin, Customer
- Login dengan Google OAuth
- Verifikasi email
- Manajemen profil (update data, avatar, password)

## 🛠️ Teknologi

**Backend**
- PHP 8.2+
- Laravel 12.x
- Livewire 3.6+ & Volt 1.7+
- MySQL/MariaDB
- Laravel Sanctum (autentikasi API)
- Laravel Socialite (Google OAuth)

**Frontend**
- TailwindCSS 3.x
- Alpine.js (via Livewire)
- Bootstrap 5.3+
- Chart.js (visualisasi data)
- Vite 6.x

**Integrasi**
- Midtrans Payment Gateway (Virtual Account)
- Google OAuth
- Gmail SMTP (notifikasi email)
- Maatwebsite Excel (fitur ekspor)
- Intervention Image (pemrosesan gambar)

## 💻 Persyaratan Sistem

```
PHP         >= 8.2
Composer    >= 2.0
Node.js     >= 18.x
NPM         >= 9.x
MySQL       >= 5.7 atau MariaDB >= 10.3
```

**PHP Extensions yang dibutuhkan:**
BCMath, Ctype, cURL, DOM, Fileinfo, Filter, Hash, Mbstring, OpenSSL, PDO, Session, Tokenizer, XML, GD, Zip

## 🧪 Testing

```bash
# Jalankan semua tests
php artisan test

# Test fitur tertentu
php artisan test --filter=OrderTest
```

## 📝 API Documentation

**Public Endpoints**
```
GET  /api/custom-design-prices              # Ambil daftar harga custom design
GET  /api/product-custom-design-prices/{id} # Harga custom design per produk
GET  /api/product/{id}/stock                # Info stok produk
```

**Authenticated Endpoints**
```
GET  /api/notifications                     # Daftar notifikasi
GET  /api/notifications/unread-count        # Jumlah notifikasi belum dibaca
POST /api/notifications/{id}/read           # Tandai sudah dibaca
POST /api/notifications/read-all            # Tandai semua sudah dibaca

GET  /api/chatbot/history                   # Riwayat chat
POST /api/chatbot/send                      # Kirim pesan
```

---

**Tugas Akhir** — Novia Listiani (122140192)
Sistem Informasi, Institut Teknologi Sumatera