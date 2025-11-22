<?php
$currentPath = strtok($_SERVER['REQUEST_URI'], '?') ?: '';
$navItems = [
    [
        'href' => '/admin/dashboard.php',
        'icon' => 'fa-chart-line',
        'label' => 'Dashboard',
    ],
    [
        'href' => '/admin/paket.php',
        'icon' => 'fa-box-open',
        'label' => 'Paket Hosting',
    ],
    [
        'href' => '/admin/paket_diskon.php',
        'icon' => 'fa-tags',
        'label' => 'Paket Diskon',
    ],
    [
        'href' => '/admin/pelanggan.php',
        'icon' => 'fa-users',
        'label' => 'Pelanggan',
    ],
    [
        'href' => '/admin/pesanan.php',
        'icon' => 'fa-shopping-cart',
        'label' => 'Pesanan',
    ],
    [
        'href' => '/admin/pembayaran.php',
        'icon' => 'fa-receipt',
        'label' => 'Pembayaran',
    ],
    [
        'href' => '/admin/promo.php',
        'icon' => 'fa-bullhorn',
        'label' => 'Promo & Diskon',
    ],
];
?>
<nav id="sidebarMenu" class="admin-sidebar offcanvas offcanvas-start offcanvas-lg text-white" tabindex="-1" aria-labelledby="sidebarMenuLabel">
    <div class="offcanvas-header d-lg-none">
        <h5 class="offcanvas-title fw-semibold" id="sidebarMenuLabel">ClouSting Admin</h5>
        <button
            type="button"
            class="btn-close btn-close-white"
            data-bs-dismiss="offcanvas"
            data-bs-target="#sidebarMenu"
            aria-label="Tutup"
        ></button>
    </div>
    <div class="offcanvas-body d-flex flex-column p-3">
        <a href="/admin/dashboard.php" class="sidebar-brand d-none d-lg-flex align-items-center text-white text-decoration-none mb-3">
            <span class="fs-4 fw-semibold">ClouSting Admin</span>
        </a>
        <div class="border-top border-light border-opacity-25 mb-3 d-none d-lg-block"></div>
        <ul class="nav nav-pills flex-column gap-1 mb-auto">
            <?php foreach ($navItems as $item): ?>
                <?php $isActive = strpos($currentPath, $item['href']) === 0; ?>
                <li class="nav-item">
                    <a href="<?php echo $item['href']; ?>" class="nav-link d-flex align-items-center gap-2 <?php echo $isActive ? 'active' : ''; ?>">
                        <i class="fas <?php echo $item['icon']; ?>"></i>
                        <span><?php echo $item['label']; ?></span>
                    </a>
                </li>
            <?php endforeach; ?>
        </ul>
        <div class="pt-3 mt-4 border-top border-light border-opacity-25">
            <span class="small d-block">Masuk sebagai: <?php echo $_SESSION['admin_name'] ?? 'Admin'; ?></span>
            <a class="btn btn-outline-light btn-sm mt-2 w-100" href="/admin/logout.php"><i class="fas fa-sign-out-alt me-2"></i>Keluar</a>
        </div>
    </div>
</nav>
