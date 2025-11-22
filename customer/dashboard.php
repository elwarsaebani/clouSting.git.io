<?php
require_once __DIR__ . '/../../config/config.php';
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
if (empty($_SESSION['customer_id'])) {
    header('Location: /customer/login.php');
    exit;
}

$customerId = (int)$_SESSION['customer_id'];
$customerName = $_SESSION['customer_name'];

$activeOrder = null;
$activeOrderQuery = mysqli_query($conn, "SELECT p.*, pk.nama_paket, pk.harga AS harga_paket, b.nama AS bundling_nama FROM pesanan p LEFT JOIN paket_hosting pk ON p.paket_id = pk.id LEFT JOIN bundling_packages b ON p.bundling_id = b.id WHERE p.user_id = $customerId ORDER BY p.tanggal_pesanan DESC LIMIT 1");
if ($activeOrderQuery && mysqli_num_rows($activeOrderQuery) > 0) {
    $activeOrder = mysqli_fetch_assoc($activeOrderQuery);
    mysqli_free_result($activeOrderQuery);
}

$totalOrdersQuery = mysqli_query($conn, "SELECT COUNT(*) as total FROM pesanan WHERE user_id = $customerId");
$totalOrders = $totalOrdersQuery ? (int)mysqli_fetch_assoc($totalOrdersQuery)['total'] : 0;

$promo = null;
$promoQuery = mysqli_query($conn, "SELECT p.*, b.nama AS bundling_nama FROM promos p LEFT JOIN bundling_packages b ON p.bundling_id = b.id WHERE CURDATE() BETWEEN p.tanggal_mulai AND p.tanggal_berakhir ORDER BY p.tanggal_mulai LIMIT 1");
if ($promoQuery && mysqli_num_rows($promoQuery) > 0) {
    $promo = mysqli_fetch_assoc($promoQuery);
    mysqli_free_result($promoQuery);
}

require_once __DIR__ . '/../../partials/header.php';
?>
<div class="dashboard-wrapper">
    <div class="container py-5">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h2 class="fw-bold">Halo, <?php echo htmlspecialchars($customerName); ?>!</h2>
                <p class="text-muted mb-0">Kelola layanan hosting, bundling website, dan proyek custom melalui satu dashboard.</p>
            </div>
            <div class="d-flex flex-wrap gap-2">
                <a href="/customer/pesanan_baru.php" class="btn btn-primary"><i class="fas fa-plus me-2"></i>Buat Pesanan</a>
                <?php if ($activeOrder): ?>
                    <a href="/customer/pembayaran.php?id=<?php echo $activeOrder['id']; ?>" class="btn btn-outline-primary"><i class="fas fa-wallet me-2"></i>Status Pembayaran</a>
                <?php endif; ?>
                <a href="/customer/logout.php" class="btn btn-outline-danger">Keluar</a>
            </div>
        </div>
        <div class="row g-4">
            <div class="col-md-4">
                <div class="card shadow-sm border-0 h-100">
                    <div class="card-body">
                        <h6 class="text-muted">Total Pesanan</h6>
                        <h3 class="fw-bold"><?php echo $totalOrders; ?></h3>
                        <p class="small text-muted">Pantau seluruh riwayat transaksi Anda.</p>
                        <a href="/customer/riwayat_pesanan.php" class="btn btn-sm btn-outline-primary">Lihat Riwayat</a>
                    </div>
                </div>
            </div>
            <div class="col-md-8">
                <div class="card shadow-sm border-0 h-100">
                    <div class="card-body">
                        <h6 class="text-muted">Pesanan Terbaru</h6>
                        <?php if ($activeOrder): ?>
                            <h4 class="fw-semibold mb-2"><?php echo htmlspecialchars($activeOrder['jenis_pesanan'] === 'hosting' ? ($activeOrder['nama_paket'] ?? 'Paket Hosting') : ($activeOrder['jenis_pesanan'] === 'bundling' ? ($activeOrder['bundling_nama'] ?? 'Paket Bundling') : 'Project Custom')); ?></h4>
                            <?php
                            $statusBadge = [
                                'menunggu_pembayaran' => 'warning text-dark',
                                'menunggu_konfirmasi' => 'info',
                                'diproses' => 'primary',
                                'selesai' => 'success',
                                'dibatalkan' => 'secondary',
                            ];
                            $badgeClass = $statusBadge[$activeOrder['status']] ?? 'secondary';
                            ?>
                            <p class="mb-1"><strong>Status:</strong> <span class="badge bg-<?php echo $badgeClass; ?>"><?php echo ucfirst(str_replace('_', ' ', $activeOrder['status'])); ?></span></p>
                            <?php if (!empty($activeOrder['domain'])): ?>
                                <p class="mb-1"><strong>Domain:</strong> <?php echo htmlspecialchars($activeOrder['domain']); ?></p>
                            <?php endif; ?>
                            <p class="mb-1"><strong>Total:</strong> Rp <?php echo number_format((int)$activeOrder['total_tagihan'], 0, ',', '.'); ?></p>
                            <p class="mb-1"><strong>Metode Pembayaran:</strong> <?php echo htmlspecialchars($activeOrder['metode_pembayaran']); ?> (<?php echo htmlspecialchars(strtoupper(str_replace('bank_', '', $activeOrder['opsi_pembayaran']))); ?>)</p>
                            <p class="mb-3"><strong>Tanggal Pesanan:</strong> <?php echo date('d M Y H:i', strtotime($activeOrder['tanggal_pesanan'])); ?></p>
                            <div class="d-flex flex-wrap gap-2">
                                <a href="/customer/pembayaran.php?id=<?php echo $activeOrder['id']; ?>" class="btn btn-outline-primary btn-sm"><i class="fas fa-file-invoice-dollar me-1"></i>Lihat Pembayaran</a>
                                <a href="/customer/riwayat_pesanan.php" class="btn btn-outline-secondary btn-sm">Riwayat Pesanan</a>
                            </div>
                        <?php else: ?>
                            <p class="text-muted">Belum ada pesanan. Mulai dengan memilih paket hosting atau promo bundling.</p>
                            <a href="/customer/pesanan_baru.php" class="btn btn-primary btn-sm mt-3">Pesan Sekarang</a>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
        <?php if (isset($_GET['payment'])): ?>
            <?php
            $paymentNotice = null;
            switch ($_GET['payment']) {
                case 'success':
                    $paymentNotice = ['type' => 'success', 'message' => 'Pembayaran berhasil. Pesanan Anda sedang diproses.'];
                    break;
                case 'failed':
                    $paymentNotice = ['type' => 'danger', 'message' => 'Pembayaran gagal. Silakan coba lagi atau gunakan metode lain.'];
                    break;
                case 'pending':
                    $paymentNotice = ['type' => 'warning', 'message' => 'Pembayaran masih diproses oleh penyedia. Kami akan memperbarui status segera.'];
                    break;
            }
            ?>
            <?php if ($paymentNotice): ?>
                <div class="alert alert-<?php echo $paymentNotice['type']; ?> alert-dismissible fade show mt-3" role="alert">
                    <?php echo htmlspecialchars($paymentNotice['message']); ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Tutup"></button>
                </div>
            <?php endif; ?>
        <?php endif; ?>
        <div class="row g-4 mt-1">
            <div class="col-lg-8">
                <div class="dashboard-highlight h-100">
                    <h5 class="fw-bold mb-3">Langkah Setelah Pemesanan</h5>
                    <div class="status-timeline">
                        <div class="status-step active">
                            <div class="step-index">1</div>
                            <h6 class="fw-semibold">Upload Bukti</h6>
                            <p class="small mb-0">Unggah bukti pembayaran di halaman pembayaran agar verifikasi berjalan cepat.</p>
                        </div>
                        <div class="status-step <?php echo ($activeOrder && in_array($activeOrder['status'], ['menunggu_konfirmasi','diproses','selesai'], true)) ? 'active' : ''; ?>">
                            <div class="step-index">2</div>
                            <h6 class="fw-semibold">Verifikasi Admin</h6>
                            <p class="small mb-0">Tim admin memverifikasi maksimal 1&ndash;2 jam. Jika lebih lama, kami akan menghubungi via WhatsApp.</p>
                        </div>
                        <div class="status-step <?php echo ($activeOrder && in_array($activeOrder['status'], ['diproses','selesai'], true)) ? 'active' : ''; ?>">
                            <div class="step-index">3</div>
                            <h6 class="fw-semibold">Implementasi</h6>
                            <p class="small mb-0">Tim teknis menyiapkan server, domain, dan desain sesuai paket Anda.</p>
                        </div>
                        <div class="status-step <?php echo ($activeOrder && $activeOrder['status'] === 'selesai') ? 'active' : ''; ?>">
                            <div class="step-index">4</div>
                            <h6 class="fw-semibold">Go-Live</h6>
                            <p class="small mb-0">Website siap digunakan. Dapatkan panduan optimasi eksklusif dari kami.</p>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-lg-4">
                <div class="card shadow-sm border-0 h-100">
                    <div class="card-body">
                        <h5 class="fw-bold mb-3">Promo & Inspirasi</h5>
                        <?php if ($promo): ?>
                            <p class="mb-2"><strong><?php echo htmlspecialchars($promo['nama']); ?></strong></p>
                            <p class="small text-muted mb-2"><?php echo htmlspecialchars($promo['deskripsi'] ?? ''); ?></p>
                            <p class="small mb-1">Periode: <?php echo date('d M Y', strtotime($promo['tanggal_mulai'])); ?> - <?php echo date('d M Y', strtotime($promo['tanggal_berakhir'])); ?></p>
                            <a href="/index.php#promo" class="btn btn-outline-primary btn-sm">Lihat Detail Promo</a>
                        <?php else: ?>
                            <p class="text-muted">Belum ada promo aktif saat ini. Tetap pantau dashboard untuk info terbaru.</p>
                        <?php endif; ?>
                        <hr>
                        <p class="small mb-2">Butuh referensi desain website?</p>
                        <a href="/index.php#bundling" class="btn btn-sm btn-light text-primary">Lihat Paket Bundling</a>
                    </div>
                </div>
            </div>
        </div>
        <div class="row g-4 mt-4">
            <div class="col-12">
                <div class="card shadow-sm border-0 h-100">
                    <div class="card-body">
                        <h5 class="fw-bold mb-3">Tips Maksimalkan Layanan</h5>
                        <ul class="list-unstyled mb-0 small text-muted">
                            <li class="mb-2"><i class="fas fa-check-circle text-primary me-2"></i>Pastikan domain Anda aktif dan arahkan DNS ke ClouSting.</li>
                            <li class="mb-2"><i class="fas fa-check-circle text-primary me-2"></i>Gunakan template konten dari tim kami untuk mempercepat pengisian website.</li>
                            <li class="mb-2"><i class="fas fa-check-circle text-primary me-2"></i>Jadwalkan sesi konsultasi desain ulang gratis setiap selesai proyek.</li>
                            <li><i class="fas fa-check-circle text-primary me-2"></i>Gabungkan dengan paket SEO lokal untuk meningkatkan visibilitas bisnis.</li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<?php require_once __DIR__ . '/../../partials/footer.php'; ?>
