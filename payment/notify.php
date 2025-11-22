<?php
require_once __DIR__ . '/../../config/config.php';

use Midtrans\Notification;
use Midtrans\Signature;
use Midtrans\MidtransException;

header('Content-Type: application/json');

try {
    $rawInput = file_get_contents('php://input');
    if ($rawInput === false || $rawInput === '') {
        throw new Exception('Payload kosong.');
    }

    $notification = new Notification($rawInput);
    $payload = $notification->getPayload();
} catch (MidtransException $e) {
    http_response_code(400);
    echo json_encode(['message' => $e->getMessage()]);
    exit;
} catch (Exception $e) {
    http_response_code(400);
    echo json_encode(['message' => $e->getMessage()]);
    exit;
}

$orderId = $payload['order_id'] ?? '';
$statusCode = $payload['status_code'] ?? '';
$grossAmount = $payload['gross_amount'] ?? '0';
$signatureKey = $payload['signature_key'] ?? '';

if ($orderId === '' || $signatureKey === '') {
    http_response_code(400);
    echo json_encode(['message' => 'Data notifikasi tidak lengkap.']);
    exit;
}

if (!Signature::validate($signatureKey, $orderId, $statusCode, $grossAmount, MIDTRANS_SERVER_KEY)) {
    http_response_code(403);
    echo json_encode(['message' => 'Signature tidak valid.']);
    exit;
}

$paymentType = $payload['payment_type'] ?? '';
$transactionStatus = $payload['transaction_status'] ?? 'pending';
$fraudStatus = $payload['fraud_status'] ?? '';

$orderIdEscaped = mysqli_real_escape_string($conn, $orderId);
$transResult = mysqli_query($conn, "SELECT * FROM transaksi WHERE order_id = '$orderIdEscaped' LIMIT 1");
if (!$transResult || mysqli_num_rows($transResult) === 0) {
    http_response_code(404);
    echo json_encode(['message' => 'Transaksi tidak ditemukan.']);
    exit;
}

$transaction = mysqli_fetch_assoc($transResult);
$pesananId = (int)$transaction['pesanan_id'];

$grossAmountValue = is_numeric($grossAmount) ? (float)$grossAmount : 0;
$updateStmt = mysqli_prepare($conn, "UPDATE transaksi SET payment_type = ?, transaction_status = ?, gross_amount = ?, transaction_time = NOW() WHERE order_id = ?");
if ($updateStmt) {
    $paymentTypeValue = (string)$paymentType;
    $transactionStatusValue = (string)$transactionStatus;
    mysqli_stmt_bind_param($updateStmt, 'ssds', $paymentTypeValue, $transactionStatusValue, $grossAmountValue, $orderId);
    mysqli_stmt_execute($updateStmt);
    mysqli_stmt_close($updateStmt);
}

$pesananStatus = 'menunggu_pembayaran';
switch ($transactionStatus) {
    case 'capture':
        $pesananStatus = ($fraudStatus === 'challenge') ? 'menunggu_konfirmasi' : 'diproses';
        break;
    case 'settlement':
        $pesananStatus = 'diproses';
        break;
    case 'pending':
        $pesananStatus = 'menunggu_pembayaran';
        break;
    case 'deny':
    case 'cancel':
    case 'expire':
    case 'failure':
        $pesananStatus = 'dibatalkan';
        break;
    default:
        $pesananStatus = 'menunggu_pembayaran';
        break;
}

$escapedStatus = mysqli_real_escape_string($conn, $pesananStatus);
$extra = '';
if ($pesananStatus === 'diproses') {
    $extra = ", tanggal_konfirmasi = NOW()";
} elseif ($pesananStatus === 'menunggu_konfirmasi') {
    $extra = ", tanggal_konfirmasi = NULL";
}
mysqli_query($conn, "UPDATE pesanan SET status='" . $escapedStatus . "'" . $extra . " WHERE id=$pesananId");

http_response_code(200);
echo json_encode(['message' => 'Notifikasi diterima', 'status' => $transactionStatus]);
