<?php

namespace PHComments;

use Exception;

class Page
{
    private string $url = '';

    private const DEFAULT_BASE_PATH = 'https://www.pornhub.com';

    private const URL_RANDOM_VIDEO = 'video/random';

    private const URL_VIEWKEY = 'view_video.php?viewkey=';

    public function __construct(private string $basePath = self::DEFAULT_BASE_PATH)
    {
    }

    public function setViewKey(string $viewKey): void
    {
        $this->setUrl(sprintf('%s%s', self::URL_VIEWKEY, $viewKey));
    }

    public function setPageUrl(string $url): void
    {
        $this->setUrl($url);
    }

    public function randomVideo(): void
    {
        $this->setUrl(self::URL_RANDOM_VIDEO);
    }

    public function getUrl(): string
    {
        $url = $this->trimSlashes($this->url);

        if (empty($url)) {
            throw new Exception('Empty URL');
        }

        return $url;
    }

    private function setUrl(string $url): void
    {
        if (str_starts_with($url, $this->basePath)) {
            $url = ltrim(str_replace($this->basePath, '',  $url), '/');
        }

        $this->url = sprintf('%s/%s', $this->basePath, $url);
    }

    private function trimSlashes(string $string): string
    {
        return trim($string, '/');
    }
}
