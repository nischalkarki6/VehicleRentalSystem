<?php

class EsewaHelper
{
    private string $secretKey;
    private string $productCode;
    private string $paymentUrl;
    private string $statusUrl;

    public function __construct()
    {
        $this->secretKey  = $_ENV['ESEWA_SECRET_KEY']  ?? '8gBm/:&EnhH.1/q';
        $this->productCode = $_ENV['ESEWA_PRODUCT_CODE'] ?? 'EPAYTEST';
        $this->paymentUrl  = $_ENV['ESEWA_PAYMENT_URL']  ?? 'https://rc-epay.esewa.com.np/api/epay/main/v2/form';
        $this->statusUrl   = $_ENV['ESEWA_STATUS_URL']   ?? 'https://rc.esewa.com.np/api/epay/transaction/status/';
    }

    public function generateSignature(string $message): string
    {
        $hash = hash_hmac('sha256', $message, $this->secretKey, true);
        return base64_encode($hash);
    }

    public function buildPaymentData(
        float $totalAmount,
        string $transactionUuid,
        string $successUrl,
        string $failureUrl
    ): array {
        // Ensure amount is formatted as a string with no decimals if integer, or consistent decimals
        // eSewa UAT usually prefers plain integers for whole numbers
        $formattedAmount = strval($totalAmount);

        $message = "total_amount={$formattedAmount},transaction_uuid={$transactionUuid},product_code={$this->productCode}";
        $signature = $this->generateSignature($message);

        return [
            'amount'                 => $formattedAmount,
            'tax_amount'             => '0',
            'total_amount'           => $formattedAmount,
            'transaction_uuid'       => $transactionUuid,
            'product_code'           => $this->productCode,
            'product_service_charge' => '0',
            'product_delivery_charge'=> '0',
            'success_url'            => $successUrl,
            'failure_url'            => $failureUrl,
            'signed_field_names'     => 'total_amount,transaction_uuid,product_code',
            'signature'              => $signature,
        ];
    }

    public function verifyResponseSignature(array $data): bool
    {
        $signedFields = $data['signed_field_names'] ?? '';
        if (empty($signedFields) || empty($data['signature'])) {
            return false;
        }

        $fields = explode(',', $signedFields);
        $parts  = [];
        foreach ($fields as $field) {
            $parts[] = $field . '=' . ($data[$field] ?? '');
        }
        $message = implode(',', $parts);

        $expectedSignature = $this->generateSignature($message);
        return hash_equals($expectedSignature, $data['signature']);
    }

    public function checkTransactionStatus(string $transactionUuid, float $totalAmount): ?array
    {
        $url = $this->statusUrl . '?' . http_build_query([
            'product_code'   => $this->productCode,
            'total_amount'   => $totalAmount,
            'transaction_uuid' => $transactionUuid,
        ]);

        $context = stream_context_create([
            'http' => [
                'method'  => 'GET',
                'timeout' => 15,
                'header'  => "Accept: application/json\r\n",
            ],
        ]);

        $response = @file_get_contents($url, false, $context);
        if ($response === false) {
            return null;
        }

        return json_decode($response, true);
    }

    public function generateTransactionUuid(int $rentalId): string
    {
        return $rentalId . '-' . date('ymdHis');
    }

    public function getPaymentUrl(): string
    {
        return $this->paymentUrl;
    }

    public function getProductCode(): string
    {
        return $this->productCode;
    }
}
