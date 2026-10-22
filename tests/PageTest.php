<?php

declare(strict_types=1);

namespace Tests;

use Exception;
use PHComments\Page;
use PHPUnit\Framework\TestCase;

final class PageTest extends TestCase
{
    public function test_set_view_key_sets_correct_url(): void
    {
        $basePath = 'https://www.phleech.co.uk';
        $viewKey = 'abcdef1234567890';
        $page = new Page(basePath: $basePath);
        $page->setViewKey(viewKey: $viewKey);

        $this->assertEquals(sprintf('%s/view_video.php?viewkey=%s', $basePath, $viewKey), $page->getUrl());
    }

    public function test_set_page_url_sets_correct_url(): void
    {
        $basePath = 'https://www.phleech.co.uk';
        $url = '/this/is/a/url.php?foo=bar';
        $page = new Page(basePath: $basePath);
        $page->setPageUrl(url: $url);

        $this->assertEquals(sprintf('%s/%s', $basePath, $url), $page->getUrl());
    }

    public function test_random_video_sets_correct_url(): void
    {
        $basePath = 'https://www.phleech.co.uk';
        $page = new Page(basePath: $basePath);
        $page->randomVideo();

        $this->assertEquals(sprintf('%s/video/random', $basePath), $page->getUrl());
    }

    public function test_excess_slashes_are_removed_from_urls(): void
    {
        $basePath = 'https://www.phleech.co.uk////';
        $url = '///this/is/a/url.php?foo=bar';
        $page = new Page(basePath: $basePath);
        $page->setPageUrl(url: $url);

        $this->assertEquals(sprintf('%s/%s', $basePath, $url), $page->getUrl());
    }

    public function test_slashes_are_added_to_urls(): void
    {
        $basePath = 'https://www.phleech.co.uk';
        $url = 'this/is/a/url.php?foo=bar';
        $page = new Page(basePath: $basePath);
        $page->setPageUrl(url: $url);

        $this->assertEquals(sprintf('%s/%s', $basePath, $url), $page->getUrl());
    }

    public function test_provided_base_path_is_used_in_set_url(): void
    {
        $basePath = 'https://www.phleech.co.uk';
        $page = new Page(basePath: $basePath);
        $page->setPageUrl(url: '');

        $this->assertStringStartsWith($basePath, $page->getUrl());

    }

    public function test_default_base_path_is_used_in_set_url(): void
    {
        $page = new Page;
        $page->setPageUrl(url: '');

        $this->assertStringStartsWith('https://www.pornhub.com', $page->getUrl());

    }

    public function test_exception_is_thrown_if_get_url_is_called_before_set_url(): void
    {
        $this->expectException(Exception::class);
        $page = new Page;
        $page->getUrl();
    }
}
