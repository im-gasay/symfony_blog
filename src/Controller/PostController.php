<?php

namespace App\Controller;

use App\Repository\PostRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class PostController extends AbstractController
{


    #[Route('/', name: 'app_posts')]
    public function index(PostRepository $postRepository): Response
    {
        $posts = $postRepository->findAll();

        return $this->render('post/index.html.twig', [
            'posts' => $posts
        ]);
    }

    #[Route('/posts/{id}', name: 'app_post')]
    public function post(int $id, PostRepository $postRepository)
    {
        $post = $postRepository->find($id);

        if (is_null($post)) {
            return $this->render('post/404.html.twig');
        }

        return $this->render('post/page.html.twig', [
            'post' => $post
        ]);
    }
}
