<footer class="footer-main text-white mt-5">
    <div class="container py-5">
        <div class="row g-4 align-items-start">
            <div class="col-lg-4">
                <div class="footer-brand">
                    <img src="/assets/media/clousting-logo.svg" alt="Logo ClouSting">
                    <div>
                        <h5 class="fw-bold mb-1">ClouSting</h5>
                        <p class="mb-0 small text-white-75">Infrastruktur cloud, hosting, dan pengembangan website profesional dengan dukungan 24/7 dan SLA uptime 99.9%.</p>
                    </div>
                </div>
                <div class="d-flex gap-2 flex-wrap">
                    <span class="badge bg-white text-primary fw-semibold">#BuildWithClouSting</span>
                    <span class="badge bg-white text-primary fw-semibold">#TrustedPartner</span>
                </div>
            </div>
            <div class="col-lg-4">
                <h6 class="text-uppercase text-white-50 mb-3">Hubungi Kami</h6>
                <ul class="list-unstyled mb-0 small">
                    <li class="mb-2 d-flex align-items-start gap-2">
                        <i class="fas fa-envelope mt-1"></i>
                        <a href="mailto:clousting.cs@gmail.com" class="text-decoration-none">clousting.cs@gmail.com</a>
                    </li>
                    <li class="mb-2 d-flex align-items-start gap-2">
                        <i class="fas fa-phone mt-1"></i>
                        <a href="tel:+6285175394358" class="text-decoration-none">0851-7539-4358</a>
                    </li>
                    <li class="d-flex align-items-start gap-2">
                        <i class="fas fa-map-marker-alt mt-1"></i>
                        <span>Perum Puri Indah Blok GH No. 38, Purwokerto</span>
                    </li>
                </ul>
            </div>
            <div class="col-lg-4">
                <h6 class="text-uppercase text-white-50 mb-3">Terhubung</h6>
                <div class="social-links d-flex flex-wrap gap-2">
                    <a href="https://www.instagram.com" target="_blank" rel="noopener" aria-label="Instagram ClouSting"><i class="fab fa-instagram"></i></a>
                    <a href="https://www.linkedin.com" target="_blank" rel="noopener" aria-label="LinkedIn ClouSting"><i class="fab fa-linkedin-in"></i></a>
                    <a href="https://www.youtube.com" target="_blank" rel="noopener" aria-label="YouTube ClouSting"><i class="fab fa-youtube"></i></a>
                    <a href="https://wa.me/6285175394358" target="_blank" rel="noopener" aria-label="WhatsApp ClouSting"><i class="fab fa-whatsapp"></i></a>
                </div>
            </div>
        </div>
        <div class="footer-divider"></div>
        <div class="d-flex flex-column flex-lg-row justify-content-between align-items-center gap-3 small text-white-50">
            <span>&copy; <?php echo date('Y'); ?> ClouSting. Seluruh hak cipta dilindungi.</span>
            <div class="d-flex flex-wrap gap-3">
                <a href="/contact.php" class="text-decoration-none">Pusat Bantuan</a>
                <a href="mailto:clousting.cs@gmail.com" class="text-decoration-none">clousting.cs@gmail.com</a>
            </div>
        </div>
    </div>
</footer>
<script>
    document.addEventListener('DOMContentLoaded', function () {
        const countdownElement = document.getElementById('promo-countdown');
        if (countdownElement) {
            const deadlineAttribute = countdownElement.getAttribute('data-deadline');
            const deadline = deadlineAttribute ? new Date(deadlineAttribute) : null;
            if (deadline instanceof Date && !isNaN(deadline)) {
                const updateCountdown = () => {
                    const now = new Date();
                    const diff = deadline.getTime() - now.getTime();
                    if (diff <= 0) {
                        countdownElement.textContent = '00h : 00m : 00s';
                        return;
                    }
                    const totalSeconds = Math.floor(diff / 1000);
                    const days = Math.floor(totalSeconds / 86400);
                    const hours = Math.floor((totalSeconds % 86400) / 3600);
                    const minutes = Math.floor((totalSeconds % 3600) / 60);
                    const seconds = totalSeconds % 60;
                    const totalHours = days * 24 + hours;
                    countdownElement.textContent = `${String(totalHours).padStart(2, '0')}h : ${String(minutes).padStart(2, '0')}m : ${String(seconds).padStart(2, '0')}s`;
                };
                updateCountdown();
                setInterval(updateCountdown, 1000);
            }
        }

        const sidebarMenu = document.getElementById('sidebarMenu');
        if (sidebarMenu && typeof bootstrap !== 'undefined') {
            const getSidebarInstance = () => bootstrap.Offcanvas.getInstance(sidebarMenu) || new bootstrap.Offcanvas(sidebarMenu);

            sidebarMenu.querySelectorAll('[data-bs-dismiss="offcanvas"]').forEach((button) => {
                button.addEventListener('click', () => {
                    getSidebarInstance().hide();
                });
            });
        }
    });
</script>
</body>
</html>
