<?php

namespace App\Command;

use App\Command\PostImport\Data\PostCollection;
use App\Command\PostImport\Exceptions\ResourceNotFoundException;
use App\Command\PostImport\Resources\RbcResource;
use App\Command\PostImport\Resources\ResourceInterface;
use App\Entity\Post as PostEntity;
use App\Repository\PostRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(
    name: 'app:import-posts',
    description: 'Import posts from news sites'
)]
class PostImportCommand extends Command
{
    private const AVAILABLE_RESOURCES = [
        'rbc' => RbcResource::class,
    ];

    public function __construct(
        private readonly EntityManagerInterface $entityManager,
        private readonly PostRepository $postRepository
    )
    {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this->addOption('from', null, InputOption::VALUE_OPTIONAL);
    }

    private function getResourceNamespaceFromInputOption(string $resourceKey): string
    {
        if (!array_key_exists($resourceKey, self::AVAILABLE_RESOURCES)) {
            throw new ResourceNotFoundException("Resource $resourceKey not found!");
        }

        return self::AVAILABLE_RESOURCES[$resourceKey];
    }

    private function createPostFromResource(PostCollection $postCollection)
    {
        foreach ($postCollection->items() as $post) {
            $existPost = $this->postRepository->findOneByHash($post->hash);

            if (!is_null($existPost)) {
                if ($existPost->getEditAt() != $post->editAt) {
                    $existPost->setTitle($post->title);
                    $existPost->setImageUrl($post->imageUrl);
                    $existPost->setContent($post->text);
                    $existPost->setEditAt($post->editAt);
                    $this->entityManager->persist($existPost);
                }
                continue;
            }

            $postEntity = new PostEntity();
            $postEntity->setTitle($post->title);
            $postEntity->setImageUrl($post->imageUrl);
            $postEntity->setContent($post->text);
            $postEntity->setPostAt($post->postAt);
            $postEntity->setHash($post->hash);
            $postEntity->setEditAt($post->editAt);

            $this->entityManager->persist($postEntity);
        }
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $fromOption = $input->getOption('from');

        $importsFrom = '';

        if (!is_null($fromOption)) {
            try {
                /** @var ResourceInterface $resource */
                $resource = new ($this->getResourceNamespaceFromInputOption($input->getOption('from')));
            } catch (ResourceNotFoundException $e) {
                $output->writeln($e->getMessage());
                return Command::FAILURE;
            }

            $importsFrom = $fromOption;

            $this->createPostFromResource($resource->execute());
        } else {
            foreach (self::AVAILABLE_RESOURCES as $resourceKey => $resourceNamespace) {
                /** @var ResourceInterface $resource */
                $resource = new $resourceNamespace;
                $this->createPostFromResource($resource->execute());
                $importsFrom .= $resourceKey . ', ';
            }
        }

        $this->entityManager->flush();


        $output->writeln(rtrim("Success import news from $importsFrom", ', '));

        return Command::SUCCESS;
    }
}
