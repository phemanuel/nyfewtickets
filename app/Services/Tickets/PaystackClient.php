<?php
namespace App\Services\Tickets;

use Illuminate\Support\Facades\Http;
use RuntimeException;

class PaystackClient
{
    private function key(): string
    {
        $key = config('nyfew_paystack.secret_key');
        if (!is_string($key) || !str_starts_with($key, 'sk_')) {
            throw new RuntimeException('Paystack secret key is not configured.');
        }
        return $key;
    }

    public function initialize(string $email, int $amountKobo, string $reference, string $callback): string
    {
        $response = Http::withToken($this->key())->acceptJson()->timeout(15)->post(
            config('nyfew_paystack.base_url').'/transaction/initialize',
            ['email'=>$email,'amount'=>$amountKobo,'currency'=>'NGN','reference'=>$reference,'callback_url'=>$callback]
        );
        if (!$response->successful() || $response->json('status') !== true) {
            throw new RuntimeException('Paystack could not initialize this transaction.');
        }
        $url = $response->json('data.authorization_url');
        if (!is_string($url) || !str_starts_with($url, 'https://checkout.paystack.com/')) {
            throw new RuntimeException('Invalid checkout URL from Paystack.');
        }
        return $url;
    }

    public function verify(string $reference): array
    {
        $response = Http::withToken($this->key())->acceptJson()->timeout(15)->get(
            config('nyfew_paystack.base_url').'/transaction/verify/'.rawurlencode($reference)
        );
        if (!$response->successful() || $response->json('status') !== true || !is_array($response->json('data'))) {
            throw new RuntimeException('Paystack payment verification is currently unavailable.');
        }
        return $response->json('data');
    }

    public function validWebhook(string $body, ?string $signature): bool
    {
        return is_string($signature) && hash_equals(hash_hmac('sha512', $body, $this->key()), $signature);
    }
}
