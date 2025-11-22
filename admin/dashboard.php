<?php
require_once __DIR__ . '/../../config/config.php';
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
if (empty($_SESSION['admin_id'])) {
    header('Location: /admin/login.php');
    exit;
}

$totalCustomer = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as total FROM users WHERE role='customer'"))['total'];
$totalOrders = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as total FROM pesanan"))['total'];
$pendingPayments = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as total FROM pesanan WHERE status IN ('menunggu_pembayaran','menunggu_konfirmasi')"))['total'];
$paidRevenueQuery = mysqli_fetch_assoc(mysqli_query($conn, "SELECT SUM(total_tagihan) as total FROM pesanan WHERE status IN ('diproses','selesai')"));
$paidRevenueValue = $paidRevenueQuery['total'] ?? 0;
$activeDiscounts = [];
$upcomingDiscounts = [];
$activePromoQuery = mysqli_query($conn, "SELECT p.*, b.nama AS bundling_nama FROM promos p LEFT JOIN bundling_packages b ON b.id = p.bundling_id WHERE CURDATE() BETWEEN p.tanggal_mulai AND p.tanggal_berakhir ORDER BY p.tanggal_mulai ASC");
if ($activePromoQuery) {
    while ($promoRow = mysqli_fetch_assoc($activePromoQuery)) {
        $activeDiscounts[] = $promoRow;
    }
    mysqli_free_result($activePromoQuery);
}
$upcomingPromoQuery = mysqli_query($conn, "SELECT p.*, b.nama AS bundling_nama FROM promos p LEFT JOIN bundling_packages b ON b.id = p.bundling_id WHERE p.tanggal_mulai > CURDATE() ORDER BY p.tanggal_mulai ASC LIMIT 3");
if ($upcomingPromoQuery) {
    while ($upcomingRow = mysqli_fetch_assoc($upcomingPromoQuery)) {
        $upcomingDiscounts[] = $upcomingRow;
    }
    mysqli_free_result($upcomingPromoQuery);
}
$latestOrders = mysqli_query($conn, "SELECT p.*, u.nama, pk.nama_paket, b.nama AS bundling_nama FROM pesanan p JOIN users u ON p.user_id = u.id LEFT JOIN paket_hosting pk ON p.paket_id = pk.id LEFT JOIN bundling_packages b ON p.bundling_id = b.id ORDER BY p.tanggal_pesanan DESC LIMIT 5");
$activeDiscountCount = count($activeDiscounts);
?>
<?php require_once __DIR__ . '/../../partials/header.php'; ?>
<div class="admin-layout d-lg-flex">
    <?php require_once __DIR__ . '/../../partials/sidebar.php'; ?>
    <div class="admin-dashboard flex-grow-1 bg-light min-vh-100">
        <div class="admin-dashboard__inner container-fluid py-3 py-md-4">
            <div class="admin-dashboard-top d-flex flex-wrap align-items-center justify-content-between gap-2 gap-md-3">
                <div class="d-flex align-items-center gap-2 gap-md-3 w-100 w-md-auto">
                    <button class="btn btn-outline-primary btn-compact d-lg-none" type="button" data-bs-toggle="offcanvas" data-bs-target="#sidebarMenu" aria-controls="sidebarMenu">
                        <i class="fas fa-bars me-2"></i>Menu
                    </button>
                    <div>
                        <h2 class="fw-bold mb-1">Dashboard Admin</h2>
                        <p class="text-muted mb-0">Ringkasan aktivitas ClouSting.</p>
                    </div>
                </div>
                <div class="admin-dashboard-actions d-none d-md-flex align-items-center gap-2">
                    <a href="/admin/pesanan.php" class="btn btn-primary btn-compact"><i class="fas fa-clipboard-list me-2"></i>Kelola Pesanan</a>
                    <a href="/admin/paket.php" class="btn btn-outline-primary btn-compact"><i class="fas fa-box-open me-2"></i>Paket Hosting</a>
                    <a href="/admin/logout.php" class="btn btn-outline-danger btn-compact"><i class="fas fa-sign-out-alt me-2"></i>Keluar</a>
                </div>
                <div class="dropdown d-md-none w-100">
                    <button class="btn btn-outline-primary dropdown-toggle btn-compact w-100" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                        Menu Lainnya
                    </button>
                    <ul class="dropdown-menu dropdown-menu-end dropdown-menu-md-start shadow-sm w-100">
                        <li><a class="dropdown-item" href="/admin/pesanan.php"><i class="fas fa-clipboard-list me-2"></i>Kelola Pesanan</a></li>
                        <li><a class="dropdown-item" href="/admin/paket.php"><i class="fas fa-box-open me-2"></i>Paket Hosting</a></li>
                        <li><hr class="dropdown-divider"></li>
                        <li><a class="dropdown-item text-danger" href="/admin/logout.php"><i class="fas fa-sign-out-alt me-2"></i>Keluar</a></li>
                    </ul>
                </div>
            </div>
            <div class="row g-4 mt-2">
                <div class="col-md-6 col-lg-3">
                    <div class="card border-0 shadow-sm">
                        <div class="card-body">
                            <h6 class="text-muted">Total Pelanggan</h6>
                            <h3 class="fw-bold"><?php echo (int)$totalCustomer; ?></h3>
                        </div>
                    </div>
                </div>
                <div class="col-md-6 col-lg-3">
                    <div class="card border-0 shadow-sm">
                        <div class="card-body">
                            <h6 class="text-muted">Total Pesanan</h6>
                            <h3 class="fw-bold"><?php echo (int)$totalOrders; ?></h3>
                        </div>
                    </div>
                </div>
                <div class="col-md-6 col-lg-3">
                    <div class="card border-0 shadow-sm">
                        <div class="card-body">
                            <h6 class="text-muted">Pembayaran Pending</h6>
                            <h3 class="fw-bold"><?php echo (int)$pendingPayments; ?></h3>
                        </div>
                    </div>
                </div>
                <div class="col-md-6 col-lg-3">
                    <div class="card border-0 shadow-sm">
                        <div class="card-body">
                            <h6 class="text-muted">Pembayaran Terkonfirmasi</h6>
                            <h3 class="fw-bold">Rp <?php echo number_format($paidRevenueValue, 0, ',', '.'); ?></h3>
                        </div>
                    </div>
                </div>
                <div class="col-md-6 col-lg-3">
                    <div class="card border-0 shadow-sm">
                        <div class="card-body">
                            <h6 class="text-muted">Promo Aktif</h6>
                            <h3 class="fw-bold"><?php echo $activeDiscountCount; ?></h3>
                        </div>
                    </div>
                </div>
            </div>
            <div class="card border-0 shadow-sm mt-4">
                <div class="card-body p-3 p-md-4">
                    <div class="d-flex flex-wrap align-items-center justify-content-between gap-2 mb-3">
                        <h5 class="fw-bold mb-0">Promo &amp; Diskon Aktif</h5>
                        <a href="/admin/promo.php" class="btn btn-outline-primary btn-sm btn-compact"><i class="fas fa-tags me-2"></i>Kelola Promo</a>
                    </div>
                    <?php if (!empty($activeDiscounts)): ?>
                        <div class="list-group mb-3">
                            <?php foreach ($activeDiscounts as $promo): ?>
                                <div class="list-group-item list-group-item-action d-flex justify-content-between align-items-start">
                                    <div>
                                        <h6 class="mb-1"><?php echo htmlspecialchars($promo['nama']); ?></h6>
                                        <p class="mb-1 small text-muted">
                                            Diskon <?php echo (int)$promo['diskon_persen']; ?>%
                                            <?php if (!empty($promo['bundling_nama'])): ?>untuk <?php echo htmlspecialchars($promo['bundling_nama']); ?><?php endif; ?>
                                        </p>
                                        <p class="mb-0 small">Periode: <?php echo date('d M Y', strtotime($promo['tanggal_mulai'])); ?> - <?php echo date('d M Y', strtotime($promo['tanggal_berakhir'])); ?></p>
                                    </div>
                                    <span class="badge bg-primary align-self-center">Aktif</span>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php else: ?>
                        <p class="text-muted mb-3">Belum ada promo aktif saat ini.</p>
                    <?php endif; ?>
                    <?php if (!empty($upcomingDiscounts)): ?>
                        <h6 class="fw-semibold">Akan Datang</h6>
                        <ul class="list-unstyled small text-muted mb-3">
                            <?php foreach ($upcomingDiscounts as $promo): ?>
                                <li class="mb-1">
                                    <i class="fas fa-clock text-primary me-2"></i><?php echo htmlspecialchars($promo['nama']); ?> (mulai <?php echo date('d M Y', strtotime($promo['tanggal_mulai'])); ?>)
                                </li>
                            <?php endforeach; ?>
                        </ul>
                    <?php endif; ?>
                </div>
            </div>
            <div class="card border-0 shadow-sm mt-4">
                <div class="card-body p-3 p-md-4">
                    <div class="d-flex flex-wrap align-items-center justify-content-between gap-2 mb-3">
                        <h5 class="fw-bold mb-0">Pesanan Terbaru</h5>
                        <a href="/admin/pesanan.php" class="btn btn-outline-primary btn-sm btn-compact">Lihat Semua</a>
                    </div>
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead class="table-light">
                                <tr>
                                    <th>Customer</th>
                                    <th>Paket</th>
                                    <th>Domain</th>
                                    <th>Status</th>
                                    <th>Tanggal</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if ($latestOrders && mysqli_num_rows($latestOrders) > 0): ?>
                                    <?php while ($row = mysqli_fetch_assoc($latestOrders)): ?>
                                        <tr>
                                            <td><?php echo htmlspecialchars($row['nama']); ?></td>
                                            <td>
                                                <?php
                                                $label = 'Custom Project';
                                                if ($row['jenis_pesanan'] === 'hosting') {
                                                    $label = $row['nama_paket'] ?? 'Paket Hosting';
                                                } elseif ($row['jenis_pesanan'] === 'bundling') {
                                                    $label = $row['bundling_nama'] ?? 'Paket Bundling';
                                                }
                                                echo htmlspecialchars($label);
                                                ?>
                                            </td>
                                            <?php
                                            $statusMap = [
                                                'menunggu_pembayaran' => ['badge' => 'warning text-dark', 'label' => 'Menunggu Pembayaran'],
                                                'menunggu_konfirmasi' => ['badge' => 'info', 'label' => 'Menunggu Konfirmasi'],
                                                'diproses' => ['badge' => 'primary', 'label' => 'Diproses'],
                                                'selesai' => ['badge' => 'success', 'label' => 'Selesai'],
                                                'dibatalkan' => ['badge' => 'secondary', 'label' => 'Dibatalkan'],
                                            ];
                                            $statusInfo = $statusMap[$row['status']] ?? ['badge' => 'secondary', 'label' => ucfirst($row['status'])];
                                            ?>
                                            <td><?php echo htmlspecialchars($row['domain'] ?? '-'); ?></td>
                                            <td><span class="badge bg-<?php echo $statusInfo['badge']; ?>"><?php echo $statusInfo['label']; ?></span></td>
                                            <td><?php echo date('d M Y', strtotime($row['tanggal_pesanan'])); ?></td>
                                        </tr>
                                    <?php endwhile; ?>
                                <?php else: ?>
                                    <tr>
                                        <td colspan="5" class="text-center py-3">Belum ada pesanan.</td>
                                    </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="mobile-logout-action d-md-none container pb-3">
        <a href="/admin/logout.php" class="btn btn-outline-danger w-100">
            <i class="fas fa-sign-out-alt me-2"></i>Keluar
        </a>
    </div>
</div>
<?php require_once __DIR__ . '/../../partials/footer.php'; ?>
