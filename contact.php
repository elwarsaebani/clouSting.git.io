<?php
require_once __DIR__ . '/../partials/header.php';
require_once __DIR__ . '/../partials/navbar.php';
?>
<section class="py-5">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-lg-8">
                <h1 class="section-title text-center">Hubungi Kami</h1>
                <p class="text-center text-muted mb-5">Tim kami siap membantu Anda kapanpun. Jangan ragu untuk menghubungi ClouSting melalui form berikut.</p>
                <div class="card border-0 shadow-lg">
                    <div class="card-body p-5">
                        <form>
                            <div class="mb-3">
                                <label class="form-label">Nama Lengkap</label>
                                <input type="text" class="form-control" placeholder="Nama Anda" required>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Email</label>
                                <input type="email" class="form-control" placeholder="email@domain.com" required>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Pesan</label>
                                <textarea class="form-control" rows="5" placeholder="Sampaikan kebutuhan Anda"></textarea>
                            </div>
                            <button type="submit" class="btn btn-primary">Kirim Pesan</button>
                        </form>
                    </div>
                </div>
                <div class="row mt-4 text-center">
                    <div class="col-md-4">
                        <i class="fas fa-map-marker-alt text-primary mb-2"></i>
                        <p>Jakarta, Indonesia</p>
                    </div>
                    <div class="col-md-4">
                        <i class="fas fa-envelope text-primary mb-2"></i>
                        <p>clousting.cs@gmail.com</p>
                    </div>
                    <div class="col-md-4">
                        <i class="fas fa-phone text-primary mb-2"></i>
                        <p>0851-7539-4358</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>
<?php require_once __DIR__ . '/../partials/footer.php'; ?>
