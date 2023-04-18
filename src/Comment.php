<?php

namespace PHComments;

use JsonSerializable;

class Comment implements JsonSerializable
{
    public const DEFAULT_MAX_BODY_LENGTH = 200;

    public const DEFAULT_MAX_AUTHOR_LENGTH = 15;

    public function __construct(
        public readonly string $body = '',
        public readonly string $timestamp = '',
        public readonly string $author = '',
        public readonly string $votes = '',
    ) {
    }

    public function jsonSerialize(): mixed
    {
        return get_object_vars($this);
    }
}
