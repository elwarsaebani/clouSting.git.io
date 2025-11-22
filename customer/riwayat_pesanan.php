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

$query = mysqli_query($conn, "SELECT p.*, pk.nama_paket, pk.harga AS harga_paket, b.nama AS bundling_nama, b.harga AS harga_bundling FROM pesanan p LEFT JOIN paket_hosting pk ON p.paket_id = pk.id LEFT JOIN bundling_packages b ON p.bundling_id = b.id WHERE p.user_id = $customerId ORDER BY p.tanggal_pesanan DESC");
?>
<?php require_once __DIR__ . '/../../partials/header.php'; ?>
<div class="container py-5">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2 class="fw-bold">Riwayat Pesanan</h2>
            <p class="text-muted mb-0">Pantau status semua pesanan hosting, bundling, dan custom Anda.</p>
        </div>
        <a href="/customer/dashboard.php" class="btn btn-outline-secondary">Kembali</a>
    </div>
    <div class="card shadow border-0">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>#</th>
                            <th>Paket/Layanan</th>
                            <th>Domain/URL</th>
                            <th>Total</th>
                            <th>Pembayaran</th>
                            <th>Status</th>
                            <th>Tanggal Pesanan</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if ($query && mysqli_num_rows($query) > 0): $no = 1; ?>
                            <?php while ($row = mysqli_fetch_assoc($query)): ?>
                                <tr>
                                    <td><?php echo $no++; ?></td>
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
                                    <td><?php echo htmlspecialchars($row['domain'] ?? '-'); ?></td>
                                    <td>Rp <?php echo number_format((int)$row['total_tagihan'], 0, ',', '.'); ?></td>
                                    <td><?php echo htmlspecialchars($row['metode_pembayaran']); ?> (<?php echo htmlspecialchars(strtoupper(str_replace('bank_', '', $row['opsi_pembayaran']))); ?>)</td>
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
                                    <td><span class="badge bg-<?php echo $statusInfo['badge']; ?>"><?php echo $statusInfo['label']; ?></span></td>
                                    <td><?php echo date('d M Y H:i', strtotime($row['tanggal_pesanan'])); ?></td>
                                    <td class="d-flex flex-wrap gap-2">
                                        <a href="/customer/pembayaran.php?id=<?php echo $row['id']; ?>" class="btn btn-sm btn-outline-primary">Detail</a>
                                        <?php if (!empty($row['project_file'])): ?>
                                            <a href="/<?php echo htmlspecialchars($row['project_file']); ?>" class="btn btn-sm btn-outline-secondary" target="_blank" rel="noopener">Project</a>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="8" class="text-center py-4">Belum ada pesanan.</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
<?php require_once __DIR__ . '/../../partials/footer.php'; ?>
