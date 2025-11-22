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

function sanitize_decimal($value)
{
    $filtered = preg_replace('/[^0-9.,]/', '', trim((string)$value));
    if ($filtered === '') {
        return 0.0;
    }

    $lastComma = strrpos($filtered, ',');
    $lastDot = strrpos($filtered, '.');

    if ($lastComma !== false && ($lastDot === false || $lastComma > $lastDot)) {
        // Format seperti 1.234,56 -> koma sebagai desimal.
        $normalized = str_replace('.', '', $filtered);
        $normalized = str_replace(',', '.', $normalized);
    } elseif ($lastDot !== false) {
        $fractionLength = strlen($filtered) - $lastDot - 1;
        if ($fractionLength === 0) {
            // Tidak ada angka setelah titik, anggap pemisah ribuan.
            $normalized = str_replace('.', '', $filtered);
            $normalized = str_replace(',', '', $normalized);
        } elseif ($fractionLength > 0 && $fractionLength <= 2) {
            // Titik sebagai desimal, koma sebagai ribuan.
            $normalized = str_replace(',', '', $filtered);
        } else {
            // Titik sebagai ribuan, hapus seluruh koma juga.
            $normalized = str_replace(['.', ','], ['', ''], $filtered);
        }
    } else {
        $normalized = str_replace(',', '', $filtered);
    }

    if (substr_count($normalized, '.') > 1) {
        $parts = explode('.', $normalized);
        $decimal = array_pop($parts);
        $normalized = implode('', $parts) . '.' . $decimal;
    }

    return is_numeric($normalized) ? (float)$normalized : 0.0;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = (int)($_POST['id'] ?? 0);
    $nama = mysqli_real_escape_string($conn, trim($_POST['nama_paket'] ?? ''));
    $deskripsi = mysqli_real_escape_string($conn, trim($_POST['deskripsi'] ?? ''));
    $hargaNormal = sanitize_decimal($_POST['harga_normal'] ?? 0);
    $hargaDiskon = sanitize_decimal($_POST['harga_diskon'] ?? 0);
    $tanggalMulai = trim($_POST['tanggal_mulai'] ?? '');
    $tanggalSelesai = trim($_POST['tanggal_selesai'] ?? '');
    $status = $_POST['status'] ?? 'aktif';
    $status = $status === 'draft' ? 'draft' : 'aktif';

    if ($nama === '') {
        $errors[] = 'Nama paket diskon wajib diisi.';
    }
    if ($hargaNormal <= 0 || $hargaDiskon <= 0) {
        $errors[] = 'Harga normal dan harga diskon harus lebih besar dari 0.';
    } elseif ($hargaDiskon >= $hargaNormal) {
        $errors[] = 'Harga diskon harus lebih rendah dari harga normal.';
    }

    $mulaiSql = $tanggalMulai !== '' ? "'" . mysqli_real_escape_string($conn, $tanggalMulai) . "'" : 'NULL';
    $selesaiSql = $tanggalSelesai !== '' ? "'" . mysqli_real_escape_string($conn, $tanggalSelesai) . "'" : 'NULL';
    $hargaNormalSql = number_format($hargaNormal, 2, '.', '');
    $hargaDiskonSql = number_format($hargaDiskon, 2, '.', '');

    if (empty($errors)) {
        if ($id > 0) {
            $query = "UPDATE paket_diskon SET nama_paket='$nama', deskripsi='$deskripsi', harga_normal=$hargaNormalSql, harga_diskon=$hargaDiskonSql, tanggal_mulai=$mulaiSql, tanggal_selesai=$selesaiSql, status='$status' WHERE id=$id";
            $result = mysqli_query($conn, $query);
            if ($result) {
                $success = 'Paket diskon berhasil diperbarui.';
            } else {
                $errors[] = 'Gagal memperbarui paket diskon.';
            }
        } else {
            $query = "INSERT INTO paket_diskon (nama_paket, deskripsi, harga_normal, harga_diskon, tanggal_mulai, tanggal_selesai, status) VALUES ('$nama', '$deskripsi', $hargaNormalSql, $hargaDiskonSql, $mulaiSql, $selesaiSql, '$status')";
            $result = mysqli_query($conn, $query);
            if ($result) {
                $success = 'Paket diskon baru berhasil ditambahkan.';
            } else {
                $errors[] = 'Gagal menambahkan paket diskon.';
            }
        }
    }
}

if (isset($_GET['hapus'])) {
    $hapusId = (int)$_GET['hapus'];
    mysqli_query($conn, "DELETE FROM paket_diskon WHERE id=$hapusId");
    header('Location: paket_diskon.php');
    exit;
}

$editData = null;
if (isset($_GET['edit'])) {
    $editId = (int)$_GET['edit'];
    $editQuery = mysqli_query($conn, "SELECT * FROM paket_diskon WHERE id=$editId");
    $editData = mysqli_fetch_assoc($editQuery);
}

$paketDiskonList = mysqli_query($conn, "SELECT * FROM paket_diskon ORDER BY created_at DESC");
$paketDiskonCount = $paketDiskonList ? mysqli_num_rows($paketDiskonList) : 0;
?>
<?php require_once __DIR__ . '/../../partials/header.php'; ?>
<div class="admin-layout d-lg-flex">
    <?php require_once __DIR__ . '/../../partials/sidebar.php'; ?>
    <div class="admin-dashboard flex-grow-1 bg-light min-vh-100">
        <div class="admin-dashboard__inner container-fluid py-3 py-md-4">
            <div class="admin-dashboard-top d-flex flex-wrap align-items-center justify-content-between gap-2 gap-md-3">
                <div class="d-flex align-items-center gap-2 gap-md-3">
                    <button class="btn btn-outline-primary btn-compact d-lg-none" type="button" data-bs-toggle="offcanvas" data-bs-target="#sidebarMenu" aria-controls="sidebarMenu">
                        <i class="fas fa-bars me-2"></i>Menu
                    </button>
                    <div>
                        <h2 class="fw-bold mb-1">Kelola Paket Diskon</h2>
                        <p class="text-muted mb-0">Atur paket promo yang muncul pada halaman promo pelanggan.</p>
                    </div>
                </div>
                <div class="admin-dashboard-actions d-none d-md-flex align-items-center gap-2">
                    <a href="/admin/paket.php" class="btn btn-outline-primary btn-compact"><i class="fas fa-box-open me-2"></i>Paket Hosting</a>
                </div>
                <div class="dropdown d-md-none">
                    <button class="btn btn-outline-primary dropdown-toggle btn-compact w-100" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                        Aksi Lainnya
                    </button>
                    <ul class="dropdown-menu dropdown-menu-end shadow-sm">
                        <li><a class="dropdown-item" href="/admin/paket.php"><i class="fas fa-box-open me-2"></i>Paket Hosting</a></li>
                    </ul>
                </div>
            </div>

            <?php if ($success): ?>
                <div class="alert alert-success admin-feedback mt-3"><?php echo htmlspecialchars($success); ?></div>
            <?php endif; ?>
            <?php if (!empty($errors)): ?>
                <div class="alert alert-danger admin-feedback mt-3">
                    <ul class="mb-0">
                        <?php foreach ($errors as $error): ?>
                            <li><?php echo htmlspecialchars($error); ?></li>
                        <?php endforeach; ?>
                    </ul>
                </div>
            <?php endif; ?>

            <div class="card border-0 shadow-sm admin-management-card mt-3">
                <div class="card-body p-3 p-md-4">
                    <div class="d-flex flex-wrap align-items-center justify-content-between gap-2 mb-3">
                        <h5 class="fw-semibold mb-0"><?php echo $editData ? 'Edit Paket Diskon' : 'Tambah Paket Diskon'; ?></h5>
                        <?php if ($editData): ?>
                            <a href="/admin/paket_diskon.php" class="btn btn-outline-secondary btn-sm btn-compact">Batal</a>
                        <?php endif; ?>
                    </div>
                    <form method="post" class="admin-management-form">
                        <input type="hidden" name="id" value="<?php echo $editData['id'] ?? ''; ?>">
                        <div class="row g-3 g-md-4">
                            <div class="col-12 col-md-6">
                                <label class="form-label fw-medium">Nama Paket</label>
                                <input type="text" class="form-control" name="nama_paket" value="<?php echo htmlspecialchars($editData['nama_paket'] ?? ''); ?>" placeholder="Contoh: Promo Akhir Tahun" required>
                            </div>
                            <div class="col-12 col-md-3">
                                <label class="form-label fw-medium">Harga Normal (Rp)</label>
                                <input type="number" class="form-control" name="harga_normal" min="0" step="1000" value="<?php echo isset($editData['harga_normal']) ? (float)$editData['harga_normal'] : ''; ?>" placeholder="1.500.000" required>
                            </div>
                            <div class="col-12 col-md-3">
                                <label class="form-label fw-medium">Harga Diskon (Rp)</label>
                                <input type="number" class="form-control" name="harga_diskon" min="0" step="1000" value="<?php echo isset($editData['harga_diskon']) ? (float)$editData['harga_diskon'] : ''; ?>" placeholder="1.200.000" required>
                            </div>
                            <div class="col-12">
                                <label class="form-label fw-medium">Deskripsi Promo</label>
                                <textarea class="form-control" name="deskripsi" rows="3" placeholder="Tulis highlight promo dan benefit."><?php echo htmlspecialchars($editData['deskripsi'] ?? ''); ?></textarea>
                                <small class="text-muted d-block mt-2">Gunakan deskripsi singkat yang menonjolkan benefit utama.</small>
                            </div>
                            <div class="col-6 col-md-3">
                                <label class="form-label fw-medium">Tanggal Mulai</label>
                                <input type="date" class="form-control" name="tanggal_mulai" value="<?php echo htmlspecialchars($editData['tanggal_mulai'] ?? ''); ?>">
                            </div>
                            <div class="col-6 col-md-3">
                                <label class="form-label fw-medium">Tanggal Selesai</label>
                                <input type="date" class="form-control" name="tanggal_selesai" value="<?php echo htmlspecialchars($editData['tanggal_selesai'] ?? ''); ?>">
                            </div>
                            <div class="col-12 col-md-3">
                                <label class="form-label fw-medium">Status</label>
                                <select class="form-select" name="status">
                                    <option value="aktif" <?php echo ($editData['status'] ?? '') === 'aktif' ? 'selected' : ''; ?>>Aktif</option>
                                    <option value="draft" <?php echo ($editData['status'] ?? '') === 'draft' ? 'selected' : ''; ?>>Draft</option>
                                </select>
                            </div>
                            <div class="col-12 d-flex flex-wrap gap-2">
                                <button type="submit" class="btn btn-primary btn-compact"><?php echo $editData ? 'Simpan Perubahan' : 'Tambah Paket Diskon'; ?></button>
                                <?php if ($editData): ?>
                                    <a href="/admin/paket_diskon.php" class="btn btn-outline-secondary btn-compact">Reset</a>
                                <?php endif; ?>
                            </div>
                        </div>
                    </form>
                </div>
            </div>

            <div class="card border-0 shadow-sm admin-management-card mt-3 mt-md-4">
                <div class="card-body p-3 p-md-4">
                    <div class="d-flex flex-wrap align-items-center justify-content-between gap-2 mb-3">
                        <h5 class="fw-semibold mb-0">Daftar Paket Diskon</h5>
                        <span class="text-muted small"><?php echo $paketDiskonCount; ?> paket promo</span>
                    </div>
                    <div class="table-responsive admin-table-wrapper">
                        <table class="table table-hover align-middle mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th scope="col" class="text-nowrap">#</th>
                                    <th scope="col">Nama Paket</th>
                                    <th scope="col" class="text-nowrap">Harga Normal</th>
                                    <th scope="col" class="text-nowrap">Harga Diskon</th>
                                    <th scope="col">Periode</th>
                                    <th scope="col" class="text-center text-nowrap">Status</th>
                                    <th scope="col" class="text-center text-nowrap">Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if ($paketDiskonList && $paketDiskonCount > 0): ?>
                                    <?php $no = 1; while ($paket = mysqli_fetch_assoc($paketDiskonList)): ?>
                                        <tr>
                                            <td class="text-muted"><?php echo $no++; ?></td>
                                            <td>
                                                <div class="fw-semibold mb-1"><?php echo htmlspecialchars($paket['nama_paket']); ?></div>
                                                <?php if (!empty($paket['deskripsi'])): ?>
                                                    <?php
                                                    $plainDesc = strip_tags($paket['deskripsi']);
                                                    if (function_exists('mb_strimwidth')) {
                                                        $shortDesc = mb_strimwidth($plainDesc, 0, 90, '...');
                                                    } else {
                                                        $shortDesc = strlen($plainDesc) > 90 ? substr($plainDesc, 0, 87) . '...' : $plainDesc;
                                                    }
                                                    ?>
                                                    <div class="text-muted small"><?php echo htmlspecialchars($shortDesc); ?></div>
                                                <?php endif; ?>
                                            </td>
                                            <td class="text-muted">Rp <?php echo number_format($paket['harga_normal'], 0, ',', '.'); ?></td>
                                            <td class="text-success fw-semibold">Rp <?php echo number_format($paket['harga_diskon'], 0, ',', '.'); ?></td>
                                            <td class="admin-description-cell">
                                                <?php
                                                $period = [];
                                                if (!empty($paket['tanggal_mulai'])) {
                                                    $period[] = date('d M Y', strtotime($paket['tanggal_mulai']));
                                                }
                                                if (!empty($paket['tanggal_selesai'])) {
                                                    $period[] = date('d M Y', strtotime($paket['tanggal_selesai']));
                                                }
                                                echo $period ? htmlspecialchars(implode(' - ', $period)) : '-';
                                                ?>
                                            </td>
                                            <td class="text-center">
                                                <?php if ($paket['status'] === 'aktif'): ?>
                                                    <span class="badge bg-success">Aktif</span>
                                                <?php else: ?>
                                                    <span class="badge bg-secondary">Draft</span>
                                                <?php endif; ?>
                                            </td>
                                            <td class="text-center">
                                                <div class="d-inline-flex gap-2">
                                                    <a href="?edit=<?php echo $paket['id']; ?>" class="btn btn-sm btn-warning text-white btn-compact"><i class="fas fa-edit"></i></a>
                                                    <a href="?hapus=<?php echo $paket['id']; ?>" class="btn btn-sm btn-danger btn-compact" onclick="return confirm('Hapus paket diskon ini?');"><i class="fas fa-trash"></i></a>
                                                </div>
                                            </td>
                                        </tr>
                                    <?php endwhile; ?>
                                <?php else: ?>
                                    <tr>
                                        <td colspan="7" class="text-center py-3">Belum ada paket diskon.</td>
                                    </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<?php require_once __DIR__ . '/../../partials/footer.php'; ?>
