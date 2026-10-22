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
    public function test_parser_calls_correct_page_method_when_random_video_method_is_called(): void
    {
        $page = $this->createMock(Page::class);
        $page->expects($this->once())->method('randomVideo');
        $parser = new Parser(page: $page);
        $parser->randomVideo();
    }

    public function test_random_video_method_returns_self(): void
    {
        $parser = new Parser;
        $this->assertInstanceOf(Parser::class, $parser->randomVideo());
    }

    public function test_parser_calls_correct_page_method_when_set_view_key_method_is_called(): void
    {
        $viewKey = 'abcdef1234567890';
        $page = $this->createMock(Page::class);
        $page->expects($this->once())->method('setViewKey')->with($this->identicalTo($viewKey));
        $parser = new Parser(page: $page);
        $parser->setViewKey($viewKey);
    }

    public function test_set_view_key_method_returns_self(): void
    {
        $parser = new Parser;
        $this->assertInstanceOf(Parser::class, $parser->setViewKey(viewKey: 'abcdef1234567890'));
    }

    public function test_parser_calls_correct_page_method_when_set_page_url_method_is_called(): void
    {
        $url = 'this/is/a/url.php?foo=bar';
        $page = $this->createMock(Page::class);
        $page->expects($this->once())->method('setPageUrl')->with($this->identicalTo($url));
        $parser = new Parser(page: $page);
        $parser->setPageUrl($url);
    }

    public function test_set_page_url_method_returns_self(): void
    {
        $parser = new Parser;
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

    public function test_get_comments_defaults_to_calling_parse_first(): void
    {
        $comments = [
            new Comment(
                body: 'This is a comment body',
                timestamp: '1 year ago',
                author: 'Randy Starbucks',
                votes: '12'
            ),
        ];

        $crawler = new Crawler($this->generatePageHtml($comments));

        $httpBrowser = $this->createStub(HttpBrowser::class);
        $httpBrowser->method('request')->willReturn($crawler);

        $parser = new Parser(httpBrowser: $httpBrowser);
        $parser->randomVideo();

        $this->assertEquals($comments, $parser->getComments());
    }

    public function test_parse_is_not_called_when_parse_false_is_passed_into_get_comments(): void
    {
        $comments = [
            new Comment(
                body: 'This is a body',
                timestamp: '1 Year Ago',
                author: 'Billy Yenzen',
                votes: '12'
            ),
        ];

        $crawler = new Crawler($this->generatePageHtml($comments));

        $httpBrowser = $this->createStub(HttpBrowser::class);
        $httpBrowser->method('request')->willReturn($crawler);

        $parser = new Parser(httpBrowser: $httpBrowser);
        $parser->randomVideo();

        $this->assertEmpty($parser->getComments(parse: false));
    }

    public function test_only_comments_with_bodies_larger_than_max_comment_body_length_are_removed(): void
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

        $crawler = new Crawler($this->generatePageHtml([$invalidComment, $validComment]));
        $httpBrowser = $this->createStub(HttpBrowser::class);
        $httpBrowser->method('request')->willReturn($crawler);

        $parser = new Parser(httpBrowser: $httpBrowser, maxCommentBodyLength: 25);
        $parser->randomVideo();

        $this->assertEquals([$validComment], $parser->getComments());
    }

    public function test_default_max_comment_body_length_is_used_if_none_is_supplied_to_parser_constructor(): void
    {
        $parser = new Parser;

        $this->assertEquals(200, $parser->maxCommentBodyLength);
    }

    public function test_only_comments_with_authors_larger_than_max_comment_author_length_are_removed(): void
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

        $crawler = new Crawler($this->generatePageHtml([$invalidComment, $validComment]));
        $httpBrowser = $this->createStub(HttpBrowser::class);
        $httpBrowser->method('request')->willReturn($crawler);

        $parser = new Parser(httpBrowser: $httpBrowser, maxCommentAuthorLength: 15);
        $parser->randomVideo();

        $this->assertEquals([$validComment], $parser->getComments());
    }

    public function test_default_max_comment_author_length_is_used_if_none_is_supplied_to_parser_constructor(): void
    {
        $parser = new Parser;

        $this->assertEquals(15, $parser->maxCommentAuthorLength);
    }

    public function test_age_disclaimer_cookie_is_present_on_request(): void
    {
        $cookieJar = new CookieJar;
        $httpBrowser = $this->createStub(HttpBrowser::class);
        $httpBrowser->method('request')->willReturn(new Crawler);
        $httpBrowser->method('getCookieJar')->willReturn($cookieJar);

        $parser = new Parser(httpBrowser: $httpBrowser);
        $parser->setPageUrl('');
        $parser->getComments();

        $cookie = $cookieJar->get(Parser::COOKIE_AGE_DISCLAIMER_NAME);
        $this->assertInstanceOf(Cookie::class, $cookie);
        $this->assertEquals(Parser::COOKIE_AGE_DISCLAIMER_VALUE, $cookie->getValue());
    }
}
