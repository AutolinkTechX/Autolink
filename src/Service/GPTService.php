<?php
namespace App\Service;

use Symfony\Contracts\HttpClient\HttpClientInterface;

class GPTService
{
    private HttpClientInterface $client;
    private string $openaiApiKey;

    public function __construct(HttpClientInterface $client, string $openaiApiKey)
    {
        $this->client = $client;
        $this->openaiApiKey = $openaiApiKey;
    }

    public function getResponse(string $prompt): string
    {
        $response = $this->client->request('POST', 'https://api.openai.com/v1/chat/completions', [
            'headers' => [
                'Authorization' => 'Bearer ' . $this->openaiApiKey,
                'Content-Type'  => 'application/json',
            ],
            'json' => [
                'model'    => 'gpt-3.5-turbo',
                'messages' => [['role' => 'user', 'content' => $prompt]],
                'max_tokens' => 150,
            ],
        ]);

        $data = $response->toArray();
        return $data['choices'][0]['message']['content'] ?? 'Désolé, je ne peux pas répondre pour le moment.';
    }
}
