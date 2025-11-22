<?php
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../partials/header.php';
require_once __DIR__ . '/../partials/navbar.php';

$activePromo = null;
$promoSql = "SELECT p.*, b.nama AS bundling_nama FROM promos p LEFT JOIN bundling_packages b ON p.bundling_id = b.id WHERE CURDATE() BETWEEN p.tanggal_mulai AND p.tanggal_berakhir ORDER BY p.tanggal_mulai ASC LIMIT 1";
$promoResult = mysqli_query($conn, $promoSql);
if ($promoResult && mysqli_num_rows($promoResult) > 0) {
    $activePromo = mysqli_fetch_assoc($promoResult);
    mysqli_free_result($promoResult);
} else {
    $upcomingPromoSql = "SELECT p.*, b.nama AS bundling_nama FROM promos p LEFT JOIN bundling_packages b ON p.bundling_id = b.id WHERE p.tanggal_mulai >= CURDATE() ORDER BY p.tanggal_mulai ASC LIMIT 1";
    $upcomingResult = mysqli_query($conn, $upcomingPromoSql);
    if ($upcomingResult && mysqli_num_rows($upcomingResult) > 0) {
        $activePromo = mysqli_fetch_assoc($upcomingResult);
        mysqli_free_result($upcomingResult);
    }
}

$promoStart = $activePromo ? new DateTime($activePromo['tanggal_mulai']) : null;
$promoEnd = $activePromo ? new DateTime($activePromo['tanggal_berakhir']) : null;

$bundlingPackages = [];
$bundlingQuery = mysqli_query($conn, "SELECT * FROM bundling_packages ORDER BY FIELD(slug, 'company-profile', 'personal-website', 'custom'), nama ASC");
if ($bundlingQuery) {
    while ($row = mysqli_fetch_assoc($bundlingQuery)) {
        $row['references'] = [];
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
        $bundlingPackages[] = $row;
    }
    mysqli_free_result($bundlingQuery);
}

$referenceShowcase = [
    [
        'title' => 'Website Company 1',
        'label' => 'Company Profile',
        'description' => 'Cuplikan halaman muka dengan hero bergambar layanan utama dan tombol ajakan bertindak yang jelas.',
        'media' => 'https://elwass.github.io/companyprofile0.git.io/',
        'url' => 'https://elwass.github.io/companyprofile0.git.io/',
        'type' => 'iframe',
    ],
    [
        'title' => 'Website Company 2',
        'label' => 'Company Profile',
        'description' => 'Menampilkan struktur awal website dengan sorotan layanan, statistik, dan testimoni pelanggan.',
        'media' => 'https://elwass.github.io/companyprofile1.git.io/',
        'url' => 'https://elwass.github.io/companyprofile1.git.io/',
        'type' => 'iframe',
    ],
    [
        'title' => 'Website Company 3',
        'label' => 'Company Profile',
        'description' => 'Halaman depan modern dengan kombinasi hero teks, CTA, dan highlight layanan perusahaan.',
        'media' => 'https://elwass.github.io/companyprofile3.git.io/',
        'url' => 'https://elwass.github.io/companyprofile3.git.io/',
        'type' => 'iframe',
    ],
];
?>
<section class="hero-section text-center">
    <div class="container">
        <div class="row align-items-center">
            <div class="col-lg-7 text-lg-start text-center mb-5 mb-lg-0">
                <div class="hero-badge mb-3">
                    <i class="fas fa-bolt"></i>
                    Infrastruktur super cepat & aman
                </div>
                <h1 class="display-5 fw-bold">Bangun Ekosistem Digital Andal untuk Bisnis Anda</h1>
                <p class="lead mt-4 mb-4">ClouSting menghadirkan performa tinggi, keamanan berlapis, monitoring real-time, dan uptime 99.9% untuk setiap website, aplikasi, maupun solusi custom yang Anda butuhkan.</p>
                <div class="d-flex flex-wrap gap-3">
                    <a href="/customer/register.php" class="btn btn-primary btn-lg rounded-pill">Mulai Sekarang</a>
                </div>
            </div>
            <div class="col-lg-5 mt-5 mt-lg-0">
                <div class="hero-visual">
                    <img src="https://images.unsplash.com/photo-1521737604893-d14cc237f11d?auto=format&fit=crop&w=960&q=80" class="img-fluid" alt="Kolaborasi tim digital ClouSting">
                </div>
            </div>
        </div>
    </div>
</section>
<?php if ($activePromo): ?>
<section class="py-5" id="promo">
    <div class="container">
        <div class="promo-card shadow-lg">
            <div class="row align-items-center">
                <div class="col-lg-7">
                    <h3 class="fw-bold mb-2"><?php echo htmlspecialchars($activePromo['nama']); ?></h3>
                    <p class="mb-3 lead">Diskon <?php echo (int)($activePromo['diskon_persen'] ?? 0); ?>% untuk <?php echo htmlspecialchars($activePromo['bundling_nama'] ?? 'paket pilihan ClouSting'); ?>.</p>
                    <?php if (!empty($activePromo['deskripsi'])): ?>
                        <p class="mb-3 text-white-50"><?php echo htmlspecialchars($activePromo['deskripsi']); ?></p>
                    <?php endif; ?>
                    <p class="mb-0 small">Periode promo: <?php echo $promoStart ? $promoStart->format('d M Y') : '-'; ?> &mdash; <?php echo $promoEnd ? $promoEnd->format('d M Y') : '-'; ?></p>
                </div>
                <div class="col-lg-5">
                    <p id="countdown-status" class="text-uppercase small fw-semibold text-white-50 mb-2"></p>
                    <div id="promo-countdown" class="countdown-grid" data-start="<?php echo $promoStart ? $promoStart->format(DateTime::ATOM) : ''; ?>" data-end="<?php echo $promoEnd ? $promoEnd->format(DateTime::ATOM) : ''; ?>">
                        <div class="countdown-box">
                            <h3 id="countdown-days">0</h3>
                            <div class="countdown-label">Hari</div>
                        </div>
                        <div class="countdown-box">
                            <h3 id="countdown-hours">0</h3>
                            <div class="countdown-label">Jam</div>
                        </div>
                        <div class="countdown-box">
                            <h3 id="countdown-minutes">0</h3>
                            <div class="countdown-label">Menit</div>
                        </div>
                        <div class="countdown-box">
                            <h3 id="countdown-seconds">0</h3>
                            <div class="countdown-label">Detik</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>
<?php endif; ?>

<section class="py-5" id="services">
    <div class="container">
        <div class="text-center mb-5">
            <h2 class="section-title"><?php echo htmlspecialchars($t['services_title']); ?></h2>
            <p class="text-muted"><?php echo htmlspecialchars($t['services_subtitle']); ?></p>
        </div>
        <div class="row g-4">
            <div class="col-md-4">
                <div class="card card-feature p-4 h-100">
                    <div class="icon mb-3 text-primary"><i class="fas fa-tachometer-alt fa-2x"></i></div>
                    <h5 class="fw-semibold"><?php echo htmlspecialchars($t['feature_1_title']); ?></h5>
                    <p><?php echo htmlspecialchars($t['feature_1_desc']); ?></p>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card card-feature p-4 h-100">
                    <div class="icon mb-3 text-primary"><i class="fas fa-shield-alt fa-2x"></i></div>
                    <h5 class="fw-semibold"><?php echo htmlspecialchars($t['feature_2_title']); ?></h5>
                    <p><?php echo htmlspecialchars($t['feature_2_desc']); ?></p>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card card-feature p-4 h-100">
                    <div class="icon mb-3 text-primary"><i class="fas fa-headset fa-2x"></i></div>
                    <h5 class="fw-semibold"><?php echo htmlspecialchars($t['feature_3_title']); ?></h5>
                    <p><?php echo htmlspecialchars($t['feature_3_desc']); ?></p>
                </div>
            </div>
        </div>
    </div>
</section>
<section class="py-5 bg-white" id="pricing">
    <div class="container">
        <div class="text-center mb-5">
            <h2 class="section-title"><?php echo htmlspecialchars($t['pricing_title']); ?></h2>
            <p class="text-muted"><?php echo htmlspecialchars($t['pricing_subtitle']); ?></p>
        </div>
        <div class="row g-4">
            <?php
            require_once __DIR__ . '/../config/config.php';
            $packages = mysqli_query($conn, "SELECT * FROM paket_hosting ORDER BY harga ASC");
            while ($paket = mysqli_fetch_assoc($packages)):
            ?>
            <div class="col-md-4">
                <div class="price-card <?php echo $paket['nama_paket'] === 'Business' ? 'featured' : ''; ?> text-center h-100">
                    <h4 class="fw-bold text-primary"><?php echo htmlspecialchars($paket['nama_paket']); ?></h4>
                    <p class="display-6 fw-bold mb-3">Rp <?php echo number_format($paket['harga'], 0, ',', '.'); ?>/bln</p>
                    <p class="text-muted"><?php echo htmlspecialchars($paket['deskripsi']); ?></p>
                    <ul class="list-unstyled text-start mt-3 mb-4">
                        <?php foreach (explode("\n", $paket['fitur']) as $fitur): ?>
                            <li class="mb-2"><i class="fas fa-check text-success me-2"></i><?php echo htmlspecialchars($fitur); ?></li>
                        <?php endforeach; ?>
                    </ul>
                    <a href="/customer/login.php" class="btn btn-primary"><?php echo htmlspecialchars($t['pricing_cta']); ?></a>
                </div>
            </div>
            <?php endwhile; ?>
        </div>
    </div>
</section>
<section class="py-5 bg-light" id="bundling">
    <div class="container">
        <div class="row g-4 align-items-center mb-4">
            <div class="col-lg-7">
                <h2 class="section-title">Paket Bundling Website</h2>
                <p class="text-muted">Paket bundling kami hadir dalam dua opsi siap pakai (company profile &amp; personal website) serta jalur custom untuk kebutuhan unik. Semua sudah termasuk domain, hosting, dan sesi konsultasi desain.</p>
                <ul class="list-unstyled text-muted small mb-0">
                    <li class="mb-1"><i class="fas fa-check text-primary me-2"></i>Estimasi live kurang dari 1 Jam</li>
                    <li class="mb-1"><i class="fas fa-check text-primary me-2"></i>Optimasi SEO dasar &amp; integrasi WhatsApp</li>
                    <li><i class="fas fa-check wtext-primary me-2"></i>Free update minor selama 30 hari setelah go-live</li>
                </ul>
            </div>
            <div class="col-lg-5">
                <div class="bundling-highlight text-center text-lg-start">
                    <span class="text-uppercase small fw-semibold">Harga Spesial</span>
                    <div class="bundle-price">Rp 190K</div>
                    <p class="mb-3">Setara Rp <?php echo number_format(190000, 0, ',', '.'); ?> untuk website profesional lengkap dengan domain &amp; hosting 1 tahun.</p>
                    <div class="d-flex flex-wrap justify-content-center justify-content-lg-start gap-2">
                        <a href="/customer/pesanan_baru.php#section-bundling" class="btn btn-primary rounded-pill">Pesan Sekarang</a>
                        <a href="/customer/pesanan_baru.php" class="btn btn-outline-primary rounded-pill">Detail Pemesanan</a>
                    </div>
                </div>
            </div>
        </div>
        <?php
        $companyPersonalPackages = array_filter($bundlingPackages, function ($bundle) {
            return in_array($bundle['slug'], ['company-profile', 'personal-website'], true);
        });
        $customPackage = null;
        foreach ($bundlingPackages as $bundle) {
            if ($bundle['slug'] === 'custom') {
                $customPackage = $bundle;
                break;
            }
        }
        ?>
        <div class="row g-4 align-items-stretch">
            <?php if (!empty($companyPersonalPackages)): ?>
                <?php foreach ($companyPersonalPackages as $variant): ?>
                    <div class="col-lg-4 col-md-6">
                        <div class="bundling-card h-100">
                            <span class="badge mb-3">Website Siap Pakai</span>
                            <h4 class="fw-bold mb-2"><?php echo htmlspecialchars($variant['nama']); ?></h4>
                            <p class="text-muted mb-3"><?php echo htmlspecialchars($variant['deskripsi']); ?></p>
                            <div class="d-flex align-items-baseline justify-content-between mb-3">
                                <span class="fw-bold fs-5 text-primary">Rp <?php echo number_format($variant['harga'], 0, ',', '.'); ?></span>
                                <a href="/customer/pesanan_baru.php#section-bundling" class="btn btn-sm btn-primary rounded-pill">Pilih Paket</a>
                            </div>
                            <?php if (!empty($variant['highlight'])): ?>
                                <ul class="small text-muted ps-3 mb-3">
                                    <?php foreach (explode('\n', $variant['highlight']) as $point): ?>
                                        <?php $trimmedPoint = trim($point); if ($trimmedPoint === '') { continue; } ?>
                                        <li><?php echo htmlspecialchars($trimmedPoint); ?></li>
                                    <?php endforeach; ?>
                                </ul>
                            <?php endif; ?>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <div class="col-12">
                    <div class="alert alert-info mb-0">Paket bundling siap pakai akan segera hadir. Hubungi kami untuk daftar tunggu.</div>
                </div>
            <?php endif; ?>
            <div class="col-lg-4 col-md-6">
                <div class="bundling-card h-100 border-dashed">
                    <span class="badge mb-3">Custom Project</span>
                    <?php if ($customPackage): ?>
                        <h4 class="fw-bold mb-2">Diskusikan Kebutuhan Anda</h4>
                        <p class="text-muted mb-3"><?php echo htmlspecialchars($customPackage['deskripsi']); ?></p>
                        <?php if (!empty($customPackage['highlight'])): ?>
                            <ul class="small text-muted ps-3 mb-3">
                                <?php foreach (explode('\n', $customPackage['highlight']) as $point): ?>
                                    <?php $trimmedPoint = trim($point); if ($trimmedPoint === '') { continue; } ?>
                                    <li><?php echo htmlspecialchars($trimmedPoint); ?></li>
                                <?php endforeach; ?>
                            </ul>
                        <?php endif; ?>
                        <a href="/customer/pesanan_baru.php#section-custom" class="btn btn-outline-primary w-100">Konsultasi Paket Custom</a>
                    <?php else: ?>
                        <p class="text-muted">Masih belum ada paket custom aktif. Hubungi tim kami untuk merancang solusi khusus.</p>
                        <a href="https://wa.me/6285175394358" class="btn btn-outline-primary w-100" target="_blank" rel="noopener">Hubungi Kami</a>
                    <?php endif; ?>
                </div>
            </div>
        </div>
        <div class="mt-5">
            <div class="d-flex justify-content-between align-items-center mb-3 flex-wrap gap-2">
                <h3 class="fw-semibold mb-0">Referensi Desain Pilihan</h3>
                <span class="text-muted small">Klik kartu untuk melihat tampilan lengkap atau video demo.</span>
            </div>
            <div class="reference-gallery">
                <?php foreach ($referenceShowcase as $reference): ?>
                    <div class="reference-card <?php echo htmlspecialchars($reference['type']); ?>">
                        <?php
                        $posterStyle = '';
                        if (!empty($reference['poster'])) {
                            $posterStyle = ' style="background-image: url(' . htmlspecialchars($reference['poster'], ENT_QUOTES) . ');"';
                        }
                        ?>
                        <div class="media-wrapper<?php echo $reference['type'] === 'video' ? ' video-wrapper' : ''; ?>"<?php echo $posterStyle; ?>>
                            <?php if ($reference['type'] === 'video' && !empty($reference['video_embed'])): ?>
                                <iframe src="<?php echo htmlspecialchars($reference['video_embed']); ?>" title="<?php echo htmlspecialchars($reference['title']); ?>" allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture" allowfullscreen loading="lazy"></iframe>
                            <?php elseif ($reference['type'] === 'iframe' && !empty($reference['media'])): ?>
                                <iframe src="<?php echo htmlspecialchars($reference['media']); ?>" title="Pratinjau <?php echo htmlspecialchars($reference['title']); ?>" loading="lazy" referrerpolicy="no-referrer" sandbox="allow-scripts allow-same-origin"></iframe>
                            <?php elseif (!empty($reference['media'])): ?>
                                <img src="<?php echo htmlspecialchars($reference['media']); ?>" alt="<?php echo htmlspecialchars($reference['title']); ?>">
                            <?php endif; ?>
                        </div>
                        <div class="reference-content">
                            <span class="reference-label"><?php echo htmlspecialchars($reference['label']); ?></span>
                            <h5 class="reference-title"><?php echo htmlspecialchars($reference['title']); ?></h5>
                            <?php if (!empty($reference['description'])): ?>
                                <p class="reference-description mb-3"><?php echo htmlspecialchars($reference['description']); ?></p>
                            <?php endif; ?>
                            <?php if (!empty($reference['url'])): ?>
                                <a class="btn btn-sm btn-light" href="<?php echo htmlspecialchars($reference['url']); ?>" target="_blank" rel="noopener">
                                    Lihat Referensi <i class="fas fa-arrow-up-right-from-square ms-1"></i>
                                </a>
                            <?php endif; ?>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    </div>
</section>
<section class="py-5 bg-light" id="about">
    <div class="container">
        <div class="row align-items-center">
            <div class="col-lg-6">
                <h2 class="section-title"><?php echo htmlspecialchars($t['about_title']); ?></h2>
                <p><?php echo htmlspecialchars($t['about_paragraph_1']); ?></p>
                <p><?php echo htmlspecialchars($t['about_paragraph_2']); ?></p>
            </div>
            <div class="col-lg-6 mt-4 mt-lg-0">
                <img src="https://images.unsplash.com/photo-1556740749-887f6717d7e4?auto=format&fit=crop&w=960&q=80" class="img-fluid rounded-4 shadow" alt="Tim data center dan support ClouSting">
            </div>
        </div>
    </div>
</section>
<section class="py-5" id="cta">
    <div class="container text-center">
        <div class="bg-primary text-white p-5 rounded-4 shadow-lg">
            <h3 class="fw-bold"><?php echo htmlspecialchars($t['cta_title']); ?></h3>
            <p class="mb-4"><?php echo htmlspecialchars($t['cta_paragraph']); ?></p>
            <a href="/customer/register.php" class="btn btn-light btn-lg text-primary fw-semibold"><?php echo htmlspecialchars($t['cta_button']); ?></a>
        </div>
    </div>
</section>
<script>
    (function () {
        const countdownEl = document.getElementById('promo-countdown');
        if (!countdownEl) {
            return;
        }

        const start = countdownEl.dataset.start ? new Date(countdownEl.dataset.start) : null;
        const end = countdownEl.dataset.end ? new Date(countdownEl.dataset.end) : null;
        if (!end || Number.isNaN(end.getTime())) {
            return;
        }

        const dayEl = document.getElementById('countdown-days');
        const hourEl = document.getElementById('countdown-hours');
        const minuteEl = document.getElementById('countdown-minutes');
        const secondEl = document.getElementById('countdown-seconds');
        const statusEl = document.getElementById('countdown-status');

        function updateCountdown() {
            const now = new Date();
            if (start && now < start) {
                const diff = start - now;
                render(diff);
                if (statusEl) {
                    statusEl.textContent = 'Promo dimulai dalam';
                }
                return;
            }
            const diff = end - now;
            if (diff <= 0) {
                dayEl.textContent = '0';
                hourEl.textContent = '0';
                minuteEl.textContent = '0';
                secondEl.textContent = '0';
                if (statusEl) {
                    statusEl.textContent = 'Promo telah berakhir';
                }
                return;
            }
            if (statusEl) {
                statusEl.textContent = 'Promo berakhir dalam';
            }
            render(diff);
        }

        function render(ms) {
            const totalSeconds = Math.floor(ms / 1000);
            const days = Math.floor(totalSeconds / 86400);
            const hours = Math.floor((totalSeconds % 86400) / 3600);
            const minutes = Math.floor((totalSeconds % 3600) / 60);
            const seconds = totalSeconds % 60;
            dayEl.textContent = String(days);
            hourEl.textContent = String(hours).padStart(2, '0');
            minuteEl.textContent = String(minutes).padStart(2, '0');
            secondEl.textContent = String(seconds).padStart(2, '0');
        }

        updateCountdown();
        setInterval(updateCountdown, 1000);
    })();
</script>
<?php require_once __DIR__ . '/../partials/footer.php'; ?>
