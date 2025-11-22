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
$orderId = isset($_GET['id']) ? (int)$_GET['id'] : 0;
if ($orderId <= 0) {
    header('Location: /customer/dashboard.php');
    exit;
}

$orderSql = "SELECT p.*, pk.nama_paket, pk.harga AS harga_paket, b.nama AS bundling_nama, b.harga AS harga_bundling, u.nama AS customer_nama FROM pesanan p LEFT JOIN paket_hosting pk ON pk.id = p.paket_id LEFT JOIN bundling_packages b ON b.id = p.bundling_id JOIN users u ON u.id = p.user_id WHERE p.id = $orderId AND p.user_id = $customerId LIMIT 1";
$orderResult = mysqli_query($conn, $orderSql);
if (!$orderResult || mysqli_num_rows($orderResult) === 0) {
    header('Location: /customer/dashboard.php');
    exit;
}

$order = mysqli_fetch_assoc($orderResult);
mysqli_free_result($orderResult);

$errors = [];
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['bukti_pembayaran'])) {
    $file = $_FILES['bukti_pembayaran'];
    if ($file['error'] === UPLOAD_ERR_NO_FILE) {
        $errors[] = 'Silakan pilih file bukti pembayaran.';
    } elseif ($file['error'] !== UPLOAD_ERR_OK) {
        $errors[] = 'Gagal mengunggah bukti pembayaran. Silakan coba kembali.';
    } else {
        $allowedExtensions = ['jpg', 'jpeg', 'png', 'pdf'];
        $extension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        if (!in_array($extension, $allowedExtensions, true)) {
            $errors[] = 'Format file tidak didukung. Gunakan JPG, PNG, atau PDF.';
        }

        $maxSize = 5 * 1024 * 1024; // 5 MB
        if ($file['size'] > $maxSize) {
            $errors[] = 'Ukuran file melebihi 5 MB. Harap kompres terlebih dahulu.';
        }

        if (empty($errors)) {
            try {
                $filename = 'payment_' . $customerId . '_' . time() . '_' . bin2hex(random_bytes(4)) . '.' . $extension;
            } catch (Exception $e) {
                $errors[] = 'Terjadi kesalahan saat menyiapkan penyimpanan bukti pembayaran.';
                $filename = '';
            }

            if ($filename !== '') {
                $targetPath = rtrim(PAYMENT_UPLOAD_DIR, '/') . '/' . $filename;
                if (!move_uploaded_file($file['tmp_name'], $targetPath)) {
                    $errors[] = 'Bukti pembayaran tidak dapat disimpan. Coba ulangi.';
                } else {
                    $relativePath = trim(PAYMENT_UPLOAD_URI, '/') . '/' . $filename;
                    $relativeEscaped = mysqli_real_escape_string($conn, $relativePath);
                    $updateSql = "UPDATE pesanan SET bukti_pembayaran = '$relativeEscaped', status = 'menunggu_konfirmasi', tanggal_konfirmasi = NULL WHERE id = $orderId AND user_id = $customerId";
                    if (mysqli_query($conn, $updateSql)) {
                        $order['bukti_pembayaran'] = $relativePath;
                        $order['status'] = 'menunggu_konfirmasi';
                        $order['tanggal_konfirmasi'] = null;
                        $success = 'Bukti pembayaran berhasil diunggah. Tim admin akan melakukan verifikasi maksimal 1-2 jam.';
                    } else {
                        $errors[] = 'Terjadi kesalahan saat menyimpan bukti pembayaran.';
                        unlink($targetPath);
                    }
                }
            }
        }
    }
}

$statusSteps = [
    'menunggu_pembayaran' => 'Menunggu Pembayaran',
    'menunggu_konfirmasi' => 'Menunggu Konfirmasi',
    'diproses' => 'Diproses Admin',
    'selesai' => 'Selesai',
    'dibatalkan' => 'Dibatalkan',
];

$paymentAccounts = [
    'bank_mandiri' => ['label' => 'Bank Mandiri', 'rekening' => '1800013831369'],
    'bank_bni' => ['label' => 'Bank BNI', 'rekening' => '1793840405'],
    'bank_bca' => ['label' => 'Bank BCA', 'rekening' => '0463308591'],
];

$selectedPayment = $paymentAccounts[$order['opsi_pembayaran']] ?? null;

require_once __DIR__ . '/../../partials/header.php';
?>
<div class="container py-5">
    <div class="row g-4">
        <div class="col-lg-8">
            <div class="card shadow-sm border-0">
                <div class="card-body p-4">
                    <h3 class="fw-bold mb-3">Pembayaran Pesanan #<?php echo htmlspecialchars($order['id']); ?></h3>
                    <p class="text-muted">Ikuti alur pembayaran di bawah ini. Jika verifikasi melebihi 2 jam, tim kami akan menghubungi Anda melalui WhatsApp.</p>
                    <div class="status-timeline mt-4">
                        <?php
                        $currentStatus = $order['status'];
                        $stepOrder = ['menunggu_pembayaran', 'menunggu_konfirmasi', 'diproses', 'selesai'];
                        $index = 1;
                        foreach ($stepOrder as $key) {
                            $isActive = ($index === 1);
                            if ($currentStatus === $key) {
                                $isActive = true;
                            } else {
                                $currentIndex = array_search($currentStatus, $stepOrder, true);
                                if ($currentIndex !== false && $index - 1 < $currentIndex) {
                                    $isActive = true;
                                }
                            }
                        ?>
                            <div class="status-step <?php echo $isActive ? 'active' : ''; ?>">
                                <div class="step-index"><?php echo $index; ?></div>
                                <h6 class="fw-semibold mb-1"><?php echo $statusSteps[$key] ?? ucfirst($key); ?></h6>
                                <p class="small text-muted mb-0">
                                    <?php if ($key === 'menunggu_pembayaran'): ?>Transfer ke rekening yang tersedia dan unggah bukti pembayaran Anda.
                                    <?php elseif ($key === 'menunggu_konfirmasi'): ?>Tim admin sedang memverifikasi bukti pembayaran Anda.
                                    <?php elseif ($key === 'diproses'): ?>Admin mempersiapkan layanan Anda (estimasi 1-2 jam).
                                    <?php elseif ($key === 'selesai'): ?>Layanan aktif. Hubungi kami jika memerlukan bantuan tambahan.
                                    <?php else: ?>Status pesanan telah diperbarui.
                                    <?php endif; ?>
                                </p>
                            </div>
                        <?php
                            $index++;
                        }
                        ?>
                    </div>
                </div>
            </div>
            <div class="card shadow-sm border-0 mt-4">
                <div class="card-body p-4">
                    <h5 class="fw-semibold mb-3">Unggah Bukti Pembayaran</h5>
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
                    <div class="payment-proof-card mb-3">
                        <p class="mb-2">Unggah bukti transfer dalam bentuk JPG, PNG, atau PDF (maks. 5 MB).</p>
                        <?php if (!empty($order['bukti_pembayaran'])): ?>
                            <p class="small">Bukti saat ini: <a href="/<?php echo htmlspecialchars($order['bukti_pembayaran']); ?>" target="_blank" rel="noopener">Lihat berkas</a></p>
                        <?php endif; ?>
                        <form method="post" enctype="multipart/form-data">
                            <div class="mb-3">
                                <input type="file" class="form-control" name="bukti_pembayaran" accept=".jpg,.jpeg,.png,.pdf" required>
                            </div>
                            <button type="submit" class="btn btn-primary">Unggah Bukti Pembayaran</button>
                        </form>
                    </div>
                    <p class="small text-muted mb-0">Setelah bukti terunggah, status pesanan berubah menjadi <strong>menunggu konfirmasi</strong>. Kami akan menghubungi Anda jika dibutuhkan informasi tambahan.</p>
                </div>
            </div>
            <a href="/customer/dashboard.php" class="btn btn-outline-secondary w-100 mt-4">Kembali ke Dashboard</a>
        </div>
        <div class="col-lg-4">
            <div class="card shadow-sm border-0">
                <div class="card-body p-4">
                    <h5 class="fw-semibold mb-3">Ringkasan Pesanan</h5>
                    <p class="mb-1"><strong>Nama Pelanggan:</strong> <?php echo htmlspecialchars($order['customer_nama']); ?></p>
                    <p class="mb-1"><strong>Jenis Pesanan:</strong> <?php echo ucfirst($order['jenis_pesanan']); ?></p>
                    <?php if ($order['jenis_pesanan'] === 'hosting' && $order['nama_paket']): ?>
                        <p class="mb-1"><strong>Paket Hosting:</strong> <?php echo htmlspecialchars($order['nama_paket']); ?></p>
                    <?php elseif ($order['jenis_pesanan'] === 'bundling' && $order['bundling_nama']): ?>
                        <p class="mb-1"><strong>Paket Bundling:</strong> <?php echo htmlspecialchars($order['bundling_nama']); ?></p>
                    <?php endif; ?>
                    <?php if (!empty($order['domain'])): ?>
                        <p class="mb-1"><strong>Domain/URL:</strong> <?php echo htmlspecialchars($order['domain']); ?></p>
                    <?php endif; ?>
                    <?php if (!empty($order['catatan_custom'])): ?>
                        <p class="mb-1"><strong>Catatan Custom:</strong> <?php echo nl2br(htmlspecialchars($order['catatan_custom'])); ?></p>
                    <?php endif; ?>
                    <p class="mb-1"><strong>Total Tagihan:</strong> Rp <?php echo number_format((int)$order['total_tagihan'], 0, ',', '.'); ?></p>
                    <p class="mb-1"><strong>Status Saat Ini:</strong> <span class="badge bg-primary"><?php echo $statusSteps[$order['status']] ?? ucfirst($order['status']); ?></span></p>
                    <p class="mb-0"><small>Tanggal Pesanan: <?php echo date('d M Y H:i', strtotime($order['tanggal_pesanan'])); ?></small></p>
                </div>
            </div>
            <div class="card shadow-sm border-0 mt-4">
                <div class="card-body p-4">
                    <h5 class="fw-semibold mb-3">Detail Pembayaran</h5>
                    <?php if ($selectedPayment): ?>
                        <p class="mb-1"><strong>Transfer ke:</strong> <?php echo htmlspecialchars($selectedPayment['label']); ?></p>
                        <p class="mb-3"><strong>No. Rekening:</strong> <?php echo htmlspecialchars($selectedPayment['rekening']); ?></p>
                    <?php else: ?>
                        <p class="text-muted">Pilih metode pembayaran pada formulir pemesanan.</p>
                    <?php endif; ?>
                    <p class="small text-muted mb-2">Setelah pembayaran diverifikasi, layanan akan diproses maksimal 1-2 jam. Jika melebihi waktu tersebut, hubungi kami.</p>
                    <a href="https://wa.me/6285175394358" class="btn btn-outline-primary w-100" target="_blank" rel="noopener"><i class="fab fa-whatsapp me-2"></i>Hubungi via WhatsApp</a>
                </div>
            </div>
        </div>
    </div>
</div>
<?php require_once __DIR__ . '/../../partials/footer.php'; ?>
