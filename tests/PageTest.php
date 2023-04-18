<?php

declare(strict_types=1);

namespace Tests;

use Exception;
use PHComments\Page;
use PHPUnit\Framework\TestCase;

final class PageTest extends TestCase
{
    public function testSetViewKeySetsCorrectURL(): void
    {
        $basePath = 'https://www.phleech.co.uk';
        $viewKey = 'abcdef1234567890';
        $page = new Page(basePath: $basePath);
        $page->setViewKey(viewKey: $viewKey);

        $this->assertEquals(sprintf('%s/view_video.php?viewkey=%s', $basePath, $viewKey), $page->getUrl());
    }

    public function testSetPageUrlSetsCorrectURL(): void
    {
        $basePath = 'https://www.phleech.co.uk';
        $url = '/this/is/a/url.php?foo=bar';
        $page = new Page(basePath: $basePath);
        $page->setPageUrl(url: $url);

        $this->assertEquals(sprintf('%s/%s', $basePath, $url), $page->getUrl());
    }

    public function testRandomVideoSetsCorrectURL(): void
    {
        $basePath = 'https://www.phleech.co.uk';
        $page = new Page(basePath: $basePath);
        $page->randomVideo();

        $this->assertEquals(sprintf('%s/video/random', $basePath), $page->getUrl());
    }

    public function testExcessSlashesAreRemovedFromUrls(): void
    {
        $basePath = 'https://www.phleech.co.uk////';
        $url = '///this/is/a/url.php?foo=bar';
        $page = new Page(basePath: $basePath);
        $page->setPageUrl(url: $url);

        $this->assertEquals(sprintf('%s/%s', $basePath, $url), $page->getUrl());
    }

    public function testSlashesAreAddedToUrls(): void
    {
        $basePath = 'https://www.phleech.co.uk';
        $url = 'this/is/a/url.php?foo=bar';
        $page = new Page(basePath: $basePath);
        $page->setPageUrl(url: $url);

        $this->assertEquals(sprintf('%s/%s', $basePath, $url), $page->getUrl());
    }

    public function testProvidedBasePathIsUsedInSetUrl(): void
    {
        $basePath = 'https://www.phleech.co.uk';
        $page = new Page(basePath: $basePath);
        $page->setPageUrl(url: '');

        $this->assertStringStartsWith($basePath, $page->getUrl());

    }

    public function testDefaultBasePathIsUsedInSetUrl(): void
    {
        $page = new Page();
        $page->setPageUrl(url: '');

        $this->assertStringStartsWith('https://www.pornhub.com', $page->getUrl());

    }

    public function testExceptionIsThrownIfGetUrlIsCalledBeforeSetUrl(): void
    {
        $this->expectException(Exception::class);
        $page = new Page();
        $page->getUrl();
    }
}
