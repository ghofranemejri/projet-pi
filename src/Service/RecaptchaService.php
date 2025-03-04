<?php
// src/Service/RecaptchaService.php
namespace App\Service;

use Symfony\Contracts\HttpClient\HttpClientInterface;

class RecaptchaService
{
    private $client;
    private $secretKey;

    public function __construct(HttpClientInterface $client, string $recaptchaSecretKey)
    {
        $this->client = $client;
        $this->secretKey = $recaptchaSecretKey;
    }

    public function verify(string $recaptchaResponse): bool
    {
        $response = $this->client->request('POST', 'https://www.google.com/recaptcha/api/siteverify', [
            'json' => [
                'secret' => $this->secretKey,
                'response' => $recaptchaResponse,
            ],
        ]);

        $data = $response->toArray();
        return $data['success'] ?? false;
    }
}
