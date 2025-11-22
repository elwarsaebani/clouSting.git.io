<?php
require_once __DIR__ . '/../../config/config.php';
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
if (empty($_SESSION['admin_id'])) {
    header('Location: /admin/login.php');
    exit;
}

$errors = [];
$success = '';

$bundlingOptions = [];
$bundlingQuery = mysqli_query($conn, "SELECT id, nama FROM bundling_packages ORDER BY nama ASC");
if ($bundlingQuery) {
    while ($row = mysqli_fetch_assoc($bundlingQuery)) {
        $bundlingOptions[] = $row;
    }
    mysqli_free_result($bundlingQuery);
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $promoId = (int)($_POST['id'] ?? 0);
    $nama = trim($_POST['nama'] ?? '');
    $deskripsi = trim($_POST['deskripsi'] ?? '');
    $tanggalMulai = trim($_POST['tanggal_mulai'] ?? '');
    $tanggalBerakhir = trim($_POST['tanggal_berakhir'] ?? '');
    $diskonPersen = (int)($_POST['diskon_persen'] ?? 0);
    $bundlingId = isset($_POST['bundling_id']) && $_POST['bundling_id'] !== '' ? (int)$_POST['bundling_id'] : null;

    if ($nama === '') {
        $errors[] = 'Nama promo wajib diisi.';
    }

    if ($tanggalMulai === '' || $tanggalBerakhir === '') {
        $errors[] = 'Tanggal mulai dan berakhir wajib diisi.';
    } elseif (strtotime($tanggalMulai) === false || strtotime($tanggalBerakhir) === false) {
        $errors[] = 'Format tanggal tidak valid.';
    } elseif (strtotime($tanggalMulai) > strtotime($tanggalBerakhir)) {
        $errors[] = 'Tanggal berakhir tidak boleh lebih awal dari tanggal mulai.';
    }

    if ($diskonPersen < 0 || $diskonPersen > 100) {
        $errors[] = 'Diskon harus berada di antara 0 hingga 100%.';
    }

    if ($bundlingId !== null) {
        $validBundling = false;
        foreach ($bundlingOptions as $option) {
            if ((int)$option['id'] === $bundlingId) {
                $validBundling = true;
                break;
            }
        }
        if (!$validBundling) {
            $errors[] = 'Paket bundling tidak ditemukan.';
        }
    }

    if (empty($errors)) {
        $namaEscaped = mysqli_real_escape_string($conn, $nama);
        $deskripsiEscaped = mysqli_real_escape_string($conn, $deskripsi);
        $mulaiEscaped = mysqli_real_escape_string($conn, $tanggalMulai);
        $berakhirEscaped = mysqli_real_escape_string($conn, $tanggalBerakhir);
        $diskonValue = (int)$diskonPersen;
        $bundlingValue = $bundlingId !== null ? (int)$bundlingId : 'NULL';

        if ($promoId > 0) {
            $updateSql = "UPDATE promos SET nama = '$namaEscaped', deskripsi = '$deskripsiEscaped', tanggal_mulai = '$mulaiEscaped', tanggal_berakhir = '$berakhirEscaped', diskon_persen = $diskonValue, bundling_id = $bundlingValue WHERE id = $promoId";
            if (mysqli_query($conn, $updateSql)) {
                $success = 'Promo berhasil diperbarui.';
            } else {
                $errors[] = 'Gagal memperbarui promo. Silakan coba lagi.';
            }
        } else {
            $insertSql = "INSERT INTO promos (nama, deskripsi, tanggal_mulai, tanggal_berakhir, diskon_persen, bundling_id) VALUES ('$namaEscaped', '$deskripsiEscaped', '$mulaiEscaped', '$berakhirEscaped', $diskonValue, $bundlingValue)";
            if (mysqli_query($conn, $insertSql)) {
                $success = 'Promo baru berhasil ditambahkan.';
            } else {
                $errors[] = 'Gagal menambahkan promo baru.';
            }
        }
    }
}

if (isset($_GET['hapus'])) {
    $hapusId = (int)$_GET['hapus'];
    mysqli_query($conn, "DELETE FROM promos WHERE id = $hapusId");
    header('Location: /admin/promo.php');
    exit;
}

$editData = null;
if (isset($_GET['edit'])) {
    $editId = (int)$_GET['edit'];
    $editQuery = mysqli_query($conn, "SELECT * FROM promos WHERE id = $editId LIMIT 1");
    if ($editQuery && mysqli_num_rows($editQuery) > 0) {
        $editData = mysqli_fetch_assoc($editQuery);
        mysqli_free_result($editQuery);
    }
}

$promoList = mysqli_query($conn, "SELECT p.*, b.nama AS bundling_nama FROM promos p LEFT JOIN bundling_packages b ON b.id = p.bundling_id ORDER BY p.tanggal_mulai DESC");

require_once __DIR__ . '/../../partials/header.php';
?>
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
                        <h2 class="fw-bold mb-1">Kelola Promo &amp; Diskon</h2>
                        <p class="text-muted mb-0">Tambahkan promo baru atau perbarui diskon bundling website.</p>
                    </div>
                </div>
                <div class="admin-dashboard-actions d-none d-md-flex align-items-center gap-2">
                    <a href="/admin/paket_diskon.php" class="btn btn-outline-primary btn-compact"><i class="fas fa-tags me-2"></i>Paket Diskon</a>
                    <a href="/admin/pesanan.php" class="btn btn-outline-secondary btn-compact"><i class="fas fa-clipboard-list me-2"></i>Kelola Pesanan</a>
                </div>
                <div class="dropdown d-md-none w-100">
                    <button class="btn btn-outline-primary dropdown-toggle btn-compact w-100" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                        Menu Lainnya
                    </button>
                    <ul class="dropdown-menu dropdown-menu-end dropdown-menu-md-start shadow-sm w-100">
                        <li><a class="dropdown-item" href="/admin/paket_diskon.php"><i class="fas fa-tags me-2"></i>Paket Diskon</a></li>
                        <li><a class="dropdown-item" href="/admin/pesanan.php"><i class="fas fa-clipboard-list me-2"></i>Kelola Pesanan</a></li>
                    </ul>
                </div>
            </div>
            <?php if ($success): ?>
                <div class="alert alert-success"><?php echo htmlspecialchars($success); ?></div>
            <?php endif; ?>
            <?php if (!empty($errors)): ?>
                <div class="alert alert-danger">
                    <ul class="mb-0">
                        <?php foreach ($errors as $error): ?>
                            <li><?php echo htmlspecialchars($error); ?></li>
                        <?php endforeach; ?>
                    </ul>
                </div>
            <?php endif; ?>
            <div class="card border-0 shadow-sm admin-management-card mb-4 mt-3">
                <div class="card-body p-3 p-md-4">
                    <div class="d-flex flex-wrap align-items-center justify-content-between gap-2 mb-3">
                        <h5 class="fw-semibold mb-0"><?php echo $editData ? 'Edit Promo' : 'Tambah Promo Baru'; ?></h5>
                        <?php if ($editData): ?>
                            <a href="/admin/promo.php" class="btn btn-outline-secondary btn-sm btn-compact">Batal</a>
                        <?php endif; ?>
                    </div>
                    <form method="post" class="admin-management-form">
                        <input type="hidden" name="id" value="<?php echo $editData['id'] ?? ''; ?>">
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label">Nama Promo</label>
                                <input type="text" class="form-control" name="nama" value="<?php echo htmlspecialchars($editData['nama'] ?? ''); ?>" required>
                            </div>
                            <div class="col-md-3">
                                <label class="form-label">Diskon (%)</label>
                                <input type="number" class="form-control" name="diskon_persen" min="0" max="100" value="<?php echo htmlspecialchars($editData['diskon_persen'] ?? '0'); ?>" required>
                            </div>
                            <div class="col-md-3">
                                <label class="form-label">Bundling Terkait</label>
                                <select class="form-select" name="bundling_id">
                                    <option value="">Semua Bundling</option>
                                    <?php foreach ($bundlingOptions as $option): ?>
                                        <option value="<?php echo $option['id']; ?>" <?php echo (isset($editData['bundling_id']) && (int)$editData['bundling_id'] === (int)$option['id']) ? 'selected' : ''; ?>><?php echo htmlspecialchars($option['nama']); ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="col-md-3">
                                <label class="form-label">Tanggal Mulai</label>
                                <input type="date" class="form-control" name="tanggal_mulai" value="<?php echo htmlspecialchars($editData['tanggal_mulai'] ?? ''); ?>" required>
                            </div>
                            <div class="col-md-3">
                                <label class="form-label">Tanggal Berakhir</label>
                                <input type="date" class="form-control" name="tanggal_berakhir" value="<?php echo htmlspecialchars($editData['tanggal_berakhir'] ?? ''); ?>" required>
                            </div>
                            <div class="col-12">
                                <label class="form-label">Deskripsi</label>
                                <textarea class="form-control" name="deskripsi" rows="3" placeholder="Tuliskan detail promo atau syarat & ketentuan."><?php echo htmlspecialchars($editData['deskripsi'] ?? ''); ?></textarea>
                            </div>
                            <div class="col-12 d-flex flex-wrap gap-2">
                                <button type="submit" class="btn btn-primary btn-compact"><?php echo $editData ? 'Simpan Perubahan' : 'Tambah Promo'; ?></button>
                                <?php if ($editData): ?>
                                    <a href="/admin/promo.php" class="btn btn-outline-secondary btn-compact">Reset</a>
                                <?php endif; ?>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
            <div class="card border-0 shadow-sm admin-management-card">
                <div class="card-body p-3 p-md-4">
                    <div class="d-flex flex-wrap align-items-center justify-content-between gap-2 mb-3">
                        <h5 class="fw-semibold mb-0">Daftar Promo</h5>
                        <span class="text-muted small"><?php echo $promoList ? mysqli_num_rows($promoList) : 0; ?> promo aktif</span>
                    </div>
                    <div class="table-responsive admin-table-wrapper">
                        <table class="table table-striped">
                            <thead>
                                <tr>
                                    <th>#</th>
                                    <th>Nama Promo</th>
                                    <th>Diskon</th>
                                    <th>Periode</th>
                                    <th>Bundling</th>
                                    <th>Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if ($promoList && mysqli_num_rows($promoList) > 0): ?>
                                    <?php $no = 1; while ($promo = mysqli_fetch_assoc($promoList)): ?>
                                        <tr>
                                            <td><?php echo $no++; ?></td>
                                            <td><?php echo htmlspecialchars($promo['nama']); ?></td>
                                            <td><?php echo (int)$promo['diskon_persen']; ?>%</td>
                                            <td><?php echo date('d M Y', strtotime($promo['tanggal_mulai'])); ?> - <?php echo date('d M Y', strtotime($promo['tanggal_berakhir'])); ?></td>
                                            <td><?php echo htmlspecialchars($promo['bundling_nama'] ?? 'Semua Paket'); ?></td>
                                            <td>
                                                <a href="?edit=<?php echo $promo['id']; ?>" class="btn btn-sm btn-warning text-white"><i class="fas fa-edit"></i></a>
                                                <a href="?hapus=<?php echo $promo['id']; ?>" class="btn btn-sm btn-danger" onclick="return confirm('Hapus promo ini?');"><i class="fas fa-trash"></i></a>
                                            </td>
                                        </tr>
                                    <?php endwhile; ?>
                                <?php else: ?>
                                    <tr>
                                        <td colspan="6" class="text-center py-3">Belum ada promo tersimpan.</td>
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
