<?php

declare(strict_types=1);

namespace App\Console;

use App\Application\Queue\QueueInterface;
use App\Domain\Image\Image;
use App\Domain\Image\Status;
use App\Infrastructure\ImageGenerator\BaseImageGenerator;
use Doctrine\ORM\EntityManagerInterface;

class App
{
    private QueueInterface $queue;
    private BaseImageGenerator $imageGenerator;
    private EntityManagerInterface $entityManager;
    private \Psr\Log\LoggerInterface $logger;

    public function __construct(QueueInterface $queue, BaseImageGenerator $imageGener, EntityManagerInterface $entityManager, \Psr\Log\LoggerInterface $logger)
    {
        $this->queue = $queue;
        $this->imageGenerator = $imageGener;
        $this->entityManager = $entityManager;
        $this->logger = $logger;
        $this->logger->debug('App created');
    }

    public function run()
    {
        $message = $this->queue->getMessageOrNull();
        $this->logger->debug('Trying to get message');
        if (!$message) {
            return;
        }
        $this->logger->debug('Generating image for ' . $message->description);
        $imagePath = $this->imageGenerator->generateImage($message->description);
        $image = $this->entityManager->find(Image::class, $message->uuid);
        $image->setPath($imagePath);
        $image->setStatus(Status::GENERATED->value);
        $this->entityManager->flush();
        $this->logger->debug('Generated ' . $message->uuid);
    }
}
