<?php

namespace App\Entity;

use App\Repository\PostRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: PostRepository::class)]
class Post
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private $id;

    #[ORM\Column(type: 'text')]
    private $title;

    #[ORM\Column(type: 'text')]
    private $content;

    #[ORM\Column(type: 'string', unique: true)]
    private $hash;

    #[ORM\Column(type: 'string', nullable: true)]
    private $image_url;

    #[ORM\Column(type: 'datetime_immutable', nullable: true)]
    private $post_at;

    #[ORM\Column(type: 'datetime_immutable', nullable: true)]
    private $edit_at;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getTitle(): ?string
    {
        return $this->title;
    }

    public function setTitle(string $title): self
    {
        $this->title = $title;

        return $this;
    }

    public function getContent(): ?string
    {
        return $this->content;
    }

    public function setContent(string $content): self
    {
        $this->content = $content;

        return $this;
    }

    public function getImageUrl(): ?string
    {
        return $this->image_url;
    }

    public function setImageUrl(?string $image_url): self
    {
        $this->image_url = $image_url;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getHash(): string
    {
        return $this->hash;
    }

    /**
     * @param mixed $hash
     */
    public function setHash(string $hash): void
    {
        $this->hash = $hash;
    }

    public function getPostAt(): ?\DateTimeImmutable
    {
        return $this->post_at;
    }

    public function setPostAt(?\DateTimeImmutable $post_at): self
    {
        $this->post_at = $post_at;

        return $this;
    }

    public function getEditAt(): ?\DateTimeImmutable
    {
        return $this->edit_at;
    }

    public function setEditAt(?\DateTimeImmutable $edit_at): self
    {
        $this->edit_at = $edit_at;

        return $this;
    }
}
