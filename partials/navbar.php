<nav class="navbar navbar-expand-lg navbar-light bg-white shadow-sm py-3 border-0">
    <div class="container">
        <a class="navbar-brand" href="/index.php">
            <img src="/assets/media/clousting-logo.svg" alt="Logo ClouSting" class="navbar-logo">
            <span>ClouSting</span>
        </a>
        <button class="navbar-toggler border-0 shadow-none" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse mt-3 mt-lg-0 pt-3 pt-lg-0" id="navbarNav">
            <ul class="navbar-nav w-100 w-lg-auto ms-lg-auto align-items-lg-center gap-2 gap-lg-3">
                <li class="nav-item mx-lg-2"><a class="nav-link" href="/index.php#services">Layanan</a></li>
                <li class="nav-item mx-lg-2"><a class="nav-link" href="/index.php#pricing">Paket</a></li>
                <li class="nav-item mx-lg-2"><a class="nav-link" href="/index.php#promo">Promo</a></li>
                <li class="nav-item mx-lg-2"><a class="nav-link" href="/about.php">Tentang Kami</a></li>
                <li class="nav-item mx-lg-2"><a class="nav-link" href="/contact.php">Kontak</a></li>
                <li class="nav-item dropdown mx-lg-2">
                    <button class="btn btn-primary rounded-pill px-3 dropdown-toggle w-100 w-lg-auto" type="button" id="navbarLoginDropdown" data-bs-toggle="dropdown" aria-expanded="false">
                        Masuk
                    </button>
                    <ul class="dropdown-menu dropdown-menu-end dropdown-menu-lg-start shadow-sm" aria-labelledby="navbarLoginDropdown">
                        <li>
                            <a class="dropdown-item d-flex align-items-center gap-2" href="/customer/login.php">
                                <i class="fas fa-user"></i>
                                Masuk Pelanggan
                            </a>
                        </li>
                        <li>
                            <a class="dropdown-item d-flex align-items-center gap-2" href="/admin/login.php">
                                <i class="fas fa-user-shield"></i>
                                Masuk Admin
                            </a>
                        </li>
                    </ul>
                </li>
            </ul>
        </div>
    </div>
</nav>
<div class="promo-banner text-white" data-deadline="<?php echo htmlspecialchars($promoDeadlineIso); ?>">
    <div class="container d-flex flex-column flex-lg-row justify-content-lg-between align-items-lg-center gap-3 py-2">
        <div class="promo-text fw-semibold text-center text-lg-start">
            <span class="me-1"><?php echo htmlspecialchars($t['promo_headline']); ?></span>
            <span class="opacity-75"><?php echo htmlspecialchars($t['promo_subheadline']); ?></span>
        </div>
        <div class="d-flex flex-column flex-lg-row align-items-center gap-2">
            <div class="d-flex align-items-center gap-2">
                <span class="countdown-label text-uppercase small fw-semibold"><?php echo htmlspecialchars($t['promo_countdown_label']); ?></span>
                <span id="promo-countdown" class="countdown-badge" data-deadline="<?php echo htmlspecialchars($promoDeadlineIso); ?>">--</span>
            </div>
            <?php
            $discountUrl = '/paket-diskon.php';
            if ($currentLang !== 'id') {
                $discountUrl .= '?lang=' . urlencode($currentLang);
            }
            ?>
            <a class="btn btn-warning fw-semibold text-dark" href="<?php echo htmlspecialchars($discountUrl); ?>"><?php echo htmlspecialchars($t['promo_button']); ?></a>
        </div>
    </div>
</div>
