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

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nama = mysqli_real_escape_string($conn, trim($_POST['nama_paket'] ?? ''));
    $deskripsi = mysqli_real_escape_string($conn, trim($_POST['deskripsi'] ?? ''));
    $harga = (int)($_POST['harga'] ?? 0);
    $fitur = mysqli_real_escape_string($conn, trim($_POST['fitur'] ?? ''));
    $id = (int)($_POST['id'] ?? 0);

    if ($nama === '' || $harga <= 0) {
        $errors[] = 'Nama paket dan harga wajib diisi.';
    }

    if (empty($errors)) {
        if ($id > 0) {
            $update = mysqli_query($conn, "UPDATE paket_hosting SET nama_paket='$nama', deskripsi='$deskripsi', harga=$harga, fitur='$fitur' WHERE id=$id");
            if ($update) {
                $success = 'Paket berhasil diperbarui.';
            } else {
                $errors[] = 'Gagal memperbarui paket.';
            }
        } else {
            $insert = mysqli_query($conn, "INSERT INTO paket_hosting (nama_paket, deskripsi, harga, fitur) VALUES ('$nama', '$deskripsi', $harga, '$fitur')");
            if ($insert) {
                $success = 'Paket baru berhasil ditambahkan.';
            } else {
                $errors[] = 'Gagal menambahkan paket.';
            }
        }
    }
}

if (isset($_GET['hapus'])) {
    $hapusId = (int)$_GET['hapus'];
    mysqli_query($conn, "DELETE FROM paket_hosting WHERE id=$hapusId");
    header('Location: paket.php');
    exit;
}

$editData = null;
if (isset($_GET['edit'])) {
    $editId = (int)$_GET['edit'];
    $editQuery = mysqli_query($conn, "SELECT * FROM paket_hosting WHERE id=$editId");
    $editData = mysqli_fetch_assoc($editQuery);
}

$paketList = mysqli_query($conn, "SELECT * FROM paket_hosting ORDER BY harga ASC");
$paketCount = $paketList ? mysqli_num_rows($paketList) : 0;
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
                        <h2 class="fw-bold mb-1">Kelola Paket Hosting</h2>
                        <p class="text-muted mb-0">Tambah, ubah, atau hapus paket hosting ClouSting.</p>
                    </div>
                </div>
                <div class="admin-dashboard-actions d-none d-md-flex align-items-center gap-2">
                    <a href="/admin/paket_diskon.php" class="btn btn-outline-primary btn-compact"><i class="fas fa-tags me-2"></i>Paket Diskon</a>
                </div>
                <div class="dropdown d-md-none">
                    <button class="btn btn-outline-primary dropdown-toggle btn-compact w-100" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                        Aksi Lainnya
                    </button>
                    <ul class="dropdown-menu dropdown-menu-end shadow-sm">
                        <li><a class="dropdown-item" href="/admin/paket_diskon.php"><i class="fas fa-tags me-2"></i>Paket Diskon</a></li>
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
                        <h5 class="fw-semibold mb-0"><?php echo $editData ? 'Edit Paket Hosting' : 'Tambah Paket Baru'; ?></h5>
                        <?php if ($editData): ?>
                            <a href="/admin/paket.php" class="btn btn-outline-secondary btn-sm btn-compact">Batal</a>
                        <?php endif; ?>
                    </div>
                    <form method="post" class="admin-management-form">
                        <input type="hidden" name="id" value="<?php echo $editData['id'] ?? ''; ?>">
                        <div class="row g-3 g-md-4">
                            <div class="col-12 col-md-6">
                                <label class="form-label fw-medium">Nama Paket</label>
                                <input type="text" class="form-control" name="nama_paket" value="<?php echo htmlspecialchars($editData['nama_paket'] ?? ''); ?>" placeholder="Contoh: Paket Starter" required>
                            </div>
                            <div class="col-12 col-md-6">
                                <label class="form-label fw-medium">Harga (Rp)</label>
                                <input type="number" class="form-control" name="harga" value="<?php echo htmlspecialchars($editData['harga'] ?? ''); ?>" min="10000" step="1000" placeholder="Mulai dari 100.000" required>
                            </div>
                            <div class="col-12">
                                <label class="form-label fw-medium">Deskripsi Singkat</label>
                                <input type="text" class="form-control" name="deskripsi" value="<?php echo htmlspecialchars($editData['deskripsi'] ?? ''); ?>" placeholder="Highlight utama paket">
                            </div>
                            <div class="col-12">
                                <label class="form-label fw-medium">Fitur (pisahkan dengan baris baru)</label>
                                <textarea class="form-control" name="fitur" rows="4" placeholder="Unlimited Bandwidth&#10;Gratis SSL&#10;Backup Harian"><?php echo htmlspecialchars($editData['fitur'] ?? ''); ?></textarea>
                                <small class="text-muted d-block mt-2">Gunakan enter untuk membuat daftar fitur agar mudah dibaca pelanggan.</small>
                            </div>
                            <div class="col-12 d-flex flex-wrap gap-2">
                                <button type="submit" class="btn btn-primary btn-compact"><?php echo $editData ? 'Simpan Perubahan' : 'Tambah Paket'; ?></button>
                                <?php if ($editData): ?>
                                    <a href="/admin/paket.php" class="btn btn-outline-secondary btn-compact">Reset</a>
                                <?php endif; ?>
                            </div>
                        </div>
                    </form>
                </div>
            </div>

            <div class="card border-0 shadow-sm admin-management-card mt-3 mt-md-4">
                <div class="card-body p-3 p-md-4">
                    <div class="d-flex flex-wrap align-items-center justify-content-between gap-2 mb-3">
                        <h5 class="fw-semibold mb-0">Daftar Paket Hosting</h5>
                        <span class="text-muted small"><?php echo $paketCount; ?> paket aktif</span>
                    </div>
                    <div class="table-responsive admin-table-wrapper">
                        <table class="table table-hover align-middle mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th scope="col" class="text-nowrap">#</th>
                                    <th scope="col">Nama Paket</th>
                                    <th scope="col" class="text-nowrap">Harga</th>
                                    <th scope="col">Deskripsi</th>
                                    <th scope="col" class="text-center text-nowrap">Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if ($paketCount > 0): ?>
                                    <?php $no = 1; while ($paket = mysqli_fetch_assoc($paketList)): ?>
                                        <tr>
                                            <td class="text-muted"><?php echo $no++; ?></td>
                                            <td>
                                                <div class="fw-semibold mb-1"><?php echo htmlspecialchars($paket['nama_paket']); ?></div>
                                                <?php if (!empty($paket['fitur'])): ?>
                                                    <div class="text-muted small"><?php echo nl2br(htmlspecialchars($paket['fitur'])); ?></div>
                                                <?php endif; ?>
                                            </td>
                                            <td class="fw-semibold">Rp <?php echo number_format($paket['harga'], 0, ',', '.'); ?></td>
                                            <?php
                                            $fullDescription = trim($paket['deskripsi'] ?? '');
                                            $previewDescription = $fullDescription;
                                            $maxDescriptionLength = 140;
                                            $hasMoreDescription = false;

                                            if ($fullDescription !== '') {
                                                if (function_exists('mb_strlen') && function_exists('mb_substr')) {
                                                    if (mb_strlen($fullDescription) > $maxDescriptionLength) {
                                                        $previewDescription = mb_substr($fullDescription, 0, $maxDescriptionLength);
                                                        $hasMoreDescription = true;
                                                    }
                                                } elseif (strlen($fullDescription) > $maxDescriptionLength) {
                                                    $previewDescription = substr($fullDescription, 0, $maxDescriptionLength);
                                                    $hasMoreDescription = true;
                                                }

                                                if ($hasMoreDescription) {
                                                    $previewDescription = rtrim($previewDescription) . '…';
                                                }
                                            }
                                            ?>
                                            <td class="text-muted admin-description-cell">
                                                <?php if ($fullDescription !== ''): ?>
                                                    <?php $titleAttribute = $hasMoreDescription ? ' title="' . htmlspecialchars($fullDescription, ENT_QUOTES, 'UTF-8') . '"' : ''; ?>
                                                    <div class="description-preview"<?php echo $titleAttribute; ?>>
                                                        <?php echo nl2br(htmlspecialchars($previewDescription, ENT_QUOTES, 'UTF-8')); ?>
                                                    </div>
                                                <?php else: ?>
                                                    <span class="text-muted">-</span>
                                                <?php endif; ?>
                                            </td>
                                            <td class="text-center">
                                                <div class="d-inline-flex gap-2">
                                                    <a href="?edit=<?php echo $paket['id']; ?>" class="btn btn-sm btn-warning text-white btn-compact"><i class="fas fa-edit"></i></a>
                                                    <a href="?hapus=<?php echo $paket['id']; ?>" class="btn btn-sm btn-danger btn-compact" onclick="return confirm('Hapus paket ini?');"><i class="fas fa-trash"></i></a>
                                                </div>
                                            </td>
                                        </tr>
                                    <?php endwhile; ?>
                                <?php else: ?>
                                    <tr>
                                        <td colspan="5" class="text-center py-3">Belum ada paket hosting.</td>
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
