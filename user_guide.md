# 👤 User Guide - Admin Panel

Panduan lengkap penggunaan sistem **Katalog Sablon Topi Lampung** untuk admin dan customer admin.

---

## 📖 Daftar Isi

1. [Login & Dashboard](#-login--dashboard)
2. [Manajemen Pesanan](#-manajemen-pesanan)
3. [Manajemen Produk](#-manajemen-produk)
4. [Custom Design](#-custom-design)
5. [User Management](#-user-management)
6. [Analytics & Laporan](#-analytics--laporan)
7. [Chatbot & Chat](#-chatbot--chat)
8. [Activity Logs](#-activity-logs)
9. [Notifikasi](#-notifikasi)
10. [Pengaturan Profil](#-pengaturan-profil)
11. [Troubleshooting](#-troubleshooting)

---

## 🔐 Login & Dashboard

### Login Admin

1. Akses halaman admin: **`/admin/login`**
2. Masukkan **email** dan **password** admin
3. Klik tombol **"Login"**

**Role akses:**
- **Super Admin**: Akses penuh ke semua fitur
- **Admin**: Akses terbatas sesuai permission
- **Customer**: Tidak bisa akses admin panel

### Dashboard Overview

Setelah login, Anda akan melihat:

- **📊 Statistik Pesanan**: Total pesanan, pending, approved, rejected
- **💰 Revenue**: Total pendapatan dan grafik penjualan
- **📦 Stok Produk**: Alert produk stok rendah
- **📈 Grafik Analytics**: Visualisasi data penjualan
- **🔔 Notifikasi Terbaru**: Notifikasi pesanan baru dan aktivitas

**Navigasi Utama:**
```
├── Dashboard
├── Orders (Pesanan)
├── Products (Produk)
├── Custom Design
├── User Management
├── Analytics
├── Chat/Chatbot
├── Activity Logs
└── Settings
```

---

## 📦 Manajemen Pesanan

### Melihat Daftar Pesanan

**Menu**: Pesanan → Order List

**Fitur:**
- Filter berdasarkan status: All, Pending, Approved, Rejected, Completed
- Search by order ID, customer name, atau product name
- Export data ke Excel
- View detail pesanan

### Status Pesanan

| Status | Deskripsi | Action yang Tersedia |
|--------|-----------|---------------------|
| **Pending** | Pesanan baru, menunggu pembayaran | Approve, Reject |
| **Approved** | Pesanan disetujui, dalam produksi | Update ke Complete |
| **Rejected** | Pesanan ditolak | View only |
| **Completed** | Pesanan selesai | View history |
| **Cancelled** | Dibatalkan customer | View only |

### Detail Pesanan

**Informasi yang ditampilkan:**

1. **Customer Information**
   - Nama, email, nomor telepon
   - Alamat pengiriman lengkap

2. **Product Details**
   - Nama produk dan quantity
   - Harga satuan dan total
   - Gambar produk

3. **Custom Design** (jika ada)
   - File design yang diupload customer
   - Custom design price breakdown
   - Preview design

4. **Payment Information**
   - Metode pembayaran (Virtual Account/Transfer)
   - Status pembayaran
   - Bukti transfer (jika ada)
   - Payment deadline

5. **Order Timeline**
   - Created at
   - Approved/Rejected at
   - Completed at
   - Status history

### Actions pada Pesanan

#### 1. Approve Pesanan
```
1. Buka detail pesanan
2. Klik tombol "Approve Order"
3. Konfirmasi approval
4. System otomatis:
   - Update status ke "Approved"
   - Kurangi stok produk
   - Kirim email notifikasi ke customer
   - Log activity
```

#### 2. Reject Pesanan
```
1. Buka detail pesanan
2. Klik tombol "Reject Order"
3. Masukkan alasan penolakan
4. Konfirmasi rejection
5. System otomatis:
   - Update status ke "Rejected"
   - Kirim email notifikasi dengan alasan
   - Log activity
```

#### 3. Complete Pesanan
```
1. Pastikan pesanan sudah approved
2. Setelah proses produksi selesai
3. Klik "Mark as Completed"
4. System update status ke "Completed"
5. Customer menerima notifikasi
```

### Virtual Account Management

**Menu**: Orders → Virtual Accounts

**Fitur:**
- View semua virtual accounts yang ter-generate
- Monitor status pembayaran
- Check expiry date
- Manual update payment status (jika diperlukan)

---

## 🛍️ Manajemen Produk

### Daftar Produk

**Menu**: Products → Product Management

**Fitur:**
- View semua produk dalam grid/list view
- Search product by name
- Filter by category/subcategory
- Quick edit stock dan price
- Bulk actions (coming soon)

### Tambah Produk Baru

```
1. Klik tombol "Add New Product"
2. Isi form:
   ├── Product Name (required)
   ├── Category & Subcategory (required)
   ├── Description
   ├── Price (required)
   ├── Stock (required)
   ├── Colors (multiple selection)
   └── Product Images (max 5 images)
3. Upload gambar produk (format: JPG, PNG, max 5MB)
4. Klik "Save Product"
5. Product langsung tampil di catalog customer
```

**Tips:**
- Gunakan gambar berkualitas tinggi (min 800x800px)
- Deskripsi yang detail meningkatkan konversi
- Set stok minimum alert untuk notifikasi

### Edit Produk

```
1. Pada product list, klik "Edit" pada produk
2. Update informasi yang diperlukan
3. Ubah gambar (optional)
4. Klik "Update Product"
```

### Manajemen Stok

**Auto Stock Reduction:**
- Stok berkurang otomatis saat pesanan di-approve
- Alert muncul jika stok < 10 items
- Stok tidak berkurang jika order rejected/cancelled

**Manual Stock Update:**
```
1. Edit produk
2. Update field "Stock"
3. Save changes
4. Activity log mencatat perubahan
```

### Manajemen Kategori & Subcategory

**Menu**: Products → Categories

**Actions:**
- Add new category/subcategory
- Edit existing
- Delete (jika tidak ada produk terkait)
- Reorder categories

### Manajemen Warna (Colors)

**Menu**: Products → Colors

**Actions:**
- Add new color option
- Edit color name & hex code
- Delete unused colors
- Apply colors ke multiple products

---

## 🎨 Custom Design

### Custom Design Prices

**Menu**: Custom Design → Price Management

**Pricing Structure:**
```
├── Design Type
│   ├── Simple Design (1-2 warna)
│   ├── Medium Design (3-4 warna)
│   └── Complex Design (5+ warna)
├── Print Location
│   ├── Front Only
│   ├── Back Only
│   └── Front + Back
└── Additional Services
    ├── Rush Order (+30%)
    └── Design Revision
```

**Set Custom Price:**
```
1. Menu: Custom Design → Prices
2. Pilih product yang ingin di-set custom price
3. Set base price untuk masing-masing kategori
4. Save changes
5. Price langsung berlaku di customer page
```

### Review Custom Design Orders

**Menu**: Orders → Custom Design Orders

**Workflow:**
```
1. Customer upload design di product page
2. Admin menerima notifikasi order baru
3. Review design file:
   ├── Check file format (PNG, JPG, AI, PDF)
   ├── Verify design quality
   └── Confirm print specifications
4. Approve atau request revision:
   ├── Approve: Process to production
   └── Revision: Contact customer via chat
5. Track order seperti order regular
```

**Design File Management:**
- Download design files untuk produksi
- Archive design files per order
- Format support: PNG, JPG, AI, PDF, CDR

---

## 👥 User Management

**Menu**: User Management

### Fitur User Management

**View All Users:**
- List semua registered users
- Filter by role (Customer, Admin)
- Search by name/email
- View registration date

**User Details:**
```
├── Personal Information
│   ├── Name, Email, Phone
│   ├── Avatar
│   └── Registration date
├── Order History
│   ├── Total orders
│   ├── Total spent
│   └── Last order date
└── Account Status
    ├── Email verified
    ├── Account active/suspended
    └── Login history
```

### User Actions

#### 1. View Customer Detail
```
1. Klik nama customer di user list
2. Lihat informasi lengkap:
   - Profile & contact
   - Alamat pengiriman
   - Order history
   - Payment history
3. Export customer data to PDF
```

#### 4. Export User Data
```
1. Filter users sesuai kebutuhan
2. Klik "Export Users"
3. Download Excel file
4. Data: Name, Email, Total Orders, Total Spent
```

---

## 📊 Analytics & Laporan

**Menu**: Analytics

### Dashboard Analytics

**Metrics yang ditampilkan:**

1. **Sales Overview**
   - Total revenue (hari ini, minggu ini, bulan ini)
   - Number of orders
   - Average order value
   - Conversion rate

2. **Product Performance**
   - Best selling products
   - Products with low stock
   - Products with no sales (30 days)
   - Revenue by category

3. **Customer Analytics**
   - New customers this month
   - Returning customers
   - Customer lifetime value
   - Top customers by spending

4. **Order Status Breakdown**
   - Pending orders
   - Processing orders
   - Completed orders
   - Cancelled/rejected orders

### Grafik & Visualisasi

**Chart Types:**
- **Line Chart**: Revenue trend over time
- **Bar Chart**: Sales by product category
- **Pie Chart**: Order status distribution
- **Donut Chart**: Payment method breakdown

### Filter & Export

**Filter Options:**
```
├── Date Range
│   ├── Today
│   ├── Last 7 days
│   ├── Last 30 days
│   ├── This month
│   └── Custom range
├── Product Category
└── Order Status
```

## 💬 Chatbot & Chat

### Customer Chat Management

**Menu**: Chat → Conversations

**Fitur:**

├── Chat Interface
│   ├── Real-time messaging
│   ├── Product context (jika chat dari product page)
│   ├── Customer info sidebar
│   └── Quick replies


### Chatbot Settings

**Menu**: Settings → Chatbot

**Configuration:**
```
1. N8N Integration
   ├── Webhook URL setup
   ├── API key configuration
   └── Test connection
   
2. Auto Responses
   ├── Common questions & answers
   ├── Business hours notification
   └── Out of office message
   
3. Escalation Rules
   ├── When to transfer to human agent
   ├── Keywords for urgent inquiries
   └── Admin notification triggers
```

**Best Practices:**
- Response dalam 5 menit untuk customer satisfaction
- Gunakan quick replies untuk pertanyaan umum
- Set auto-response untuk diluar jam kerja
- Review chat logs untuk improve bot responses

---

## 📝 Activity Logs

**Menu**: Activity Logs

### Apa yang di-log?

**System Activities:**
```
✅ User login/logout
✅ Product CRUD operations
✅ Order status changes
✅ Stock updates
✅ User management actions
✅ Payment confirmations
✅ Email sent notifications
✅ Configuration changes
```

### Log Details

**Information Captured:**
- **Who**: User yang melakukan action
- **What**: Action yang dilakukan
- **When**: Timestamp (date & time)
- **Where**: IP address & location
- **Details**: Before/after values (untuk updates)

### Filter & Search Logs

```
1. Filter by date range
2. Filter by user (admin)
3. Filter by action type
4. Search by keyword
5. Export filtered logs to Excel
```

**Use Cases:**
- Audit trail untuk compliance
- Troubleshooting errors
- Monitor admin activities
- Security incident investigation

---

## 🔔 Notifikasi

### Types of Notifications

**Order Notifications:**
- 🛒 New order received
- ✅ Order approved
- ❌ Order rejected
- ✓ Order completed
- 💳 Payment received

**Product Notifications:**
- ⚠️ Low stock alert (< 10 items)
- 📦 Out of stock
- 🆕 New product added

**User Notifications:**
- 👤 New user registration
- 🔐 Password reset request
- 💬 New chat message

**System Notifications:**
- ⚙️ Configuration changes
- 🔒 Security alerts
- 📊 Weekly report ready

### Notification Settings

**Menu**: Profile → Notification Preferences

**Configuration:**
```
✓ Email notifications
✓ In-app notifications
```

**Manage Notifications:**
- Mark as read
- Mark all as read
- Delete notification
- Filter by type
- Mute specific notification types

---

## ⚙️ Pengaturan Profil

**Menu**: Profile atau klik avatar di navbar

### Edit Profile Information

**Editable Fields:**
- Full name
- Email (requires verification)
- Phone number
- Profile photo/avatar
- Bio (optional)

**Update Process:**
```
1. Klik "Edit Profile"
2. Update informasi yang diperlukan
3. Upload avatar baru (optional)
   - Max size: 2MB
   - Format: JPG, PNG
4. Klik "Save Changes"
5. Email verification jika ubah email
```

### Change Password

```
1. Menu: Profile → Security
2. Masukkan current password
3. Masukkan new password
4. Confirm new password
5. Klik "Update Password"
6. Logout otomatis, login dengan password baru
```

**Password Requirements:**
- Minimum 8 characters
- Include uppercase & lowercase
- Include numbers
- Include special characters (recommended)

## 📚 Additional Resources

- 📖 **Installation Guide**: [installation.md](installation.md)
- 🔒 **Security Best Practices**: [README.md](README.md#-keamanan)
- 🚀 **Deployment Guide**: [README.md](README.md#-deployment) 

---

## 💡 Tips & Best Practices

### Untuk Admin

✅ **Response Time**: Balas chat customer dalam 5 menit untuk customer satisfaction tinggi

✅ **Order Processing**: Approve/reject orders dalam 24 jam untuk avoid customer complaints

✅ **Stock Management**: Update stock secara berkala, set minimum stock alerts


**Selamat menggunakan Katalog Sablon Topi Lampung! 🎉**

Untuk pertanyaan lebih lanjut, hubungi tim developer atau buat issue di GitHub repository.
