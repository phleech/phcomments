<?php

namespace PHComments\Proxy;

readonly class Geolocation
{
    public function __construct(
        public string $country,
        public string $city
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            country: $data['country'],
            city: $data['city']
        );
    }
}
