<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$languages = [
    'id' => [
        'language_name' => 'Bahasa Indonesia',
        'nav_services' => 'Layanan',
        'nav_packages' => 'Paket',
        'nav_about' => 'Tentang Kami',
        'nav_contact' => 'Kontak',
        'nav_customer_login' => 'Login Customer',
        'nav_admin_login' => 'Login Admin',
        'hero_title' => 'Solusi Cloud & Hosting Tercepat untuk Bisnis Modern',
        'hero_subtitle' => 'ClouSting menghadirkan performa tinggi, keamanan berlapis, dan uptime 99.9% agar website bisnis Anda selalu online.',
        'hero_cta' => 'Mulai Sekarang',
        'services_title' => 'Mengapa Memilih ClouSting?',
        'services_subtitle' => 'Kami menghadirkan layanan terbaik dengan teknologi terkini dan dukungan pelanggan 24/7.',
        'feature_1_title' => 'Performa Tinggi',
        'feature_1_desc' => 'Server kami dirancang untuk kecepatan maksimum dengan resource yang scalable.',
        'feature_2_title' => 'Keamanan Premium',
        'feature_2_desc' => 'Sertifikat SSL gratis, proteksi DDoS, dan backup harian menjaga data Anda tetap aman.',
        'feature_3_title' => 'Support 24/7',
        'feature_3_desc' => 'Tim support siap membantu kapanpun melalui live chat, email, dan telepon.',
        'pricing_title' => 'Paket Hosting',
        'pricing_subtitle' => 'Pilih paket sesuai kebutuhan website Anda.',
        'pricing_cta' => 'Pesan Sekarang',
        'about_title' => 'Tentang ClouSting',
        'about_paragraph_1' => 'ClouSting merupakan penyedia layanan cloud & hosting yang didirikan oleh para profesional IT dengan pengalaman lebih dari 10 tahun. Kami berkomitmen memberikan solusi infrastruktur yang handal, aman, dan mudah digunakan.',
        'about_paragraph_2' => 'Dengan data center di Jakarta dan Singapore, ClouSting memberikan performa optimal bagi pelanggan di seluruh Asia Tenggara.',
        'cta_title' => 'Siap meningkatkan performa website Anda?',
        'cta_paragraph' => 'Daftar sekarang dan nikmati diskon 30% untuk bulan pertama.',
        'cta_button' => 'Daftar Gratis',
        'promo_headline' => 'Black Friday! Gratis Migrasi & Domain.',
        'promo_subheadline' => 'Diskon 60% untuk semua paket tahunan.',
        'promo_countdown_label' => 'Berakhir dalam',
        'promo_button' => 'Lihat Paket Diskon',
        'modal_title' => 'Bingung pilih paket?',
        'modal_description' => 'Hubungi Sales Assistant kami untuk rekomendasi paket sesuai kebutuhan bisnis Anda.',
        'modal_contact' => '+62 851-7539-4358',
        'modal_cta' => 'Jadwalkan Konsultasi',
        'modal_note' => 'Tim kami siap membantu 24/7.',
        'modal_bonus' => 'Extra 10% off untuk konsultasi hari ini!',
        'discount_page_title' => 'Paket Diskon Spesial',
        'discount_page_subtitle' => 'Jangan lewatkan penawaran terbatas dengan harga terbaik untuk performa maksimal.',
        'discount_original_price' => 'Harga Normal',
        'discount_price_label' => 'Harga Diskon',
        'discount_action' => 'Pesan Paket Ini',
        'discount_period' => 'Berlaku sampai',
        'discount_empty' => 'Belum ada paket diskon aktif untuk saat ini.'
    ],
    'en' => [
        'language_name' => 'English',
        'nav_services' => 'Services',
        'nav_packages' => 'Plans',
        'nav_about' => 'About',
        'nav_contact' => 'Contact',
        'nav_customer_login' => 'Customer Login',
        'nav_admin_login' => 'Admin Login',
        'hero_title' => 'Lightning-fast Cloud & Hosting for Modern Business',
        'hero_subtitle' => 'ClouSting delivers high performance, layered security, and 99.9% uptime so your website stays online.',
        'hero_cta' => 'Get Started',
        'services_title' => 'Why Choose ClouSting?',
        'services_subtitle' => 'We combine the latest technology with 24/7 support for a seamless hosting experience.',
        'feature_1_title' => 'High Performance',
        'feature_1_desc' => 'Our servers are tuned for maximum speed with scalable resources on demand.',
        'feature_2_title' => 'Premium Security',
        'feature_2_desc' => 'Free SSL certificates, DDoS protection, and daily backups keep your data safe.',
        'feature_3_title' => '24/7 Support',
        'feature_3_desc' => 'Our specialists are available anytime via live chat, email, or phone.',
        'pricing_title' => 'Hosting Plans',
        'pricing_subtitle' => 'Pick the perfect plan for your project.',
        'pricing_cta' => 'Order Now',
        'about_title' => 'About ClouSting',
        'about_paragraph_1' => 'ClouSting was founded by IT professionals with over a decade of experience delivering reliable, secure, and easy-to-use infrastructure.',
        'about_paragraph_2' => 'With data centers in Jakarta and Singapore, we provide optimal performance across Southeast Asia.',
        'cta_title' => 'Ready to boost your website performance?',
        'cta_paragraph' => 'Sign up today and enjoy 30% off for the first month.',
        'cta_button' => 'Start for Free',
        'promo_headline' => 'Black Friday! Free Migration & Domain.',
        'promo_subheadline' => 'Save 60% on every annual plan.',
        'promo_countdown_label' => 'Ends in',
        'promo_button' => 'View Discount Plans',
        'modal_title' => 'Need help choosing?',
        'modal_description' => 'Talk with our Sales Assistant for a tailored hosting recommendation.',
        'modal_contact' => '+62 851-7539-4358',
        'modal_cta' => 'Talk to Sales',
        'modal_note' => 'Our experts are on call 24/7.',
        'modal_bonus' => 'Extra 10% off when you consult today!',
        'discount_page_title' => 'Limited Discount Plans',
        'discount_page_subtitle' => 'Grab our limited-time offers to unlock peak performance for less.',
        'discount_original_price' => 'Regular Price',
        'discount_price_label' => 'Promo Price',
        'discount_action' => 'Order This Plan',
        'discount_period' => 'Valid until',
        'discount_empty' => 'No active discount plans at the moment.'
    ],
    'nl' => [
        'language_name' => 'Nederlands',
        'nav_services' => 'Diensten',
        'nav_packages' => 'Pakketten',
        'nav_about' => 'Over ons',
        'nav_contact' => 'Contact',
        'nav_customer_login' => 'Login Klant',
        'nav_admin_login' => 'Login Admin',
        'hero_title' => 'Razendsnelle Cloud & Hosting voor moderne bedrijven',
        'hero_subtitle' => 'ClouSting levert hoge prestaties, gelaagde beveiliging en 99,9% uptime zodat uw website altijd online blijft.',
        'hero_cta' => 'Begin nu',
        'services_title' => 'Waarom kiezen voor ClouSting?',
        'services_subtitle' => 'Wij combineren de nieuwste technologie met 24/7 ondersteuning voor een zorgeloze hostingervaring.',
        'feature_1_title' => 'Hoge prestaties',
        'feature_1_desc' => 'Onze servers zijn geoptimaliseerd voor maximale snelheid met schaalbare resources.',
        'feature_2_title' => 'Premium beveiliging',
        'feature_2_desc' => 'Gratis SSL-certificaten, DDoS-bescherming en dagelijkse back-ups houden uw data veilig.',
        'feature_3_title' => 'Support 24/7',
        'feature_3_desc' => 'Ons supportteam is altijd bereikbaar via live chat, e-mail en telefoon.',
        'pricing_title' => 'Hostingpakketten',
        'pricing_subtitle' => 'Kies het pakket dat bij uw website past.',
        'pricing_cta' => 'Bestel nu',
        'about_title' => 'Over ClouSting',
        'about_paragraph_1' => 'ClouSting is opgericht door IT-professionals met meer dan 10 jaar ervaring en levert betrouwbare, veilige en gebruiksvriendelijke infrastructuur.',
        'about_paragraph_2' => 'Met datacenters in Jakarta en Singapore bieden wij optimale prestaties in heel Zuidoost-Azië.',
        'cta_title' => 'Klaar om de prestaties van uw website te verhogen?',
        'cta_paragraph' => 'Meld u vandaag aan en ontvang 30% korting in de eerste maand.',
        'cta_button' => 'Gratis starten',
        'promo_headline' => 'Black Friday! Gratis migratie & domein.',
        'promo_subheadline' => '60% korting op alle jaarplannen.',
        'promo_countdown_label' => 'Eindigt over',
        'promo_button' => 'Bekijk kortingspakketten',
        'modal_title' => 'Twijfelt u over een pakket?',
        'modal_description' => 'Neem contact op met onze sales assistent voor een hostingadvies op maat.',
        'modal_contact' => '+62 851-7539-4358',
        'modal_cta' => 'Plan een gesprek',
        'modal_note' => 'Ons team staat 24/7 voor u klaar.',
        'modal_bonus' => 'Extra 10% korting bij een adviesgesprek vandaag!',
        'discount_page_title' => 'Speciale Kortingspakketten',
        'discount_page_subtitle' => 'Profiteer van onze tijdelijke aanbiedingen voor maximale prestaties.',
        'discount_original_price' => 'Normale prijs',
        'discount_price_label' => 'Actieprijs',
        'discount_action' => 'Bestel dit pakket',
        'discount_period' => 'Geldig tot',
        'discount_empty' => 'Momenteel zijn er geen actieve kortingspakketten.'
    ],
];

if (isset($_GET['lang']) && array_key_exists($_GET['lang'], $languages)) {
    $_SESSION['lang'] = $_GET['lang'];
}

$currentLang = $_SESSION['lang'] ?? 'id';
if (!array_key_exists($currentLang, $languages)) {
    $currentLang = 'id';
}

$t = $languages[$currentLang];
$languageOptions = [];
foreach ($languages as $code => $data) {
    $languageOptions[$code] = $data['language_name'];
}
$currentPath = strtok($_SERVER['REQUEST_URI'], '?') ?: '/';
$promoDeadlineIso = (new DateTimeImmutable('+3 days'))->format('c');
?>
<!DOCTYPE html>
<html lang="<?php echo htmlspecialchars($currentLang); ?>">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ClouSting</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
    <link rel="stylesheet" href="/assets/css/style.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css" integrity="sha512-DTOQO9RWCH3ppGqcWaEA1BIZOC6xxalwEsw9c2QQeAIftl+Vegovlnee1c9QX4TctnWMn13TZye+giMm8e2LwA==" crossorigin="anonymous" referrerpolicy="no-referrer" />
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js" integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz" crossorigin="anonymous" defer></script>
</head>
<body style="font-family: 'Poppins', sans-serif;">
