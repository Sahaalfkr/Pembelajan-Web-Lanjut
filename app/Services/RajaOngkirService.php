<?php
namespace App\Services;
use Config\Services;

class RajaOngkirService
{
    protected $client;
    protected $apiKey;
    protected $baseUrl;

    public function __construct()
    {
        $this->client = Services::curlrequest([
            'timeout' => 20,
            'http_errors' => false,
            'connect_timeout' => 15,
        ]);

        $this->apiKey = env('RAJAONGKIR_API_KEY');
        $this->baseUrl = env('RAJAONGKIR_BASE_URL');
    }
    
    public function getDestination(string $keyword): array
    {
        if (trim($keyword) === '') {
            return ['results' => []];
        }

        // Development/Test mode - return mock data
        if (env('RAJAONGKIR_MOCK', false) == 'true') {
            return $this->getMockDestinations($keyword);
        }

        try {
            $response = $this->client->get(
                $this->baseUrl . 'destination/domestic-destination',
                [
                    'headers' => [
                        'Accept' => 'application/json',
                        'key'    => $this->apiKey,
                    ],
                    'query' => [
                        'search' => $keyword,
                        'limit'  => 50,
                    ]
                ]
            );

            $body = (string) $response->getBody();
            $decoded = json_decode($body, true);

            if (json_last_error() !== JSON_ERROR_NONE) {
                log_message('error', 'RajaOngkir JSON decode error: ' . json_last_error_msg());
                return ['results' => []];
            }

            return $decoded ?? ['results' => []];
        } catch (\Throwable $e) {
            log_message('error', 'RajaOngkir getDestination error: ' . $e->getMessage());
            return ['results' => []];
        }
    }

    private function getMockDestinations(string $keyword): array
    {
        $mockData = [
            'results' => [
                ['city_id' => '26', 'province_id' => '8', 'province' => 'Jawa Tengah', 'city_name' => 'Kota Semarang', 'type' => 'Kota'],
                ['city_id' => '27', 'province_id' => '8', 'province' => 'Jawa Tengah', 'city_name' => 'Kabupaten Semarang', 'type' => 'Kabupaten'],
                ['city_id' => '28', 'province_id' => '8', 'province' => 'Jawa Tengah', 'city_name' => 'Kota Demak', 'type' => 'Kota'],
            ]
        ];

        return $mockData;
    }
    
    public function getCost(string $origin, string $destination, int $weight, string $courier): array 
    {
        // Development/Test mode - return mock data
        if (env('RAJAONGKIR_MOCK', false) == 'true') {
            return $this->getMockCosts();
        }

        try {
            $response = $this->client->post(
                $this->baseUrl . 'calculate/domestic-cost',
                [
                    'headers' => [
                        'Accept' => 'application/json',
                        'key'    => $this->apiKey,
                    ],
                    'form_params' => [
                        'origin'      => $origin,
                        'destination' => $destination,
                        'weight'      => $weight,
                        'courier'     => $courier,
                    ]
                ]
            );

            $decoded = json_decode(
                (string) $response->getBody(),
                true
            );

            if (json_last_error() !== JSON_ERROR_NONE) {
                log_message('error', 'RajaOngkir getCost JSON decode error: ' . json_last_error_msg());
                return [];
            }

            return $decoded ?? [];
        } catch (\Throwable $e) {
            log_message('error', 'RajaOngkir getCost error: ' . $e->getMessage());
            return [];
        }
    }

    private function getMockCosts(): array
    {
        return [
            'rajaongkir' => [
                'results' => [
                    [
                        'costs' => [
                            [
                                'service'     => 'OKE',
                                'description' => 'Ongkos Kirim Ekonomis',
                                'cost'        => [
                                    ['value' => 65000, 'etd' => '3-6 hari']
                                ],
                                'etd'         => '3-6 hari'
                            ],
                            [
                                'service'     => 'REG',
                                'description' => 'Regular',
                                'cost'        => [
                                    ['value' => 95000, 'etd' => '2-3 hari']
                                ],
                                'etd'         => '2-3 hari'
                            ],
                            [
                                'service'     => 'YES',
                                'description' => 'Yakin Esok Sampai',
                                'cost'        => [
                                    ['value' => 145000, 'etd' => '1 hari']
                                ],
                                'etd'         => '1 hari'
                            ]
                        ]
                    ]
                ]
            ]
        ];
    }
}