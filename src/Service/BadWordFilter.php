<?php
namespace App\Service;

use Symfony\Contracts\HttpClient\HttpClientInterface;

class BadWordFilter
{
    private HttpClientInterface $httpClient;

    public function __construct(HttpClientInterface $httpClient)
    {
        $this->httpClient = $httpClient;
    }

    public function containsBadWords(string $text): bool
    {
        $response = $this->httpClient->request('GET', 'https://www.purgomalum.com/service/containsprofanity', [
            'query' => ['text' => $text],
        ]);

        return $response->getContent() === 'true';
    }
}
