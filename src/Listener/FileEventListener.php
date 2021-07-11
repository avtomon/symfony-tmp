<?php

declare(strict_types=1);

namespace TmpApp\Listener;

use TmpApp\Exception\ProductNotFound;
use TmpApp\Service\ProductService;
use TmpApp\Service\FileService;
use TmpApp\Service\ZipService;
use SplFileInfo;

class FileEventListener
{
    private FileService $updatedFileService;

    private Bus $bus;

    private ProductService $productService;

    private ZipService $zipService;

    private string $queueName;

    private bool $isS3StorageUse;

    public function __construct(
        FileService $updatedFileService,
        Bus $bus,
        ProductService $productService,
        ZipService $zipService,
        string $queueName,
        bool $isS3StorageUse
    )
    {
        $this->updatedFileService = $updatedFileService;
        $this->bus = $bus;
        $this->productService = $productService;
        $this->zipService = $zipService;
        $this->queueName = $queueName;
        $this->isS3StorageUse = $isS3StorageUse;
    }

    /**
     * @param string $realPath
     *
     * @throws \Doctrine\DBAL\Exception
     */
    public function onCreateOrUpdate(string $realPath): void
    {
        $dir = new SplFileInfo($realPath);
        if ($dir->isFile()) {
            return;
        }

        $productName = $this->productService->getNameById($this->updatedFileService->getProductId($dir));
        if (!$productName) {
            throw new ProductNotFound();
        }

        !$this->isS3StorageUse && $this->zipService->addFilesFromDir($dir->getRealPath());

        $message = $this->updatedFileService->generateMessage($dir, $productName);
        $this->bus->send($this->queueName, $message);
    }

    public function onDelete(string $realPath): void
    {
        $fileOrDir = new SplFileInfo($realPath);
        if ($fileOrDir->isFile()) {
            !$this->isS3StorageUse
            && $this->zipService->removeFile(dirname($fileOrDir->getRealPath()), basename($fileOrDir->getRealPath()));
            return;
        }

        $message = $this->updatedFileService->generateEmptyImagesMessage($fileOrDir);
        $this->bus->send($this->queueName, $message);
    }
}