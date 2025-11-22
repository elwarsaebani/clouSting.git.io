<?php
namespace Midtrans;

class Config
{
    public static $serverKey;
    public static $clientKey;
    public static $isProduction = false;
    public static $curlOptions = [];

    public static function getBaseUrl(): string
    {
        return self::$isProduction ? 'https://app.midtrans.com' : 'https://app.sandbox.midtrans.com';
    }

    public static function getSnapBaseUrl(): string
    {
        return self::getBaseUrl() . '/snap/v1';
    }

    public static function getApiBaseUrl(): string
    {
        return self::getBaseUrl() . '/v2';
    }
}

class MidtransException extends \Exception
{
}

class ApiRequestor
{
    public static function post(string $url, string $serverKey, array $data): array
    {
        return self::remoteCall($url, $serverKey, 'POST', $data);
    }

    private static function remoteCall(string $url, string $serverKey, string $method, ?array $data = null): array
    {
        $ch = curl_init();
        if ($ch === false) {
            throw new MidtransException('Gagal menginisialisasi koneksi ke Midtrans.');
        }

        $headers = [
            'Content-Type: application/json',
            'Accept: application/json'
        ];

        $payload = $data !== null ? json_encode($data) : '';
        if ($payload === false) {
            throw new MidtransException('Gagal menyiapkan data transaksi.');
        }

        curl_setopt_array($ch, [
            CURLOPT_URL => $url,
            CURLOPT_CUSTOMREQUEST => $method,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_HTTPHEADER => $headers,
            CURLOPT_USERPWD => $serverKey . ':',
            CURLOPT_POSTFIELDS => $payload,
            CURLOPT_TIMEOUT => 30,
        ] + Config::$curlOptions);

        $responseBody = curl_exec($ch);
        if ($responseBody === false) {
            $error = curl_error($ch);
            curl_close($ch);
            throw new MidtransException('Permintaan ke Midtrans gagal: ' . $error);
        }

        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        $decoded = json_decode($responseBody, true);
        if ($decoded === null) {
            throw new MidtransException('Respon Midtrans tidak valid: ' . $responseBody);
        }

        if ($httpCode < 200 || $httpCode >= 300) {
            $message = $decoded['status_message'] ?? ('Permintaan Midtrans gagal dengan kode ' . $httpCode);
            throw new MidtransException($message);
        }

        return $decoded;
    }
}

class Snap
{
    public static function createTransaction(array $params): array
    {
        if (!Config::$serverKey) {
            throw new MidtransException('Server key Midtrans belum dikonfigurasi.');
        }

        $url = Config::getSnapBaseUrl() . '/transactions';
        return ApiRequestor::post($url, Config::$serverKey, $params);
    }
}

class Signature
{
    public static function validate(string $signatureKey, string $orderId, string $statusCode, string $grossAmount, string $serverKey): bool
    {
        $expected = hash('sha512', $orderId . $statusCode . $grossAmount . $serverKey);
        return hash_equals($expected, $signatureKey);
    }
}

class Notification
{
    private $payload;

    public function __construct(?string $input = null)
    {
        if ($input === null) {
            $input = file_get_contents('php://input');
        }

        $data = json_decode($input, true);
        if ($data === null) {
            throw new MidtransException('Payload notifikasi tidak valid.');
        }

        $this->payload = $data;
    }

    public function getPayload(): array
    {
        return $this->payload;
    }

    public function get(string $key, $default = null)
    {
        return $this->payload[$key] ?? $default;
    }
}
