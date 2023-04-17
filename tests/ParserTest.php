<?php

declare(strict_types=1);

namespace Tests;

use PHComments\Comment;
use PHComments\Page;
use PHComments\Parser;
use PHPUnit\Framework\TestCase;
use Symfony\Component\BrowserKit\HttpBrowser;
use Symfony\Component\DomCrawler\Crawler;

final class ParserTest extends TestCase
{
    public function testParserCallsCorrectPageMethodWhenRandomVideoMethodIsCalled(): void
    {
        $page = $this->createMock(Page::class);
        $page->expects($this->once())->method('randomVideo');
        $parser = new Parser(page: $page);
        $parser->randomVideo();
    }

    public function testRandomVideoMethodReturnsSelf(): void
    {
        $page = $this->createMock(Page::class);
        $parser = new Parser(page: $page);
        $this->assertInstanceOf(Parser::class, $parser->randomVideo());
    }

    public function testParserCallsCorrectPageMethodWhenSetViewKeyMethodIsCalled(): void
    {
        $viewKey = 'abcdef1234567890';
        $page = $this->createMock(Page::class);
        $page->expects($this->once())->method('setViewKey')->with($this->identicalTo($viewKey));
        $parser = new Parser(page: $page);
        $parser->setViewKey($viewKey);
    }

    public function testSetViewKeyMethodReturnsSelf(): void
    {
        $viewKey = 'abcdef1234567890';
        $page = $this->createMock(Page::class);
        $parser = new Parser(page: $page);
        $this->assertInstanceOf(Parser::class, $parser->setViewKey($viewKey));
    }

    public function testParserCallsCorrectPageMethodWhenSetPageUrlMethodIsCalled(): void
    {
        $url = 'this/is/a/url.php?foo=bar';
        $page = $this->createMock(Page::class);
        $page->expects($this->once())->method('setPageUrl')->with($this->identicalTo($url));
        $parser = new Parser(page: $page);
        $parser->setPageUrl($url);
    }

    public function testSetPageUrlMethodReturnsSelf(): void
    {
        $url = 'this/is/a/url.php?foo=bar';
        $page = $this->createMock(Page::class);
        $parser = new Parser(page: $page);
        $this->assertInstanceOf(Parser::class, $parser->setPageUrl($url));
    }

    public function testGetCommentsDefaultsToCallingParseFirst(): void
    {
        $comments = [
            new Comment(
                body: 'This is a body',
                timestamp: '1 Year Ago',
                author: 'Billy Yenzen',
                votes: '12'
            )
        ];

        $subCrawler = $this->createStub(Crawler::class);
        $subCrawler->method('each')->willReturn($comments);

        $crawler = $this->createStub(Crawler::class);
        $crawler->method('filter')->willReturn($subCrawler);

        $httpBrowser = $this->createStub(HttpBrowser::class);
        $httpBrowser->method('request')->willReturn($crawler);

        $page = $this->createMock(Page::class);
        $parser = new Parser(page: $page, httpBrowser: $httpBrowser);

        $this->assertEquals($comments, $parser->getComments());
    }

    public function testParseIsNotCalledWhenParseFalseIsPassedIntoGetComments(): void
    {
        $comments = [
            new Comment(
                body: 'This is a body',
                timestamp: '1 Year Ago',
                author: 'Billy Yenzen',
                votes: '12'
            )
        ];

        $subCrawler = $this->createStub(Crawler::class);
        $subCrawler->method('each')->willReturn($comments);

        $crawler = $this->createStub(Crawler::class);
        $crawler->method('filter')->willReturn($subCrawler);

        $httpBrowser = $this->createStub(HttpBrowser::class);
        $httpBrowser->method('request')->willReturn($crawler);

        $page = $this->createMock(Page::class);
        $parser = new Parser(page: $page, httpBrowser: $httpBrowser);

        $this->assertEmpty($parser->getComments(parse: false));
    }

    public function testParseUsesCorrectDomLocationsToRetrieveComments(): void
    {

        $filteredPageCrawler = $this->createMock(Crawler::class);
        $filteredPageCrawler->expects($this->once())->method('each');

        $pageCrawler = $this->createMock(Crawler::class);
        $pageCrawler->expects($this->once())
                ->method('filter')
                ->with('div#cmtWrapper > div#cmtContent > div.commentBlock > div.topCommentBlock')
                ->willReturn($filteredPageCrawler);

        $httpBrowser = $this->createMock(HttpBrowser::class);
        $httpBrowser->expects($this->once())->method('request')->willReturn($pageCrawler);

        $page = $this->createMock(Page::class);
        $parser = new Parser(page: $page, httpBrowser: $httpBrowser);
        $parser->getComments();
    }

    public function testOnlyCommentsWithBodiesLargerThanMaxCommentBodyLengthAreRemoved(): void
    {
        $invalidComment = new Comment(
            body: 'This body length is too large',
            timestamp: '1 Year Ago',
            author: 'Billy Yenzen',
            votes: '12'
        );

        $validComment = new Comment(
            body: 'This body is fine',
            timestamp: '3 Weeks Ago',
            author: 'Randy Starbucks',
            votes: '5'
        );

        $subCrawler = $this->createStub(Crawler::class);
        $subCrawler->method('each')->willReturn([$validComment, $invalidComment]);

        $crawler = $this->createStub(Crawler::class);
        $crawler->method('filter')->willReturn($subCrawler);

        $httpBrowser = $this->createStub(HttpBrowser::class);
        $httpBrowser->method('request')->willReturn($crawler);

        $page = $this->createMock(Page::class);
        $parser = new Parser(page: $page, httpBrowser: $httpBrowser, maxCommentBodyLength: 25);

        $this->assertEquals([$validComment], $parser->getComments());
    }

    public function testDefaultMaxCommentBodyLengthIsUsedIfNoneIsSuppliedToParserConstructor(): void
    {
        $page = $this->createMock(Page::class);
        $parser = new Parser(page: $page);

        $this->assertEquals(200, $parser->getMaxCommentBodyLength());
    }

    public function testOnlyCommentsWithAuthorsLargerThanMaxCommentAuthorLengthAreRemoved(): void
    {
        $invalidComment = new Comment(
            body: 'This is a body',
            timestamp: '1 Year Ago',
            author: 'An author whos name is too long',
            votes: '12'
        );

        $validComment = new Comment(
            body: 'This is also a body',
            timestamp: '3 Weeks Ago',
            author: 'Randy Starbucks',
            votes: '5'
        );

        $subCrawler = $this->createStub(Crawler::class);
        $subCrawler->method('each')->willReturn([$validComment, $invalidComment]);

        $crawler = $this->createStub(Crawler::class);
        $crawler->method('filter')->willReturn($subCrawler);

        $httpBrowser = $this->createStub(HttpBrowser::class);
        $httpBrowser->method('request')->willReturn($crawler);

        $page = $this->createMock(Page::class);
        $parser = new Parser(page: $page, httpBrowser: $httpBrowser, maxCommentAuthorLength: 15);

        $this->assertEquals([$validComment], $parser->getComments());
    }

    public function testDefaultMaxCommentAuthorLengthIsUsedIfNoneIsSuppliedToParserConstructor(): void
    {
        $page = $this->createMock(Page::class);
        $parser = new Parser(page: $page);

        $this->assertEquals(15, $parser->getMaxCommentAuthorLength());
    }
}
