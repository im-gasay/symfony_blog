<?php

namespace App\Command\PostImport\Data;

class PostCollection
{
    private array $items = [];

    /** add post
     * @param Post $post
     */
    public function add(Post $post)
    {
        $this->items[] = $post;
    }

    /** post collection
     * @return array<Post>
     */
    public function items(): array
    {
        return $this->items;
    }

    /** post count
     * @return int
     */
    public function count(): int
    {
        return count($this->items);
    }
}