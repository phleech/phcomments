<?php

declare(strict_types=1);

namespace Tests;

use PHComments\Comment;
use PHPUnit\Framework\TestCase;

final class CommentTest extends TestCase
{
    public function testProvidedBodyAttributeIsPresentInJsonSerialize(): void
    {
        $body = 'This is a body';
        $comment = new Comment(body: $body);
        $serialized = $comment->jsonSerialize();
        $this->assertArrayHasKey('body', $serialized);
        $this->assertEquals($body, $serialized['body']);
    }

    public function testDefaultBodyAttributeIsPresentInJsonSerialize(): void
    {
        $comment = new Comment();
        $serialized = $comment->jsonSerialize();
        $this->assertArrayHasKey('body', $serialized);
        $this->assertEquals('', $serialized['body']);
    }

    public function testProvidedTimestampAttributeIsPresentInJsonSerialize(): void
    {
        $timestamp = 'This is a timestamp';
        $comment = new Comment(timestamp: $timestamp);
        $serialized = $comment->jsonSerialize();
        $this->assertArrayHasKey('timestamp', $serialized);
        $this->assertEquals($timestamp, $serialized['timestamp']);
    }

    public function testDefaultTimestampAttributeIsPresentInJsonSerialize(): void
    {
        $comment = new Comment();
        $serialized = $comment->jsonSerialize();
        $this->assertArrayHasKey('timestamp', $serialized);
        $this->assertEquals('', $serialized['timestamp']);
    }

    public function testProvidedAuthorAttributeIsPresentInJsonSerialize(): void
    {
        $author = 'This is an author';
        $comment = new Comment(author: $author);
        $serialized = $comment->jsonSerialize();
        $this->assertArrayHasKey('author', $serialized);
        $this->assertEquals($author, $serialized['author']);
    }

    public function testDefaultAuthorAttributeIsPresentInJsonSerialize(): void
    {
        $comment = new Comment();
        $serialized = $comment->jsonSerialize();
        $this->assertArrayHasKey('author', $serialized);
        $this->assertEquals('', $serialized['author']);
    }

    public function testProvidedVotesAttributeIsPresentInJsonSerialize(): void
    {
        $votes = 'This is a vote';
        $comment = new Comment(votes: $votes);
        $serialized = $comment->jsonSerialize();
        $this->assertArrayHasKey('votes', $serialized);
        $this->assertEquals($votes, $serialized['votes']);
    }

    public function testDefaultVotesAttributeIsPresentInJsonSerialize(): void
    {
        $comment = new Comment();
        $serialized = $comment->jsonSerialize();
        $this->assertArrayHasKey('votes', $serialized);
        $this->assertEquals('', $serialized['votes']);
    }

    public function testProvidedBodyAttributeIsUsedInGetBody(): void
    {
        $body = 'This is a body';
        $comment = new Comment(body: $body);
        $this->assertEquals($body, $comment->getBody());
    }

    public function testProvidedAuthorAttributeIsUsedInGetBody(): void
    {
        $author = 'This is an author';
        $comment = new Comment(author: $author);
        $this->assertEquals($author, $comment->getAuthor());
    }
}
