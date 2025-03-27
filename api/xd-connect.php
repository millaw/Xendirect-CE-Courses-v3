<?php
declare(strict_types=1);

class XDConnectAPI {
    private string $apiKey;
    private string $organizationId;
    private string $baseUrl;

    public function __construct() {
        $options = get_option('xd_ce_options', []);
        
        $this->apiKey = (string)($options['api_key'] ?? '');
        $this->organizationId = (string)($options['organization_id'] ?? '');
        $this->baseUrl = rtrim((string)($options['base_url'] ?? 'https://api.xendirect.com/v2/'), '/') . '/';
    }

    private function authenticate(): ?string {
        $ch = curl_init();
        
        curl_setopt_array($ch, [
            CURLOPT_URL => $this->baseUrl . 'authentication',
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POST => true,
            CURLOPT_POSTFIELDS => http_build_query([
                'apiKey' => $this->apiKey,
                'organizationId' => $this->organizationId
            ]),
            CURLOPT_HTTPHEADER => [
                'Content-Type: application/x-www-form-urlencoded'
            ],
            CURLOPT_TIMEOUT => 10,
            CURLOPT_SSL_VERIFYPEER => true,
            CURLOPT_FAILONERROR => true
        ]);

        $response = curl_exec($ch);
        
        if (curl_errno($ch)) {
            error_log('cURL Error: ' . curl_error($ch));
            curl_close($ch);
            return null;
        }

        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($httpCode !== 200) {
            error_log('API Auth Failed. HTTP Code: ' . $httpCode);
            return null;
        }

        $data = json_decode($response, true);
        return $data['access_token'] ?? null;
    }

    public function getCoursesByProgramName(string $program): ?array {
        if (!$token = $this->authenticate()) {
            return null;
        }

        $ch = curl_init();
        $url = $this->baseUrl . 'courses?' . http_build_query([
            '_limit' => 0,
            'orderBy' => 'startDate',
            'program' => $program
        ]);

        curl_setopt_array($ch, [
            CURLOPT_URL => $url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_HTTPHEADER => [
                'Authorization: ' . $token,
                'Accept: application/json'
            ],
            CURLOPT_TIMEOUT => 15,
            CURLOPT_SSL_VERIFYPEER => true
        ]);

        $response = curl_exec($ch);
        
        if (curl_errno($ch)) {
            error_log('Courses cURL Error: ' . curl_error($ch));
            curl_close($ch);
            return null;
        }

        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($httpCode !== 200) {
            error_log('Courses Fetch Failed. HTTP Code: ' . $httpCode);
            return null;
        }

        return json_decode($response, true) ?: null;
    }
}