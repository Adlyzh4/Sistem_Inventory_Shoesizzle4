# 🧾 ShoeSizzle Inventory Management System

**ShoeSizzle** adalah sistem manajemen inventory berbasis web untuk toko sepatu yang membantu mengelola data barang masuk, barang keluar, retur barang, dan stok dengan lebih efisien. Sistem ini dibangun menggunakan **PHP**, **MySQL**, dan **Bootstrap** dengan fitur interaktif dan antarmuka modern.

## 🚀 Fitur Utama

- 🔐 Autentikasi dan Role-based Access
- 📦 Manajemen Barang (CRUD)
- 📥 Barang Masuk & 📤 Barang Keluar
- ♻️ Retur Barang ke Distributor
- 🔔 Notifikasi Real-time (Stok Rendah, Aktivitas Admin, dll)
- 📊 Dashboard Interaktif
  - Ringkasan stok masuk, keluar, retur, stok rendah
  - Grafik Stok Masuk vs Keluar
  - Chart Top 10 Barang Paling Banyak Keluar
- 🧾 Log Aktivitas Admin
- 🔍 Fitur Pencarian & Filter Barang

## 🛠️ Teknologi yang Digunakan

| Teknologi       | Keterangan                         |
|----------------|-------------------------------------|
| PHP            | Backend processing                  |
| MySQL          | Basis data                          |
| Bootstrap 5    | Antarmuka responsif & modern        |
| Chart.js       | Visualisasi data di dashboard       |
| JavaScript     | Fitur interaktif (notif, chart, dsb)|
| Slim Framework | (Opsional) REST API untuk notifikasi|

## 📁 Struktur Direktori

```
shoesizzle_web/
├── auth/                # Login & session
├── config/              # File konfigurasi database
├── includes/            # Header, footer, navbar, fungsi umum
├── pages/
│   ├── dashboard/       # Dashboard utama
│   ├── barang/          # Barang CRUD
│   ├── stok_masuk/      # Barang masuk
│   ├── stok_keluar/     # Barang keluar
│   ├── retur_barang/    # Retur barang
│   ├── notifikasi/      # Fitur notifikasi
│   └── log_aktivitas/   # Catatan aktivitas admin
└── public/              # Aset umum (gambar, css, js)
```

## 📷 Tampilan

> 🚧 Tambahkan screenshot di folder `public/images/` lalu sisipkan di sini untuk memperkuat presentasi sistem

```
![Dashboard](public/images/dashboard.png)
![Manajemen Barang](public/images/barang.png)
![Stok Masuk](public/images/stok-masuk.png)
```

## 📦 Instalasi

1. Clone repo ini:

```bash
git clone https://github.com/namamu/shoesizzle_web.git
```

2. Import file database SQL ke phpMyAdmin (nama database: `shoesizzle_db`)

3. Konfigurasi koneksi database:

```php
// config/database.php
$host = 'localhost';
$db   = 'shoesizzle_db';
$user = 'root';
$pass = '';
```

4. Jalankan server lokal via XAMPP/MAMP dan buka:

```
http://localhost/shoesizzle_web/
```

## 🧪 Akun Demo

| Role    | username                         | Password       |
|---------|----------------------------------|----------------|
| Admin   | admin2                           | admin089       |

## 👨‍💻 Kontributor

- **Siganteng** - Mahasiswa Sistem Informasi  

## 📄 Lisensi

Open-source dan dapat digunakan untuk keperluan tugas, riset, atau pengembangan toko UMKM pribadi.

---

> ✨ Sistem ini masih bisa dikembangkan lebih jauh dengan fitur seperti laporan PDF, export Excel, REST API, atau integrasi dengan WhatsApp untuk retur otomatis.