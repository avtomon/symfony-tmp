<?php

declare(strict_types=1);

namespace TmpApp\Controller;

use TmpApp\DTO\S3FileDto;
use TmpApp\Listener\FileEventListener;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;

class FileUpdatedController extends AbstractController
{
    private FileEventListener $fileEventListener;

    public function __construct(FileEventListener $fileEventListener)
    {
        $this->fileEventListener = $fileEventListener;
    }

    /**
     * @Route("/file/create", name="file-new", methods="POST")
     *
     * @param S3FileDto $dto
     *
     * @throws \Doctrine\DBAL\Exception
     */
    public function createFile(S3FileDto $dto): void
    {
        $this->fileEventListener->onCreateOrUpdate("{$dto->getBucket()}/{$dto->getObjectName()}");
    }

    /**
     * @Route("/file/remove", name="file-remove", methods="POST")
     *
     * @param S3FileDto $dto
     */
    public function removeFile(S3FileDto $dto): void
    {
        $this->fileEventListener->onDelete("{$dto->getBucket()}/{$dto->getObjectName()}");
    }
}