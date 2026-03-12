<?php

namespace App\Services;

use Illuminate\Support\Facades\Log;

class LiqPayService
{
    protected string $publicKey;
    protected string $privateKey;

    public function __construct(?string $publicKey = null, ?string $privateKey = null)
    {
        $this->publicKey = $publicKey ?? config('services.liqpay.public_key', '');
        $this->privateKey = $privateKey ?? config('services.liqpay.private_key', '');
    }

    /**
     * Create LiqPay payment form data
     */
    public function createPayment(int $amount, string $orderId, string $description, string $resultUrl, string $serverUrl): array
    {
        $params = [
            'version' => 3,
            'public_key' => $this->publicKey,
            'action' => 'pay',
            'amount' => $amount,
            'currency' => 'UAH',
            'description' => $description,
            'order_id' => $orderId,
            'result_url' => $resultUrl,
            'server_url' => $serverUrl,
        ];

        $data = base64_encode(json_encode($params));
        $signature = base64_encode(sha1($this->privateKey . $data . $this->privateKey, true));

        return [
            'data' => $data,
            'signature' => $signature,
        ];
    }

    /**
     * Verify LiqPay callback
     */
    public function verifyCallback(string $data, string $signature): ?array
    {
        $expectedSignature = base64_encode(sha1($this->privateKey . $data . $this->privateKey, true));

        if ($signature !== $expectedSignature) {
            Log::warning('LiqPay: Invalid signature');
            return null;
        }

        return json_decode(base64_decode($data), true);
    }

    /**
     * Create with custom merchant keys (per-course FOP)
     */
    public static function forCourse($course): self
    {
        if ($course->liqpay_merchant_id && $course->liqpay_private_key) {
            return new self($course->liqpay_merchant_id, $course->liqpay_private_key);
        }
        return new self();
    }
}
