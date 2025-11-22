<?php
require_once __DIR__ . '/../../config/config.php';
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
if (empty($_SESSION['customer_id'])) {
    header('Location: /customer/login.php');
    exit;
}

$errors = [];
$success = '';

$paketList = [];
$paketResult = mysqli_query($conn, "SELECT * FROM paket_hosting ORDER BY harga ASC");
while ($paketResult && ($row = mysqli_fetch_assoc($paketResult))) {
    $paketList[] = $row;
}
if ($paketResult) {
    mysqli_free_result($paketResult);
}

$bundlingList = [];
$bundlingResult = mysqli_query($conn, "SELECT * FROM bundling_packages ORDER BY FIELD(slug, 'company-profile', 'personal-website', 'custom'), nama ASC");
while ($bundlingResult && ($row = mysqli_fetch_assoc($bundlingResult))) {
    $row['references'] = [];
    if (in_array($row['slug'], ['company-profile', 'personal-website'], true)) {
        $row['harga'] = 190000;
        $row['deskripsi'] = '';
    }
    if (!empty($row['reference_links'])) {
        $lines = preg_split('/\r?\n/', trim($row['reference_links']));
        foreach ($lines as $line) {
            if (strpos($line, '|') !== false) {
                [$label, $url] = array_map('trim', explode('|', $line, 2));
                if ($label !== '' && $url !== '') {
                    $row['references'][] = ['label' => $label, 'url' => $url];
                }
            }
        }
    }
    $bundlingList[] = $row;
}
if ($bundlingResult) {
    mysqli_free_result($bundlingResult);
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $customerId = (int)$_SESSION['customer_id'];
    $jenisPesanan = mysqli_real_escape_string($conn, $_POST['jenis_pesanan'] ?? 'hosting');
    $paketId = isset($_POST['paket_id']) ? (int)$_POST['paket_id'] : null;
    $bundlingId = isset($_POST['bundling_id']) ? (int)$_POST['bundling_id'] : null;
    $domain = trim($_POST['domain'] ?? '');
    $nomorTeleponInput = trim($_POST['nomor_telepon'] ?? '');
    $nomorTeleponNormalized = preg_replace('/[^0-9+]/', '', $nomorTeleponInput);
    $catatanCustom = trim($_POST['catatan_custom'] ?? '');
    $opsiPembayaran = $_POST['opsi_pembayaran'] ?? 'bank_mandiri';
    $projectUpload = $_FILES['project_file'] ?? null;
    $projectStoredRelative = null;
    $projectStoredAbsolute = null;
    $requiresProjectFile = false;
    $hasUploadedFile = $projectUpload && $projectUpload['error'] !== UPLOAD_ERR_NO_FILE;

    if (!in_array($jenisPesanan, ['hosting', 'bundling', 'custom'], true)) {
        $jenisPesanan = 'hosting';
    }

    $selectedPaket = null;
    $selectedBundling = null;

    if ($jenisPesanan === 'hosting') {
        if (empty($paketId)) {
            $errors[] = 'Silakan pilih paket hosting.';
        } else {
            foreach ($paketList as $paket) {
                if ((int)$paket['id'] === $paketId) {
                    $selectedPaket = $paket;
                    break;
                }
            }
            if (!$selectedPaket) {
                $errors[] = 'Paket hosting tidak ditemukan.';
            }
        }
        if ($domain === '') {
            $errors[] = 'Nama domain wajib diisi untuk paket hosting.';
        }
    } elseif ($jenisPesanan === 'bundling') {
        if (empty($bundlingId)) {
            $errors[] = 'Silakan pilih paket bundling.';
        } else {
            foreach ($bundlingList as $bundle) {
                if ((int)$bundle['id'] === $bundlingId) {
                    $selectedBundling = $bundle;
                    break;
                }
            }
            if (!$selectedBundling) {
                $errors[] = 'Paket bundling tidak ditemukan.';
            } elseif ($selectedBundling['slug'] === 'custom') {
                $errors[] = 'Gunakan opsi "Custom Project" untuk kebutuhan khusus.';
                $selectedBundling = null;
            }
        }
    } else {
        if ($catatanCustom === '') {
            $errors[] = 'Jelaskan kebutuhan paket custom Anda.';
        }
    }

    if (!in_array($opsiPembayaran, ['bank_mandiri', 'bank_bni', 'bank_bca'], true)) {
        $opsiPembayaran = 'bank_mandiri';
    }

    if ($jenisPesanan === 'hosting') {
        $requiresProjectFile = true;
    }

    if ($nomorTeleponInput === '') {
        $errors[] = 'Nomor telepon wajib diisi.';
    } elseif (!preg_match('/^\+?[0-9]{8,15}$/', $nomorTeleponNormalized)) {
        $errors[] = 'Nomor telepon harus berisi 8-15 digit angka dan boleh diawali dengan +.';
    }

    if ($hasUploadedFile) {
        if ($projectUpload['error'] !== UPLOAD_ERR_OK) {
            $errors[] = 'Gagal mengunggah file project. Silakan coba lagi.';
        } else {
            $extension = strtolower(pathinfo($projectUpload['name'], PATHINFO_EXTENSION));
            if ($extension !== 'zip') {
                $errors[] = 'File project harus berformat .zip.';
            }
            if ($projectUpload['size'] > 10 * 1024 * 1024) {
                $errors[] = 'Ukuran file project maksimal 10 MB.';
            }
        }
    } elseif ($requiresProjectFile) {
        $errors[] = 'Unggah file brief project berformat .zip untuk paket hosting reguler.';
    }

    if ($jenisPesanan === 'custom' && $catatanCustom === '') {
        $errors[] = 'Jelaskan kebutuhan paket custom Anda.';
    }

    $totalTagihan = 0;
    if ($selectedPaket) {
        $totalTagihan = (int)$selectedPaket['harga'];
    } elseif ($selectedBundling) {
        $totalTagihan = (int)$selectedBundling['harga'];
    }

    if (empty($errors) && $hasUploadedFile && $projectUpload['error'] === UPLOAD_ERR_OK) {
        if (function_exists('random_bytes')) {
            $uniqueSegment = bin2hex(random_bytes(4));
        } else {
            $uniqueSegment = bin2hex(pack('n*', mt_rand(0, 0xffff), mt_rand(0, 0xffff)));
        }
        $fileName = 'project_' . $customerId . '_' . time() . '_' . $uniqueSegment . '.zip';
        $destination = rtrim(PROJECT_UPLOAD_DIR, '/\\') . '/' . $fileName;
        if (move_uploaded_file($projectUpload['tmp_name'], $destination)) {
            $projectStoredRelative = trim(PROJECT_UPLOAD_URI, '/') . '/' . $fileName;
            $projectStoredAbsolute = $destination;
        } else {
            $errors[] = 'File project tidak dapat disimpan. Mohon coba kembali.';
        }
    }

    if (empty($errors)) {
        $paketValue = $selectedPaket ? (int)$selectedPaket['id'] : 'NULL';
        $bundlingValue = $selectedBundling ? (int)$selectedBundling['id'] : 'NULL';
        $domainEscaped = $domain !== '' ? "'" . mysqli_real_escape_string($conn, $domain) . "'" : 'NULL';
        $catatanEscaped = $catatanCustom !== '' ? "'" . mysqli_real_escape_string($conn, $catatanCustom) . "'" : 'NULL';
        $teleponEscaped = "'" . mysqli_real_escape_string($conn, $nomorTeleponNormalized) . "'";
        $opsiEscaped = mysqli_real_escape_string($conn, $opsiPembayaran);
        $jenisEscaped = mysqli_real_escape_string($conn, $jenisPesanan);
        $projectValue = $projectStoredRelative ? "'" . mysqli_real_escape_string($conn, $projectStoredRelative) . "'" : 'NULL';

        $insertSql = "INSERT INTO pesanan (user_id, paket_id, bundling_id, jenis_pesanan, domain, nomor_telepon, catatan_custom, metode_pembayaran, opsi_pembayaran, project_file, status, total_tagihan, tanggal_pesanan) VALUES ($customerId, $paketValue, $bundlingValue, '$jenisEscaped', $domainEscaped, $teleponEscaped, $catatanEscaped, 'Transfer Bank', '$opsiEscaped', $projectValue, 'menunggu_pembayaran', $totalTagihan, NOW())";
        $insert = mysqli_query($conn, $insertSql);

        if ($insert) {
            $orderId = mysqli_insert_id($conn);
            header('Location: /customer/pembayaran.php?id=' . $orderId);
            exit;
        }

        if ($projectStoredAbsolute && is_file($projectStoredAbsolute)) {
            unlink($projectStoredAbsolute);
        }

        $errors[] = 'Terjadi kesalahan saat menyimpan pesanan.';
    }
}
?>
<?php require_once __DIR__ . '/../../partials/header.php'; ?>
<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-lg-8">
            <div class="card shadow border-0">
                <div class="card-body p-5">
                    <h3 class="mb-4 text-center">Buat Pesanan Baru</h3>
                    <p class="text-muted text-center">Pilih antara paket hosting reguler, bundling website, atau tuliskan kebutuhan custom Anda.</p>
                    <?php if (!empty($errors)): ?>
                        <div class="alert alert-danger">
                            <ul class="mb-0">
                                <?php foreach ($errors as $error): ?>
                                    <li><?php echo htmlspecialchars($error); ?></li>
                                <?php endforeach; ?>
                            </ul>
                        </div>
                    <?php endif; ?>
                    <form method="post" class="needs-validation" enctype="multipart/form-data" novalidate>
                        <div class="mb-4">
                            <label class="form-label fw-semibold">Jenis Pesanan</label>
                            <div class="d-flex flex-wrap gap-3">
                                <?php
                                $jenisTerpilih = $_POST['jenis_pesanan'] ?? 'hosting';
                                $jenisOptions = [
                                    'hosting' => 'Hosting Reguler',
                                    'bundling' => 'Paket Bundling',
                                    'custom' => 'Custom Project',
                                ];
                                foreach ($jenisOptions as $value => $label):
                                    $checked = $jenisTerpilih === $value ? 'checked' : '';
                                ?>
                                    <div class="form-check">
                                        <input class="form-check-input" type="radio" name="jenis_pesanan" id="jenis_<?php echo $value; ?>" value="<?php echo $value; ?>" <?php echo $checked; ?>>
                                        <label class="form-check-label" for="jenis_<?php echo $value; ?>"><?php echo $label; ?></label>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        </div>
                        <div id="section-hosting" class="mb-4">
                            <div class="mb-3">
                                <label class="form-label">Paket Hosting</label>
                                <select class="form-select" name="paket_id">
                                    <option value="">Pilih paket</option>
                                    <?php foreach ($paketList as $paket): ?>
                                        <option value="<?php echo $paket['id']; ?>" <?php echo (isset($_POST['paket_id']) && (int)$_POST['paket_id'] === (int)$paket['id']) ? 'selected' : ''; ?>><?php echo htmlspecialchars($paket['nama_paket']); ?> - Rp <?php echo number_format($paket['harga'], 0, ',', '.'); ?>/bln</option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Nama Domain</label>
                                <input type="text" class="form-control" name="domain" placeholder="contoh: bisnisanda.com" value="<?php echo htmlspecialchars($_POST['domain'] ?? ''); ?>">
                            </div>
                        </div>
                        <div id="section-bundling" class="mb-4" style="display:none;">
                            <label class="form-label">Paket Bundling</label>
                            <div class="row g-3">
                                <?php foreach ($bundlingList as $bundle): ?>
                                    <?php if ($bundle['slug'] === 'custom') { continue; } ?>
                                    <div class="col-md-6">
                                        <div class="card h-100 shadow-sm border-0">
                                            <div class="card-body">
                                                <div class="d-flex justify-content-between align-items-start mb-3">
                                                    <div class="form-check">
                                                        <input class="form-check-input" type="radio" name="bundling_id" id="bundle_<?php echo $bundle['id']; ?>" value="<?php echo $bundle['id']; ?>" <?php echo (isset($_POST['bundling_id']) && (int)$_POST['bundling_id'] === (int)$bundle['id']) ? 'checked' : ''; ?>>
                                                        <label class="form-check-label fw-semibold" for="bundle_<?php echo $bundle['id']; ?>"><?php echo htmlspecialchars($bundle['nama']); ?></label>
                                                    </div>
                                                    <span class="badge bg-light text-primary">Rp <?php echo number_format($bundle['harga'], 0, ',', '.'); ?></span>
                                                </div>
                                                <?php if (!empty($bundle['highlight'])): ?>
                                                    <ul class="small text-muted ps-3 mb-3">
                                                        <?php foreach (explode('\n', $bundle['highlight']) as $point): ?>
                                                            <?php $trimmedPoint = trim($point); if ($trimmedPoint === '') { continue; } ?>
                                                            <li><?php echo htmlspecialchars($trimmedPoint); ?></li>
                                                        <?php endforeach; ?>
                                                    </ul>
                                                <?php endif; ?>
                                            </div>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        </div>
                        <div id="section-custom" class="mb-4" style="display:none;">
                            <label class="form-label">Catatan Kebutuhan</label>
                            <textarea class="form-control" name="catatan_custom" rows="4" placeholder="Contoh: Website katalog produk dengan integrasi marketplace & pembayaran COD."><?php echo htmlspecialchars($_POST['catatan_custom'] ?? ''); ?></textarea>
                        </div>
                        <div class="mb-4">
                            <label class="form-label">Nomor Telepon</label>
                            <input type="tel" class="form-control" name="nomor_telepon" placeholder="contoh: +62 812-1234-5678" value="<?php echo htmlspecialchars($_POST['nomor_telepon'] ?? ''); ?>" required pattern="\+?[0-9 ]{8,20}">
                            <div class="form-text">Gunakan nomor aktif yang dapat dihubungi untuk konfirmasi dan update progres pesanan.</div>
                        </div>
                        <div class="mb-4">
                            <label class="form-label">Upload Brief Project (ZIP)</label>
                            <input type="file" class="form-control" name="project_file" accept=".zip">
                            <div class="form-text">Format berkas wajib .zip dengan ukuran maksimal 10 MB. Wajib diisi untuk pesanan hosting reguler dan opsional untuk paket lainnya.</div>
                        </div>
                        <div class="mb-4">
                            <label class="form-label">Pilih Metode Pembayaran</label>
                            <?php $ops = $_POST['opsi_pembayaran'] ?? ''; ?>
                            <select name="opsi_pembayaran" class="form-select">
                                <option value="bank_mandiri" <?php echo $ops === 'bank_mandiri' ? 'selected' : ''; ?>>Transfer Bank Mandiri - 1800013831369</option>
                                <option value="bank_bni" <?php echo $ops === 'bank_bni' ? 'selected' : ''; ?>>Transfer Bank BNI - 1793840405</option>
                                <option value="bank_bca" <?php echo $ops === 'bank_bca' ? 'selected' : ''; ?>>Transfer Bank BCA - 0463308591</option>
                            </select>
                            <div class="form-text">Setelah membuat pesanan, unggah bukti pembayaran pada halaman pembayaran.</div>
                        </div>
                        <button type="submit" class="btn btn-primary w-100">Lanjutkan ke Pembayaran</button>
                    </form>
                    <div class="text-center mt-3">
                        <a href="/customer/dashboard.php" class="text-decoration-none">Kembali ke Dashboard</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<script>
    (function () {
        const jenisRadios = document.querySelectorAll('input[name="jenis_pesanan"]');
        const sectionHosting = document.getElementById('section-hosting');
        const sectionBundling = document.getElementById('section-bundling');
        const sectionCustom = document.getElementById('section-custom');
        const projectFileInput = document.querySelector('input[name="project_file"]');

        function toggleSections(value) {
            sectionHosting.style.display = value === 'hosting' ? 'block' : 'none';
            sectionBundling.style.display = value === 'bundling' ? 'block' : 'none';
            sectionCustom.style.display = value === 'custom' ? 'block' : 'none';
            if (projectFileInput) {
                projectFileInput.required = value === 'hosting';
            }
        }

        jenisRadios.forEach(radio => {
            radio.addEventListener('change', (event) => {
                toggleSections(event.target.value);
            });
        });

        const current = document.querySelector('input[name="jenis_pesanan"]:checked');
        toggleSections(current ? current.value : 'hosting');
    })();
</script>
<?php require_once __DIR__ . '/../../partials/footer.php'; ?>
