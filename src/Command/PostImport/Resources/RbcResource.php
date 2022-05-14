<?php

namespace App\Command\PostImport\Resources;

use App\Command\PostImport\Data\Post;
use App\Command\PostImport\Data\PostCollection;
use Symfony\Component\DomCrawler\Crawler;
use Symfony\Component\HttpClient\HttpClient;
use Symfony\Contracts\HttpClient\HttpClientInterface;

class RbcResource implements ResourceInterface
{
    private readonly HttpClientInterface $httpClient;

    private const RBC_POST_COUNT = 15;
    private const RBC_URL = 'https://nsk.rbc.ru';

    public function __construct()
    {
        $this->httpClient = HttpClient::create();
    }

    private function mathHash($param): string
    {
        return hash('sha256', $param);
    }

    private function parseFullPost(string $postUrl): Post
    {
        $rbkRequest = $this->httpClient->request('GET', $postUrl);
        $crawler = new Crawler($rbkRequest->getContent());

        $post = new Post();
        $post->title = $crawler->filter('.article__header__title-in')->innerText();
        $post->postAt = new \DateTimeImmutable($crawler->filter('.article__header__date')->attr('content'));

        $image = $crawler->filter('.article__main-image__image');

        if ($image->count()) {
            $post->imageUrl = $image->first()->attr('src');
        }

        $text = '';

        $annotations = $crawler->filter('.article__text__overview');

        if ($annotations->count()) {
            foreach ($annotations as $annotation) {
                $text .= $annotation->textContent;
            }
        }

        $crawler->filter('.article__text > *')->each(function (Crawler $item) use (&$text) {
            if (in_array($item->nodeName(), ['p', 'h2', 'li'])) {
                $text .= $item->text();
            }

            $li = $item->children('li');

            if ($li->count()) {
                $li->each(function (Crawler $item) use (&$text) {
                    $text .= $item->text();
                });
            }
        });

        $post->text = trim($text);
        $post->hash = $this->mathHash($postUrl);

        return $post;
    }

    public function execute(): PostCollection
    {
        $rbkRequest = $this->httpClient->request('GET', self::RBC_URL);

        $postCollection = new PostCollection();

        $crawler = new Crawler($rbkRequest->getContent());

        $crawler->filter('.js-news-feed-list')->children('.news-feed__item')->each(function (Crawler $item) use (&$postCollection) {
            if ($postCollection->count() >= self::RBC_POST_COUNT) {
                return;
            }

            $postLink = $item->attr('href');
            if (is_null($postLink)) {
                return;
            }

            if (str_contains($postLink, '.html') || str_contains($postLink, 'utm_source')) {
                return;
            }

            $post = $this->parseFullPost($item->attr('href'));
            $postCollection->add($post);
        });


        return $postCollection;
    }
}