<?php

declare(strict_types=1);

namespace Tests;

use PHComments\Comment;
use PHPUnit\Framework\TestCase;

final class CommentTest extends TestCase
{
    public function test_provided_body_attribute_is_present_in_json_serialize(): void
    {
        $body = 'This is a body';
        $comment = new Comment(body: $body);
        $serialized = $comment->jsonSerialize();
        $this->assertArrayHasKey('body', $serialized);
        $this->assertEquals($body, $serialized['body']);
    }

    public function test_default_body_attribute_is_present_in_json_serialize(): void
    {
        $comment = new Comment;
        $serialized = $comment->jsonSerialize();
        $this->assertArrayHasKey('body', $serialized);
        $this->assertEquals('', $serialized['body']);
    }

    public function test_provided_timestamp_attribute_is_present_in_json_serialize(): void
    {
        $timestamp = 'This is a timestamp';
        $comment = new Comment(timestamp: $timestamp);
        $serialized = $comment->jsonSerialize();
        $this->assertArrayHasKey('timestamp', $serialized);
        $this->assertEquals($timestamp, $serialized['timestamp']);
    }

    public function test_default_timestamp_attribute_is_present_in_json_serialize(): void
    {
        $comment = new Comment;
        $serialized = $comment->jsonSerialize();
        $this->assertArrayHasKey('timestamp', $serialized);
        $this->assertEquals('', $serialized['timestamp']);
    }

    public function test_provided_author_attribute_is_present_in_json_serialize(): void
    {
        $author = 'This is an author';
        $comment = new Comment(author: $author);
        $serialized = $comment->jsonSerialize();
        $this->assertArrayHasKey('author', $serialized);
        $this->assertEquals($author, $serialized['author']);
    }

    public function test_default_author_attribute_is_present_in_json_serialize(): void
    {
        $comment = new Comment;
        $serialized = $comment->jsonSerialize();
        $this->assertArrayHasKey('author', $serialized);
        $this->assertEquals('', $serialized['author']);
    }

    public function test_provided_votes_attribute_is_present_in_json_serialize(): void
    {
        $votes = 'This is a vote';
        $comment = new Comment(votes: $votes);
        $serialized = $comment->jsonSerialize();
        $this->assertArrayHasKey('votes', $serialized);
        $this->assertEquals($votes, $serialized['votes']);
    }

    public function test_default_votes_attribute_is_present_in_json_serialize(): void
    {
        $comment = new Comment;
        $serialized = $comment->jsonSerialize();
        $this->assertArrayHasKey('votes', $serialized);
        $this->assertEquals('', $serialized['votes']);
    }

    public function test_provided_body_attribute_is_used(): void
    {
        $body = 'This is a body';
        $comment = new Comment(body: $body);
        $this->assertEquals($body, $comment->body);
    }

    public function test_provided_author_attribute_is_used(): void
    {
        $author = 'This is an author';
        $comment = new Comment(author: $author);
        $this->assertEquals($author, $comment->author);
    }
}
