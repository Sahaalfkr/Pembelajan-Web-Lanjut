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
                        'offset' => 0,
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
        return [
            'meta' => [
                'message' => 'Success Get Domestic Destinations',
                'code'    => 200,
                'status'  => 'success',
            ],
            'data' => [
                [
                    'id' => '65043',
                    'label' => 'Pindrikan Kidul, Semarang Tengah, Semarang, Jawa Tengah, 50241',
                    'province_name' => 'Jawa Tengah',
                    'city_name' => 'Semarang',
                    'district_name' => 'Semarang Tengah',
                    'subdistrict_name' => 'Pindrikan Kidul',
                    'zip_code' => '50241'
                ],
                [
                    'id' => '65042',
                    'label' => 'Banjardowo, Semarang Tengah, Semarang, Jawa Tengah, 50242',
                    'province_name' => 'Jawa Tengah',
                    'city_name' => 'Semarang',
                    'district_name' => 'Semarang Tengah',
                    'subdistrict_name' => 'Banjardowo',
                    'zip_code' => '50242'
                ],
                [
                    'id' => '65005',
                    'label' => 'Bojongsalaman, Semarang Barat, Semarang, Jawa Tengah, 50141',
                    'province_name' => 'Jawa Tengah',
                    'city_name' => 'Semarang',
                    'district_name' => 'Semarang Barat',
                    'subdistrict_name' => 'Bojongsalaman',
                    'zip_code' => '50141'
                ],
                [
                    'id' => '65006',
                    'label' => 'Bongsari, Semarang Barat, Semarang, Jawa Tengah, 50148',
                    'province_name' => 'Jawa Tengah',
                    'city_name' => 'Semarang',
                    'district_name' => 'Semarang Barat',
                    'subdistrict_name' => 'Bongsari',
                    'zip_code' => '50148'
                ],
            ]
        ];
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
            'meta' => [
                'message' => 'Success Calculate Domestic Shipping cost',
                'code'    => 200,
                'status'  => 'success',
            ],
            'data' => [
                [
                    'name' => 'Jalur Nugraha Ekakurir (JNE)',
                    'code' => 'jne',
                    'service' => 'CTC',
                    'description' => 'JNE City Courier',
                    'cost' => 9000,
                    'etd' => '1 day'
                ],
                [
                    'name' => 'Jalur Nugraha Ekakurir (JNE)',
                    'code' => 'jne',
                    'service' => 'JTR',
                    'description' => 'JNE Trucking',
                    'cost' => 40000,
                    'etd' => '3 day'
                ],
                [
                    'name' => 'Jalur Nugraha Ekakurir (JNE)',
                    'code' => 'jne',
                    'service' => 'CTCSPS',
                    'description' => 'JNE City Courier',
                    'cost' => 25000,
                    'etd' => '0 day'
                ],
                [
                    'name' => 'Jalur Nugraha Ekakurir (JNE)',
                    'code' => 'jne',
                    'service' => 'CTCYES',
                    'description' => 'JNE City Courier',
                    'cost' => 11000,
                    'etd' => '1 day'
                ]
            ]
        ];
    }
}