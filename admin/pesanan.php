<?php
require_once __DIR__ . '/../../config/config.php';
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
if (empty($_SESSION['admin_id'])) {
    header('Location: /admin/login.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = (int)($_POST['id'] ?? 0);
    $status = $_POST['status'] ?? 'menunggu_pembayaran';
    $allowed = ['menunggu_pembayaran', 'menunggu_konfirmasi', 'diproses', 'selesai', 'dibatalkan'];
    if ($id > 0 && in_array($status, $allowed, true)) {
        $statusEscaped = mysqli_real_escape_string($conn, $status);
        $extra = '';
        if (in_array($status, ['diproses', 'selesai'], true)) {
            $extra = ", tanggal_konfirmasi = NOW()";
        } elseif ($status === 'menunggu_konfirmasi') {
            $extra = ", tanggal_konfirmasi = NULL";
        }
        mysqli_query($conn, "UPDATE pesanan SET status = '$statusEscaped'$extra WHERE id = $id");
    }
    header('Location: pesanan.php');
    exit;
}

if (isset($_GET['hapus'])) {
    $hapusId = (int)$_GET['hapus'];
    if ($hapusId > 0) {
        $fileQuery = mysqli_query($conn, "SELECT bukti_pembayaran, project_file FROM pesanan WHERE id = $hapusId");
        if ($fileQuery && mysqli_num_rows($fileQuery) > 0) {
            $file = mysqli_fetch_assoc($fileQuery);
            if (!empty($file['bukti_pembayaran'])) {
                $path = dirname(__DIR__) . '/../' . $file['bukti_pembayaran'];
                if (is_file($path)) {
                    unlink($path);
                }
            }
            if (!empty($file['project_file'])) {
                $projectPath = dirname(__DIR__) . '/../' . $file['project_file'];
                if (is_file($projectPath)) {
                    unlink($projectPath);
                }
            }
        }
        mysqli_query($conn, "DELETE FROM pesanan WHERE id = $hapusId");
    }
    header('Location: pesanan.php');
    exit;
}

$orders = mysqli_query($conn, "SELECT p.*, u.nama, u.email, pk.nama_paket, b.nama AS bundling_nama FROM pesanan p JOIN users u ON p.user_id = u.id LEFT JOIN paket_hosting pk ON p.paket_id = pk.id LEFT JOIN bundling_packages b ON p.bundling_id = b.id ORDER BY p.tanggal_pesanan DESC");
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
                        <h2 class="fw-bold mb-1">Kelola Pesanan</h2>
                        <p class="text-muted mb-0">Pantau dan ubah status pesanan pelanggan.</p>
                    </div>
                </div>
                <div class="admin-dashboard-actions d-none d-md-flex align-items-center gap-2">
                    <a href="/admin/pembayaran.php" class="btn btn-outline-primary btn-compact"><i class="fas fa-receipt me-2"></i>Riwayat Pembayaran</a>
                    <a href="/admin/promo.php" class="btn btn-outline-secondary btn-compact"><i class="fas fa-tags me-2"></i>Kelola Promo</a>
                </div>
                <div class="dropdown d-md-none w-100">
                    <button class="btn btn-outline-primary dropdown-toggle btn-compact w-100" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                        Menu Lainnya
                    </button>
                    <ul class="dropdown-menu dropdown-menu-end dropdown-menu-md-start shadow-sm w-100">
                        <li><a class="dropdown-item" href="/admin/pembayaran.php"><i class="fas fa-receipt me-2"></i>Riwayat Pembayaran</a></li>
                        <li><a class="dropdown-item" href="/admin/promo.php"><i class="fas fa-tags me-2"></i>Kelola Promo</a></li>
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
                                    <th>Customer</th>
                                    <th>Telepon</th>
                                    <th>Jenis</th>
                                    <th>Paket</th>
                                    <th>Total</th>
                                    <th>File Project</th>
                                    <th>Pembayaran</th>
                                    <th>Status</th>
                                    <th>Bukti</th>
                                    <th>Tanggal</th>
                                    <th>Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if ($orders && mysqli_num_rows($orders) > 0): $no = 1; ?>
                                    <?php while ($order = mysqli_fetch_assoc($orders)): ?>
                                    <?php
                                    $label = 'Custom Project';
                                    if ($order['jenis_pesanan'] === 'hosting') {
                                        $label = $order['nama_paket'] ?? 'Paket Hosting';
                                    } elseif ($order['jenis_pesanan'] === 'bundling') {
                                        $label = $order['bundling_nama'] ?? 'Paket Bundling';
                                    }
                                    $statusOptions = [
                                        'menunggu_pembayaran' => 'Menunggu Pembayaran',
                                        'menunggu_konfirmasi' => 'Menunggu Konfirmasi',
                                        'diproses' => 'Diproses',
                                        'selesai' => 'Selesai',
                                        'dibatalkan' => 'Dibatalkan',
                                    ];
                                    $badgeMap = [
                                        'menunggu_pembayaran' => 'warning text-dark',
                                        'menunggu_konfirmasi' => 'info',
                                        'diproses' => 'primary',
                                        'selesai' => 'success',
                                        'dibatalkan' => 'secondary',
                                    ];
                                    $badgeClass = $badgeMap[$order['status']] ?? 'secondary';
                                    $paymentLabel = strtoupper(str_replace('bank_', '', $order['opsi_pembayaran']));
                                    ?>
                                    <tr>
                                        <td><?php echo $no++; ?></td>
                                        <td>
                                            <strong><?php echo htmlspecialchars($order['nama']); ?></strong><br>
                                            <span class="text-muted small"><?php echo htmlspecialchars($order['email']); ?></span>
                                        </td>
                                        <td><?php echo htmlspecialchars($order['nomor_telepon'] ?? '-'); ?></td>
                                        <td class="text-capitalize"><?php echo htmlspecialchars($order['jenis_pesanan']); ?></td>
                                        <td><?php echo htmlspecialchars($label); ?></td>
                                        <td>Rp <?php echo number_format((int)$order['total_tagihan'], 0, ',', '.'); ?></td>
                                        <td>
                                            <?php if (!empty($order['project_file'])): ?>
                                                <a href="/<?php echo htmlspecialchars($order['project_file']); ?>" class="btn btn-sm btn-outline-secondary" target="_blank" rel="noopener">Unduh</a>
                                            <?php else: ?>
                                                <span class="text-muted small">Belum ada</span>
                                            <?php endif; ?>
                                        </td>
                                        <td><?php echo htmlspecialchars($order['metode_pembayaran']); ?> (<?php echo htmlspecialchars($paymentLabel); ?>)</td>
                                        <td>
                                            <span class="badge bg-<?php echo $badgeClass; ?> mb-2"><?php echo $statusOptions[$order['status']] ?? ucfirst($order['status']); ?></span>
                                            <form method="post" class="d-flex flex-column flex-sm-row align-items-stretch align-items-sm-center gap-2">
                                                <input type="hidden" name="id" value="<?php echo $order['id']; ?>">
                                                <select name="status" class="form-select form-select-sm">
                                                    <?php foreach ($statusOptions as $key => $value): ?>
                                                        <option value="<?php echo $key; ?>" <?php echo $order['status'] === $key ? 'selected' : ''; ?>><?php echo $value; ?></option>
                                                    <?php endforeach; ?>
                                                </select>
                                                <button type="submit" class="btn btn-sm btn-primary w-100 w-sm-auto">Update</button>
                                            </form>
                                        </td>
                                        <td>
                                            <?php if (!empty($order['bukti_pembayaran'])): ?>
                                                <a href="/<?php echo htmlspecialchars($order['bukti_pembayaran']); ?>" class="btn btn-sm btn-outline-primary" target="_blank" rel="noopener">Lihat</a>
                                            <?php else: ?>
                                                <span class="text-muted small">Belum ada</span>
                                            <?php endif; ?>
                                        </td>
                                        <td><?php echo date('d M Y H:i', strtotime($order['tanggal_pesanan'])); ?></td>
                                        <td>
                                            <a href="?hapus=<?php echo $order['id']; ?>" class="btn btn-sm btn-danger" onclick="return confirm('Hapus pesanan ini?');"><i class="fas fa-trash"></i></a>
                                        </td>
                                    </tr>
                                    <?php endwhile; ?>
                                <?php else: ?>
                                    <tr>
                                        <td colspan="10" class="text-center py-3">Belum ada pesanan.</td>
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
