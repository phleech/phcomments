<?php

namespace PHComments;

require __DIR__.'/../vendor/autoload.php';
use Symfony\Component\BrowserKit\HttpBrowser;
use Symfony\Component\DomCrawler\Crawler;

class Parser
{
    private const COMMENT_DOM_LOCATION = 'div#cmtWrapper > div#cmtContent > div.commentBlock > div.topCommentBlock';

    private const BODY_DOM_LOCATION = 'div.commentMessage > span';

    private const TIMESTAMP_DOM_LOCATION = 'div.userWrap > div.date';

    private const AUTHOR_DOM_LOCATION = 'div.userWrap > div.usernameWrap .usernameLink';

    private const VOTES_DOM_LOCATION = 'div.commentMessage > div.actionButtonsBlock > span.voteTotal';

    private array $comments = [];

    public function __construct(
        private int $maxCommentBodyLength = Comment::DEFAULT_MAX_BODY_LENGTH,
        private int $maxCommentAuthorLength = Comment::DEFAULT_MAX_AUTHOR_LENGTH,
        private HttpBrowser $httpBrowser = new HttpBrowser(),
        private Page $page = new Page()
    ) {
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
        return $this->httpBrowser->request('GET', $this->page->getUrl());
    }

    private function filterMaxCommentBodyLength(): void
    {
        $this->comments = array_values(
            array_filter($this->comments, function (Comment $comment) {
                return strlen($comment->getBody()) <= $this->maxCommentBodyLength;
            })
        );
    }

    private function filterMaxCommentAuthorLength(): void
    {
        $this->comments = array_values(
            array_filter($this->comments, function (Comment $comment) {
                return strlen($comment->getAuthor()) <= $this->maxCommentAuthorLength;
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

    public function getMaxCommentBodyLength(): int
    {
        return $this->maxCommentBodyLength;
    }

    public function getMaxCommentAuthorLength(): int
    {
        return $this->maxCommentAuthorLength;
    }
}
