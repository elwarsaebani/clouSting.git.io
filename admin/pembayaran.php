<?php
require_once __DIR__ . '/../../config/config.php';
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
if (empty($_SESSION['admin_id'])) {
    header('Location: /admin/login.php');
    exit;
}

$transactions = mysqli_query($conn, "SELECT t.*, p.domain, p.status AS pesanan_status, p.total_tagihan, p.jenis_pesanan, pk.nama_paket, b.nama AS bundling_nama, u.nama, u.email FROM transaksi t JOIN pesanan p ON t.pesanan_id = p.id JOIN users u ON p.user_id = u.id LEFT JOIN paket_hosting pk ON p.paket_id = pk.id LEFT JOIN bundling_packages b ON p.bundling_id = b.id ORDER BY t.transaction_time DESC");
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
                        <h2 class="fw-bold mb-1">Riwayat Pembayaran</h2>
                        <p class="text-muted mb-0">Pantau status transaksi untuk setiap pesanan pelanggan.</p>
                    </div>
                </div>
                <div class="admin-dashboard-actions d-none d-md-flex align-items-center gap-2">
                    <a href="/admin/pesanan.php" class="btn btn-outline-primary btn-compact"><i class="fas fa-clipboard-list me-2"></i>Kelola Pesanan</a>
                    <a href="/admin/pelanggan.php" class="btn btn-outline-secondary btn-compact"><i class="fas fa-users me-2"></i>Data Pelanggan</a>
                </div>
                <div class="dropdown d-md-none w-100">
                    <button class="btn btn-outline-primary dropdown-toggle btn-compact w-100" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                        Menu Lainnya
                    </button>
                    <ul class="dropdown-menu dropdown-menu-end dropdown-menu-md-start shadow-sm w-100">
                        <li><a class="dropdown-item" href="/admin/pesanan.php"><i class="fas fa-clipboard-list me-2"></i>Kelola Pesanan</a></li>
                        <li><a class="dropdown-item" href="/admin/pelanggan.php"><i class="fas fa-users me-2"></i>Data Pelanggan</a></li>
                    </ul>
                </div>
            </div>
            <div class="card border-0 shadow-sm admin-management-card mt-3">
                <div class="card-body p-3 p-md-4">
                    <div class="table-responsive admin-table-wrapper">
                        <table class="table table-striped align-middle">
                            <thead>
                                <tr>
                                    <th>#</th>
                                    <th>Order ID</th>
                                    <th>Pelanggan</th>
                                    <th>Paket</th>
                                    <th>Nominal</th>
                                    <th>Metode</th>
                                    <th>Status Transaksi</th>
                                    <th>Status Pesanan</th>
                                    <th>Waktu</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if ($transactions && mysqli_num_rows($transactions) > 0): $no = 1; ?>
                                    <?php while ($trx = mysqli_fetch_assoc($transactions)): ?>
                                        <?php
                                        $trxStatus = strtolower($trx['transaction_status'] ?? '');
                                        $badgeMap = [
                                            'settlement' => 'success',
                                            'capture' => 'success',
                                            'pending' => 'warning text-dark',
                                            'deny' => 'danger',
                                            'cancel' => 'danger',
                                            'expire' => 'danger',
                                            'failure' => 'danger',
                                        ];
                                        $badge = $badgeMap[$trxStatus] ?? 'secondary';

                                        $orderStatusMap = [
                                            'menunggu_pembayaran' => 'warning text-dark',
                                            'menunggu_konfirmasi' => 'info',
                                            'diproses' => 'primary',
                                            'selesai' => 'success',
                                            'dibatalkan' => 'secondary',
                                        ];
                                        $orderBadge = $orderStatusMap[$trx['pesanan_status']] ?? 'secondary';

                                        $label = 'Custom Project';
                                        if ($trx['jenis_pesanan'] === 'hosting') {
                                            $label = $trx['nama_paket'] ?? 'Paket Hosting';
                                        } elseif ($trx['jenis_pesanan'] === 'bundling') {
                                            $label = $trx['bundling_nama'] ?? 'Paket Bundling';
                                        }

                                        $nominal = isset($trx['gross_amount']) && $trx['gross_amount'] > 0 ? (float)$trx['gross_amount'] : (float)($trx['total_tagihan'] ?? 0);
                                        ?>
                                        <tr>
                                            <td><?php echo $no++; ?></td>
                                            <td><?php echo htmlspecialchars($trx['order_id']); ?></td>
                                            <td>
                                                <strong><?php echo htmlspecialchars($trx['nama']); ?></strong><br>
                                                <span class="text-muted small"><?php echo htmlspecialchars($trx['email']); ?></span>
                                            </td>
                                            <td>
                                                <?php echo htmlspecialchars($label); ?><br>
                                                <span class="small text-muted"><?php echo htmlspecialchars($trx['domain'] ?? '-'); ?></span>
                                            </td>
                                            <td>Rp <?php echo number_format($nominal, 0, ',', '.'); ?></td>
                                            <td><?php echo htmlspecialchars($trx['payment_type'] ?? '-'); ?></td>
                                            <td><span class="badge bg-<?php echo $badge; ?>"><?php echo htmlspecialchars($trx['transaction_status'] ?? '-'); ?></span></td>
                                            <td><span class="badge bg-<?php echo $orderBadge; ?>"><?php echo htmlspecialchars($trx['pesanan_status']); ?></span></td>
                                            <td><?php echo date('d M Y H:i', strtotime($trx['transaction_time'])); ?></td>
                                        </tr>
                                    <?php endwhile; ?>
                                <?php else: ?>
                                    <tr>
                                        <td colspan="9" class="text-center py-4 text-muted">Belum ada data transaksi.</td>
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
