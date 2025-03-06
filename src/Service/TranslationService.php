<?php

namespace App\Service;

use Symfony\Contracts\HttpClient\HttpClientInterface;

class TranslationService
{
    private $httpClient;

    public function __construct(HttpClientInterface $httpClient)
    {
        $this->httpClient = $httpClient;
    }

    public function translate(string $text, string $targetLang): string
    {
        try {
            $response = $this->httpClient->request('GET', 'https://translate.googleapis.com/translate_a/single', [
                'query' => [
                    'client' => 'gtx',
                    'sl' => 'auto',
                    'tl' => $targetLang,
                    'dt' => 't',
                    'q' => $text,
                ],
            ]);

            $result = $response->toArray();
            $translatedText = '';

            // Combine all translated segments
            foreach ($result[0] as $segment) {
                $translatedText .= $segment[0];
            }

            return $translatedText;
        } catch (\Exception $e) {
            return $text; // Return original text if translation fails
        }
    }
}
