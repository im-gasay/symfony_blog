<?php

namespace App\Command\PostImport\Data;

use DateTimeImmutable;

class Post
{
    public function __construct(
        public ?string $title = null,
        public ?string $text = null,
        public ?string $imageUrl = null,
        public ?string $hash = null,
        public ?DateTimeImmutable $postAt = null,
        public ?DateTimeImmutable $editAt = null,
    )
    {
    }

}