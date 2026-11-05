<?php

namespace PHComments;

use Exception;
use PHComments\Proxy\Proxy;
use Symfony\Component\BrowserKit\Cookie;
use Symfony\Component\BrowserKit\HttpBrowser;
use Symfony\Component\DomCrawler\Crawler;
use Symfony\Component\HttpClient\HttpClient;
use Symfony\Contracts\HttpClient\HttpClientInterface;

class Parser
{
    private const COMMENT_DOM_LOCATION = 'div#cmtWrapper > div#cmtContent > div.commentBlock > div.topCommentBlock';

    private const BODY_DOM_LOCATION = 'div.commentMessage > span';

    private const TIMESTAMP_DOM_LOCATION = 'div.userWrap > div.date';

    private const AUTHOR_DOM_LOCATION = 'div.userWrap > div.usernameWrap .usernameLink';

    private const VOTES_DOM_LOCATION = 'div.commentMessage > div.actionButtonsBlock > span.voteTotal';

    public const COOKIE_AGE_DISCLAIMER_NAME = 'accessAgeDisclaimerPH';

    public const COOKIE_AGE_DISCLAIMER_VALUE = '1';

    private HttpBrowser $httpBrowser;

    private array $comments = [];

    public function __construct(
        public readonly int $maxCommentBodyLength = Comment::DEFAULT_MAX_BODY_LENGTH,
        public readonly int $maxCommentAuthorLength = Comment::DEFAULT_MAX_AUTHOR_LENGTH,
        ?HttpBrowser $httpBrowser = null,
        private Page $page = new Page
    ) {
        $this->httpBrowser = $httpBrowser ?? new HttpBrowser($this->getClient());
    }

    private function getClient(): HttpClientInterface
    {
        $proxies = $this->getProxies();

        if (empty($proxies)) {
            throw new Exception('Failed to retrieve any available proxies, has the schema changed?');
        }

        foreach ($proxies as $proxy) {
            if ($proxy->isWorking()) {
                return HttpClient::create([
                    'proxy' => $proxy->proxy,
                    'extra' => [
                        'curl' => [
                            \CURLOPT_PROXY_SSL_VERIFYPEER => 0,
                            \CURLOPT_PROXY_SSL_VERIFYHOST => 0,
                        ],
                    ],
                    'timeout' => 10,
                ]);
            }
        }

        throw new Exception('Failed to retreive a working proxy, are the restricted regions correct?');
    }

    private function getProxies(): array
    {
        $client = HttpClient::create();
        $response = $client->request('GET', Proxy::URL);

        if ($response->getStatusCode() !== 200) {
            throw new Exception('Failed to retreive any proxies, has the URL changed?');
        }

        $proxies = array_map(
            Proxy::fromArray(...),
            $response->toArray()
        );

        return array_filter(
            $proxies,
            fn (Proxy $proxy) => $proxy->isUnrestricted()
        );
    }

    public function randomVideo(): self
    {
        $this->page->randomVideo();

        return $this;
    }

    public function setViewKey(string $viewKey): self
    {
        $this->page->setViewKey($viewKey);

        return $this;
    }

    public function setPageUrl(string $url): self
    {
        $this->page->setPageUrl($url);

        return $this;
    }

    private function parse(): void
    {
        $crawler = $this->makeRequest();
        $this->comments = $crawler->filter(self::COMMENT_DOM_LOCATION)->each(function (Crawler $node) {
            return new Comment(
                body: $node->filter(self::BODY_DOM_LOCATION)->text(),
                timestamp: $node->filter(self::TIMESTAMP_DOM_LOCATION)->text(),
                author: $node->filter(self::AUTHOR_DOM_LOCATION)->text(),
                votes: $node->filter(self::VOTES_DOM_LOCATION)->text()
            );
        });

        $this->filterMaxCommentBodyLength();
        $this->filterMaxCommentAuthorLength();
    }

    private function makeRequest(): Crawler
    {
        $this->addCookiesToCookieJar();

        return $this->httpBrowser->request('GET', $this->page->getUrl());
    }

    private function addCookiesToCookieJar()
    {
        $this->httpBrowser->getCookieJar()->set(
            new Cookie(self::COOKIE_AGE_DISCLAIMER_NAME, self::COOKIE_AGE_DISCLAIMER_VALUE, strtotime('+1 day'))
        );
    }

    private function filterMaxCommentBodyLength(): void
    {
        $this->comments = array_values(
            array_filter($this->comments, function (Comment $comment) {
                return strlen($comment->body) <= $this->maxCommentBodyLength;
            })
        );
    }

    private function filterMaxCommentAuthorLength(): void
    {
        $this->comments = array_values(
            array_filter($this->comments, function (Comment $comment) {
                return strlen($comment->author) <= $this->maxCommentAuthorLength;
            })
        );
    }

    public function getComments(bool $parse = true): array
    {
        if ($parse) {
            $this->parse();
        }

        return $this->comments;
    }
}
