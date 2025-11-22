<?php
require_once __DIR__ . '/../partials/header.php';
require_once __DIR__ . '/../partials/navbar.php';
require_once __DIR__ . '/../config/config.php';

$discountQuery = mysqli_query($conn, "SELECT * FROM paket_diskon WHERE status='aktif' ORDER BY harga_diskon ASC, nama_paket ASC");
?>
<section class="py-5 bg-light">
    <div class="container">
        <div class="text-center mb-5">
            <h1 class="display-5 fw-bold text-primary"><?php echo htmlspecialchars($t['discount_page_title']); ?></h1>
            <p class="lead text-muted mx-auto" style="max-width: 720px;">
                <?php echo htmlspecialchars($t['discount_page_subtitle']); ?>
            </p>
        </div>
        <div class="row g-4">
            <?php if ($discountQuery && mysqli_num_rows($discountQuery) > 0): ?>
                <?php while ($diskon = mysqli_fetch_assoc($discountQuery)): ?>
                    <div class="col-md-4">
                        <div class="card shadow-sm h-100">
                            <div class="card-body d-flex flex-column">
                                <h5 class="fw-bold text-primary mb-2"><?php echo htmlspecialchars($diskon['nama_paket']); ?></h5>
                                <?php if (!empty($diskon['deskripsi'])): ?>
                                    <p class="text-muted mb-3"><?php echo nl2br(htmlspecialchars($diskon['deskripsi'])); ?></p>
                                <?php endif; ?>
                                <div class="mb-3">
                                    <div class="text-muted small text-uppercase"><?php echo htmlspecialchars($t['discount_original_price']); ?></div>
                                    <div class="fs-5 text-decoration-line-through text-muted">Rp <?php echo number_format($diskon['harga_normal'], 0, ',', '.'); ?></div>
                                </div>
                                <div class="mb-3">
                                    <div class="text-muted small text-uppercase"><?php echo htmlspecialchars($t['discount_price_label']); ?></div>
                                    <div class="display-6 fw-bold text-success">Rp <?php echo number_format($diskon['harga_diskon'], 0, ',', '.'); ?></div>
                                </div>
                                <?php if (!empty($diskon['tanggal_selesai']) || !empty($diskon['tanggal_mulai'])): ?>
                                    <p class="small text-muted mt-auto">
                                        <i class="far fa-clock me-1"></i>
                                        <?php echo htmlspecialchars($t['discount_period']); ?>
                                        <?php
                                        $periodParts = [];
                                        if (!empty($diskon['tanggal_mulai'])) {
                                            $periodParts[] = date('d M Y', strtotime($diskon['tanggal_mulai']));
                                        }
                                        if (!empty($diskon['tanggal_selesai'])) {
                                            $periodParts[] = date('d M Y', strtotime($diskon['tanggal_selesai']));
                                        }
                                        echo htmlspecialchars(implode(' - ', $periodParts));
                                        ?>
                                    </p>
                                <?php endif; ?>
                                <a href="/customer/login.php" class="btn btn-primary mt-3 align-self-start"><?php echo htmlspecialchars($t['discount_action']); ?></a>
                            </div>
                        </div>
                    </div>
                <?php endwhile; ?>
            <?php else: ?>
                <div class="col-12">
                    <div class="alert alert-info text-center shadow-sm"><?php echo htmlspecialchars($t['discount_empty']); ?></div>
                </div>
            <?php endif; ?>
        </div>
    </div>
</section>
<?php require_once __DIR__ . '/../partials/footer.php'; ?>
