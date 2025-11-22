<?php
require_once __DIR__ . '/../../config/config.php';
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
if (empty($_SESSION['admin_id'])) {
    header('Location: /admin/login.php');
    exit;
}

if (isset($_GET['hapus'])) {
    $hapusId = (int)$_GET['hapus'];
    mysqli_query($conn, "DELETE FROM users WHERE id=$hapusId AND role='customer'");
    header('Location: pelanggan.php');
    exit;
}

$customers = mysqli_query($conn, "SELECT u.*, COUNT(p.id) as total_pesanan FROM users u LEFT JOIN pesanan p ON p.user_id = u.id WHERE u.role='customer' GROUP BY u.id ORDER BY u.nama ASC");
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
                        <h2 class="fw-bold mb-1">Data Pelanggan</h2>
                        <p class="text-muted mb-0">Kelola informasi pelanggan ClouSting.</p>
                    </div>
                </div>
                <div class="admin-dashboard-actions d-none d-md-flex align-items-center gap-2">
                    <a href="/admin/pesanan.php" class="btn btn-outline-primary btn-compact"><i class="fas fa-clipboard-list me-2"></i>Kelola Pesanan</a>
                    <a href="/admin/paket.php" class="btn btn-outline-secondary btn-compact"><i class="fas fa-box-open me-2"></i>Paket Hosting</a>
                </div>
                <div class="dropdown d-md-none w-100">
                    <button class="btn btn-outline-primary dropdown-toggle btn-compact w-100" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                        Menu Lainnya
                    </button>
                    <ul class="dropdown-menu dropdown-menu-end dropdown-menu-md-start shadow-sm w-100">
                        <li><a class="dropdown-item" href="/admin/pesanan.php"><i class="fas fa-clipboard-list me-2"></i>Kelola Pesanan</a></li>
                        <li><a class="dropdown-item" href="/admin/paket.php"><i class="fas fa-box-open me-2"></i>Paket Hosting</a></li>
                    </ul>
                </div>
            </div>
            <div class="card border-0 shadow-sm admin-management-card mt-3">
                <div class="card-body p-3 p-md-4">
                    <div class="table-responsive admin-table-wrapper">
                        <table class="table table-striped">
                            <thead>
                                <tr>
                                    <th>#</th>
                                    <th>Nama</th>
                                    <th>Email</th>
                                    <th>Tanggal Daftar</th>
                                    <th>Total Pesanan</th>
                                    <th>Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php $no = 1; while ($customer = mysqli_fetch_assoc($customers)): ?>
                                    <tr>
                                        <td><?php echo $no++; ?></td>
                                        <td><?php echo htmlspecialchars($customer['nama']); ?></td>
                                        <td><?php echo htmlspecialchars($customer['email']); ?></td>
                                        <td><?php echo date('d M Y', strtotime($customer['created_at'])); ?></td>
                                        <td><?php echo (int)$customer['total_pesanan']; ?></td>
                                        <td>
                                            <a href="?hapus=<?php echo $customer['id']; ?>" class="btn btn-sm btn-danger" onclick="return confirm('Hapus pelanggan ini?');"><i class="fas fa-trash"></i></a>
                                        </td>
                                    </tr>
                                <?php endwhile; ?>
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
