<?php

namespace PHComments\Proxy;

use Exception;
use Symfony\Component\HttpClient\HttpClient;

readonly class Proxy
{
    private const array UNRESTRICTED_REGIONS = [
        'IE' => 'Dublin',
    ];

    public const string URL = 'https://raw.githubusercontent.com/proxifly/free-proxy-list/refs/heads/main/proxies/countries/IE/data.json';

    public function __construct(
        public string $proxy,
        public string $protocol,
        public string $ip,
        public int $port,
        public bool $https,
        public string $anonymity,
        public int $score,
        public Geolocation $geolocation,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            proxy: $data['proxy'],
            protocol: $data['protocol'],
            ip: $data['ip'],
            port: $data['port'],
            https: $data['https'],
            anonymity: $data['anonymity'],
            score: $data['score'],
            geolocation: Geolocation::fromArray($data['geolocation'])
        );
    }

    public function isUnrestricted(): bool
    {
        return array_any(
            self::UNRESTRICTED_REGIONS,
            fn (string $city, string $country) => (
                $this->geolocation->country === $country &&
                $this->geolocation->city === $city
            )
        );
    }

    public function isWorking(): bool
    {
        $client = HttpClient::create([
            'proxy' => $this->proxy,
            'extra' => [
                'curl' => [
                    \CURLOPT_PROXY_SSL_VERIFYPEER => 0,
                    \CURLOPT_PROXY_SSL_VERIFYHOST => 0,
                ],
            ],
            'timeout' => 10,
        ]);

        try {
            return $this->ip === $client->request('GET', 'https://api.ipify.org')->getContent();
        } catch (Exception) {
        }

        return false;
    }
}
