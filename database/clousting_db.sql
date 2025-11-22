CREATE DATABASE IF NOT EXISTS clousting_db;
USE clousting_db;

CREATE TABLE IF NOT EXISTS users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nama VARCHAR(120) NOT NULL,
    email VARCHAR(120) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    role ENUM('admin','customer') NOT NULL DEFAULT 'customer',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS paket_hosting (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nama_paket VARCHAR(100) NOT NULL,
    deskripsi VARCHAR(255) NULL,
    harga INT NOT NULL,
    fitur TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS bundling_packages (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nama VARCHAR(120) NOT NULL,
    slug VARCHAR(60) NOT NULL UNIQUE,
    deskripsi TEXT NOT NULL,
    harga INT NOT NULL DEFAULT 0,
    highlight TEXT,
    reference_links TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS promos (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nama VARCHAR(150) NOT NULL,
    deskripsi TEXT,
    tanggal_mulai DATE NOT NULL,
    tanggal_berakhir DATE NOT NULL,
    diskon_persen INT DEFAULT 0,
    bundling_id INT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_promos_bundling FOREIGN KEY (bundling_id) REFERENCES bundling_packages(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS pesanan (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    paket_id INT NULL,
    bundling_id INT NULL,
    jenis_pesanan ENUM('hosting','bundling','custom') NOT NULL DEFAULT 'hosting',
    domain VARCHAR(150) DEFAULT NULL,
    nomor_telepon VARCHAR(20) NOT NULL,
    catatan_custom TEXT,
    metode_pembayaran VARCHAR(60) NOT NULL,
    opsi_pembayaran ENUM('bank_mandiri','bank_bni','bank_bca') NOT NULL DEFAULT 'bank_mandiri',
    project_file VARCHAR(255) DEFAULT NULL,
    status ENUM('menunggu_pembayaran','menunggu_konfirmasi','diproses','selesai','dibatalkan') DEFAULT 'menunggu_pembayaran',
    bukti_pembayaran VARCHAR(255) DEFAULT NULL,
    total_tagihan INT NOT NULL DEFAULT 0,
    tanggal_pesanan DATETIME DEFAULT CURRENT_TIMESTAMP,
    tanggal_konfirmasi DATETIME DEFAULT NULL,
    CONSTRAINT fk_pesanan_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    CONSTRAINT fk_pesanan_paket FOREIGN KEY (paket_id) REFERENCES paket_hosting(id) ON DELETE SET NULL,
    CONSTRAINT fk_pesanan_bundling FOREIGN KEY (bundling_id) REFERENCES bundling_packages(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS transaksi (
    id INT AUTO_INCREMENT PRIMARY KEY,
    pesanan_id INT NOT NULL,
    order_id VARCHAR(100) UNIQUE,
    gross_amount DECIMAL(12,2),
    payment_type VARCHAR(50),
    transaction_status VARCHAR(50),
    transaction_time TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_transaksi_pesanan FOREIGN KEY (pesanan_id) REFERENCES pesanan(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

INSERT INTO users (nama, email, password, role) VALUES
('Administrator ClouSting', 'admin@clousting.id', '$2y$10$9jCqCtp42A6zsQ.zHHR6OOFJOLLmObAun1vDLteA94ppIqhzyap8m', 'admin'), -- password: admin123
('Budi Santoso', 'budi@pelanggan.id', '$2y$10$6qBsZv4dSKJ5IhXCTITVEOCZU97GU0RGnqDtFb18m0tHbdD.N14Wy', 'customer'), -- password: customer123
('Sinta Lestari', 'sinta@pelanggan.id', '$2y$10$6PF39/C4h/11FXGeUXLEVOL3G.yUpiRaY1oWXcnZ6FQcmGeXgnP9a', 'customer'); -- password: customer123

INSERT INTO paket_hosting (nama_paket, deskripsi, harga, fitur) VALUES
('Starter', 'Cocok untuk website personal dan portofolio.', 49000, '1 Website\n10 GB SSD Storage\nUnlimited Bandwidth\nGratis SSL'),
('Business', 'Solusi optimal untuk UMKM dengan traffic menengah.', 99000, '5 Website\n50 GB SSD Storage\nUnlimited Email\nBackup Harian'),
('Premium', 'Performa maksimal untuk website bisnis dan e-commerce.', 199000, 'Unlimited Website\nNVMe SSD Storage\nPrioritas Support\nCDN & Web Firewall');

INSERT INTO bundling_packages (nama, slug, deskripsi, harga, highlight, reference_links) VALUES
('Website Company Profile', 'company-profile', 'Paket lengkap untuk menampilkan profil perusahaan, layanan, dan portofolio secara profesional.', 190000, 'Desain eksklusif brand\nHalaman layanan & portofolio\nOptimasi SEO lokal', 'Website Company 1|https://elwass.github.io/companyprofile0.git.io/\nWebsite Company 2|https://elwass.github.io/companyprofile1.git.io/\nWebsite Company 3|https://elwass.github.io/companyprofile3.git.io/'),
('Personal Website', 'personal-website', 'Paket website personal untuk kreator, profesional, atau freelancer dengan tampilan ringkas dan elegan.', 190000, 'Halaman profil profesional\nBlog pribadi siap pakai\nIntegrasi sosial media', 'Digital Story|https://www.youtube.com/watch?v=XHOmBV4js_E'),
('Custom Project', 'custom', 'Tentukan kebutuhan website Anda secara fleksibel. Tim kami akan menghubungi untuk penawaran khusus.', 0, 'Analisis kebutuhan khusus\nSesi konsultasi eksklusif\nRekomendasi teknologi tepat guna', 'Form Konsultasi|https://wa.me/6285175394358');

INSERT INTO promos (nama, deskripsi, tanggal_mulai, tanggal_berakhir, diskon_persen, bundling_id) VALUES
('Promo Bundling Agustus', 'Diskon spesial hingga 25% untuk paket bundling website sepanjang bulan Agustus.', '2024-08-01', '2024-08-31', 25, 1);

INSERT INTO pesanan (user_id, paket_id, bundling_id, jenis_pesanan, domain, catatan_custom, metode_pembayaran, opsi_pembayaran, project_file, status, bukti_pembayaran, total_tagihan, tanggal_pesanan, tanggal_konfirmasi) VALUES
(2, 2, NULL, 'hosting', 'tokobudi.com', NULL, 'Transfer Bank', 'bank_mandiri', NULL, 'diproses', NULL, 99000, '2024-06-10 09:00:00', '2024-06-10 10:00:00'),
(3, NULL, 1, 'bundling', 'profil-sinta.id', NULL, 'Transfer Bank', 'bank_bca', 'uploads/projects/project_brief_sinta.zip', 'menunggu_konfirmasi', 'uploads/payments/bukti_sinta.jpg', 190000, '2024-07-15 14:30:00', NULL),
(2, NULL, 3, 'custom', NULL, 'Butuh website katalog produk dengan integrasi marketplace.', 'Transfer Bank', 'bank_bni', 'uploads/projects/project_brief_katalog.zip', 'menunggu_pembayaran', NULL, 0, '2024-07-25 10:15:00', NULL);

INSERT INTO transaksi (pesanan_id, order_id, gross_amount, payment_type, transaction_status, transaction_time) VALUES
(1, 'CLOUDHOST-1-123456', 99000.00, 'bank_transfer', 'settlement', '2024-06-10 09:30:00');
