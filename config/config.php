<?php
require_once __DIR__ . '/../vendor/midtrans/Midtrans.php';

use Midtrans\Config as MidtransConfig;

$host = 'localhost';
$user = 'root';
$password = '';
$database = 'clousting_db';

$conn = mysqli_connect($host, $user, $password, $database);

if (!$conn) {
    die('Koneksi database gagal: ' . mysqli_connect_error());
}

mysqli_set_charset($conn, 'utf8mb4');

$columnCheck = mysqli_query($conn, "SHOW COLUMNS FROM pesanan LIKE 'nomor_telepon'");
if ($columnCheck) {
    $hasNomorTelepon = mysqli_num_rows($columnCheck) > 0;
    mysqli_free_result($columnCheck);
    if (!$hasNomorTelepon) {
        $alterSql = "ALTER TABLE pesanan ADD COLUMN nomor_telepon VARCHAR(20) NOT NULL DEFAULT '' AFTER domain";
        if (!mysqli_query($conn, $alterSql)) {
            error_log('Gagal memastikan kolom nomor_telepon: ' . mysqli_error($conn));
        }
    }
}

$projectUploadDir = __DIR__ . '/../public/uploads/projects';
if (!is_dir($projectUploadDir)) {
    mkdir($projectUploadDir, 0775, true);
}

define('PROJECT_UPLOAD_DIR', $projectUploadDir);
define('PROJECT_UPLOAD_URI', '/uploads/projects');

$paymentProofDir = __DIR__ . '/../public/uploads/payments';
if (!is_dir($paymentProofDir)) {
    mkdir($paymentProofDir, 0775, true);
}

define('PAYMENT_UPLOAD_DIR', $paymentProofDir);
define('PAYMENT_UPLOAD_URI', '/uploads/payments');

$midtransServerKey = getenv('MIDTRANS_SERVER_KEY') ?: 'SB-Mid-server-yourkey';
$midtransClientKey = getenv('MIDTRANS_CLIENT_KEY') ?: 'SB-Mid-client-yourkey';
$midtransIsProduction = getenv('MIDTRANS_IS_PRODUCTION') ? filter_var(getenv('MIDTRANS_IS_PRODUCTION'), FILTER_VALIDATE_BOOLEAN) : false;

define('MIDTRANS_SERVER_KEY', $midtransServerKey);
define('MIDTRANS_CLIENT_KEY', $midtransClientKey);
define('MIDTRANS_IS_PRODUCTION', $midtransIsProduction);

MidtransConfig::$serverKey = MIDTRANS_SERVER_KEY;
MidtransConfig::$clientKey = MIDTRANS_CLIENT_KEY;
MidtransConfig::$isProduction = MIDTRANS_IS_PRODUCTION;
