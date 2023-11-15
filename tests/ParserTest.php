<?php

declare(strict_types=1);

namespace Tests;

use PHComments\Comment;
use PHComments\Page;
use PHComments\Parser;
use PHPUnit\Framework\TestCase;
use Symfony\Component\BrowserKit\Cookie;
use Symfony\Component\BrowserKit\CookieJar;
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
        $parser = new Parser();
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
        $parser = new Parser();
        $this->assertInstanceOf(Parser::class, $parser->setViewKey(viewKey: 'abcdef1234567890'));
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
        $parser = new Parser();
        $this->assertInstanceOf(Parser::class, $parser->setPageUrl(url: 'this/is/a/url.php?foo=bar'));
    }

    private function GeneratePageHtml(array $comments): string
    {
        $html = "<html><body><div id='cmtWrapper'><div id='cmtContent'>";

        foreach ($comments as $comment) {
            $html .= "
                <!-- comment start -->
                    <div class='commentBlock'>
                        <div class='topCommentBlock'>
                            <!-- body start -->
                            <div class='commentMessage'>
                                <span>".$comment->body."</span>
                            </div>
                            <!-- body end -->
                            <!-- timestamp start -->
                            <div class='userWrap'>
                                <div class='date'>".$comment->timestamp."</div>
                            </div>
                            <!-- timestamp end -->
                            <!-- author start -->
                            <div class='userWrap'>
                                <div class='usernameWrap'>
                                    <a class='usernameLink'>".$comment->author."</a>
                                </div>
                            </div>
                            <!-- author end -->
                            <!-- votes start -->
                            <div class='commentMessage'>
                                <div class='actionButtonsBlock'>
                                    <span class='voteTotal'>".$comment->votes.'</span>
                                </div>
                            </div>
                            <!-- votes end -->
                        </div>
                    </div>
                    <!-- comment end -->
            ';
        }

        $html .= '</div></div></body></html>';

        return $html;

    }

    public function testGetCommentsDefaultsToCallingParseFirst(): void
    {
        $comments = [
            new Comment(
                body: 'This is a comment body',
                timestamp: '1 year ago',
                author: 'Randy Starbucks',
                votes: '12'
            ),
        ];

        $crawler = new Crawler($this->generatePageHtml($comments), '/random');

        $httpBrowser = $this->createStub(HttpBrowser::class);
        $httpBrowser->method('request')->willReturn($crawler);

        $parser = new Parser(httpBrowser: $httpBrowser);
        $parser->randomVideo();

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
            ),
        ];

        $crawler = new Crawler($this->generatePageHtml($comments), '/random');

        $httpBrowser = $this->createStub(HttpBrowser::class);
        $httpBrowser->method('request')->willReturn($crawler);

        $parser = new Parser(httpBrowser: $httpBrowser);
        $parser->randomVideo();

        $this->assertEmpty($parser->getComments(parse: false));
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

        $crawler = new Crawler($this->generatePageHtml([$invalidComment, $validComment]), '/random');
        $httpBrowser = $this->createStub(HttpBrowser::class);
        $httpBrowser->method('request')->willReturn($crawler);

        $parser = new Parser(httpBrowser: $httpBrowser, maxCommentBodyLength: 25);
        $parser->randomVideo();

        $this->assertEquals([$validComment], $parser->getComments());
    }

    public function testDefaultMaxCommentBodyLengthIsUsedIfNoneIsSuppliedToParserConstructor(): void
    {
        $parser = new Parser();

        $this->assertEquals(200, $parser->maxCommentBodyLength);
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

        $crawler = new Crawler($this->generatePageHtml([$invalidComment, $validComment]), '/random');
        $httpBrowser = $this->createStub(HttpBrowser::class);
        $httpBrowser->method('request')->willReturn($crawler);

        $parser = new Parser(httpBrowser: $httpBrowser, maxCommentAuthorLength: 15);
        $parser->randomVideo();

        $this->assertEquals([$validComment], $parser->getComments());
    }

    public function testDefaultMaxCommentAuthorLengthIsUsedIfNoneIsSuppliedToParserConstructor(): void
    {
        $parser = new Parser();

        $this->assertEquals(15, $parser->maxCommentAuthorLength);
    }

    public function testAgeDisclaimerCookieIsPresentOnRequest(): void
    {
        $cookieJar = new CookieJar();
        $httpBrowser = $this->createStub(HttpBrowser::class);
        $httpBrowser->method('request')->willReturn(new Crawler('', '/random'));
        $httpBrowser->method('getCookieJar')->willReturn($cookieJar);

        $parser = new Parser(httpBrowser: $httpBrowser);
        $parser->setPageUrl('');
        $parser->getComments();

        $cookie = $cookieJar->get(Parser::COOKIE_AGE_DISCLAIMER_NAME);
        $this->assertInstanceOf(Cookie::class, $cookie);
        $this->assertEquals(Parser::COOKIE_AGE_DISCLAIMER_VALUE, $cookie->getValue());
    }

    public function testEmojiesAreRemoved(): void
    {
        $originalComment = new Comment(
            body: '👅',
            timestamp: '13 Years Ago',
            author: 'Gene Simmons',
            votes: '500'
        );

        $validComment = new Comment(
            body: ':tongue:',
            timestamp: '13 Years Ago',
            author: 'Gene Simmons',
            votes: '500'
        );

        $crawler = new Crawler($this->generatePageHtml([$originalComment]), 'https://www.pornhub.com/someRandomVideo');
        $httpBrowser = $this->createStub(HttpBrowser::class);
        $httpBrowser->method('request')->willReturn($crawler);

        $parser = new Parser(httpBrowser: $httpBrowser, maxCommentBodyLength: 25);
        $parser->randomVideo();

        $this->assertEquals([$validComment], $parser->getComments(translateEmojis: true));
				$this->assertEquals('https://www.pornhub.com/someRandomVideo', $parser->getPageUrl());
    }
}
