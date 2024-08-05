<?php

namespace App\Service;

use Symfony\Contracts\HttpClient\HttpClientInterface;

class AlphaVantageClient
{
    private $client;
    private $apiKey;

    public function __construct(HttpClientInterface $client, string $apiKey)
    {
        $this->client = $client;
        $this->apiKey = $apiKey;
    }

    public function getDailyTimeSeries(string $symbol): array
    {
        $response = $this->client->request(
            'GET',
            'https://www.alphavantage.co/query',
            [
                'query' => [
                    'function' => 'TIME_SERIES_DAILY',
                    'symbol' => $symbol,
                    'apikey' => $this->apiKey,
                ],
            ]
        );

        if ($response->getStatusCode() !== 200) {
            throw new \Exception('Error fetching data from Alpha Vantage');
        }

        return $response->toArray();
    }
}