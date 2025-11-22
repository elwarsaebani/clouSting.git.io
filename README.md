# ClouSting - Layanan Jasa Cloud & Hosting

Proyek ini adalah aplikasi website fullstack sederhana berbasis PHP native dengan konsep layanan Cloud & Hosting bernama **ClouSting**. Aplikasi menyediakan peran Admin dan Customer untuk mengelola paket hosting, pelanggan, serta pesanan.

## Fitur Utama

### Publik
- Landing page modern dengan hero, fitur, paket harga, dan CTA.
- Halaman Tentang Kami dan Kontak.
- Banner promo di bawah navbar dengan countdown, modal diskon, dan tombol menuju halaman paket diskon multibahasa.

### Customer
- Registrasi dan login pelanggan menggunakan session.
- Dashboard pelanggan dengan ringkasan pesanan aktif serta tombol **Bayar Sekarang** untuk transaksi Midtrans.
- Form pemesanan hosting baru lengkap dengan unggah folder project dalam format ZIP (maks. 10 MB).
- Riwayat pesanan dengan status pembayaran/pemenuhan (pending, paid, failed, aktif, selesai) dan tautan unduhan file project.

### Admin
- Login admin menggunakan session.
- Dashboard admin dengan ringkasan pelanggan, pesanan, pembayaran pending, serta pendapatan yang sudah terkonfirmasi.
- CRUD paket hosting.
- CRUD paket diskon untuk mengatur penawaran promo yang tampil di landing page.
- Manajemen pelanggan dan pesanan termasuk perubahan status.
- Halaman monitoring transaksi Midtrans untuk memantau order ID, metode pembayaran, dan status pembayaran.

## Struktur Folder
```
clousting/
├── config/
├── database/
├── partials/
└── public/
    ├── admin/
    ├── assets/
    ├── customer/
    ├── paket-diskon.php
    ├── about.php
    ├── contact.php
    └── index.php
```

## Persiapan Database
1. Import file `database/clousting_db.sql` ke dalam MySQL Anda.
2. Gunakan kredensial default:
   - Admin: `admin@clousting.id` / `admin123`
   - Customer: `budi@pelanggan.id` / `customer123`

### Modul Sistem Informasi Posyandu (Baru)
Proyek ini juga menyertakan prototipe sistem informasi Posyandu dengan peran Super Admin, Admin Puskesmas, Bidan, dan Kader untuk mendukung digitalisasi layanan Posyandu.

1. Import file `SIPosyandu/database/posyandu_db.sql` untuk membuat skema dan data awal Posyandu.
2. Kredensial contoh:
   - Super Admin: `super@posyandu.id` / `password123`
   - Admin Puskesmas: `admin@puskesmas.id` / `password123`
   - Bidan: `bidan@posyandu.id` / `password123`
   - Kader: `kader@posyandu.id` / `password123`
3. Jalankan server PHP terpisah dengan root direktori `SIPosyandu/public/` (mis. `php -S localhost:8001 -t SIPosyandu/public`).
4. Akses antarmuka Posyandu melalui URL server tersebut (contoh: `http://localhost:8001`).
5. Fitur utama meliputi pendaftaran warga, pencatatan tumbuh kembang balita, jadwal & catatan imunisasi, reminder jadwal, dashboard statistik gizi, serta unduhan laporan PDF bulanan.

## Konfigurasi
Sesuaikan koneksi database pada `config/config.php` jika diperlukan. File ini juga akan membuat folder `public/uploads/projects` secara otomatis untuk menyimpan arsip project yang diunggah customer. Pastikan server memiliki izin tulis pada direktori tersebut.

### Midtrans Snap API
- Isi `MIDTRANS_SERVER_KEY` dan `MIDTRANS_CLIENT_KEY` di `config/config.php` atau melalui environment variable `MIDTRANS_SERVER_KEY`, `MIDTRANS_CLIENT_KEY`, serta `MIDTRANS_IS_PRODUCTION` (default `false`).
- Endpoint notifikasi Midtrans mengarah ke `/payment/notify.php`. Pastikan URL ini dapat diakses publik (gunakan fitur `Ngrok` atau domain hosting Anda untuk pengujian Sandbox).
- File `/payment/create.php` digunakan untuk menghasilkan transaksi Snap dan akan mengarahkan pengguna ke halaman pembayaran Midtrans.

### Batasan Unggah Project
- Format file wajib `.zip`.
- Ukuran maksimal 10 MB (sesuaikan dengan `php.ini` apabila diperlukan).
- Arsip yang diunggah akan tersedia bagi Admin dan customer melalui tautan unduhan pada tabel pesanan.

- Tabel baru `paket_diskon` menyimpan promo harga khusus yang dapat diatur dari panel admin. Halaman publik `/paket-diskon.php`
  akan menampilkan seluruh paket berstatus aktif lengkap dengan harga asli, harga promo, dan periode berlaku.

## Menjalankan Aplikasi
Gunakan server PHP bawaan dengan root direktori `public/`:
```bash
php -S localhost:8000 -t public
```

Kemudian akses `http://localhost:8000` di browser.

## Alur Pembayaran Midtrans
1. Customer membuat pesanan baru sehingga status pesanan `pending` dan tombol **Bayar Sekarang** tersedia di dashboard.
2. Saat tombol diklik, `/payment/create.php` membuat order ID unik, mencatat data pada tabel `transaksi`, dan meminta token Snap via Midtrans PHP SDK. Pengguna kemudian diarahkan ke halaman pembayaran Midtrans.
3. Setelah pembayaran selesai, Midtrans mengirimkan callback ke `/payment/notify.php`. Endpoint ini memverifikasi signature, memperbarui tabel `transaksi`, dan mengubah status pesanan menjadi `paid` atau `failed` sesuai status Midtrans.
4. Admin dapat memonitor seluruh transaksi melalui `/admin/pembayaran.php`, sementara customer melihat status pembayaran terbaru langsung di dashboard.
>>>>>>> 2c9bef9e52f7e19bcef2f673ef0f4d40b20457c0
# clouSting.git.io
