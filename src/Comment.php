<?php

namespace PHComments;

use JsonSerializable;

class Comment implements JsonSerializable
{
    public const DEFAULT_MAX_BODY_LENGTH = 200;

    public const DEFAULT_MAX_AUTHOR_LENGTH = 15;

    public function __construct(
        private string $body = '',
        private string $timestamp = '',
        private string $author = '',
        private string $votes = '',
    ) {
    }

    public function jsonSerialize(): mixed
    {
        return get_object_vars($this);
    }

    public function getBody(): string
    {
        return $this->body;
    }

    public function getAuthor(): string
    {
        return $this->author;
    }
}
