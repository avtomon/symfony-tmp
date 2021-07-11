<?php

declare(strict_types=1);

namespace TmpApp\Service;

use TmpApp\Helper\FileFinder;
use TmpApp\Helper\FileUrlGenerator;
use TmpApp\Infrastructure\Bus\Out\ImageNode;
use TmpApp\Message\Out\ProductImages;
use SplFileInfo;
use Symfony\Component\Finder\Finder;

class FileService
{
    private string $dirImagesSrc;

    private FileFinder $imageFinder;

    private FileUrlGenerator $imageUrlGenerator;

    public function __construct(string $dirImagesSrc, FileFinder $imageFinder, FileUrlGenerator $imageUrlGenerator)
    {
        $this->dirImagesSrc = $dirImagesSrc;
        $this->imageFinder = $imageFinder;
        $this->imageUrlGenerator = $imageUrlGenerator;
    }

    public function getAllDirectories(): Finder
    {
        return $this->imageFinder->getFilesDirectories($this->dirImagesSrc);
    }

    public function generateMessage(SplFileInfo $directory, string $productName): ProductImages
    {
        $message = $this->generateEmptyImagesMessage($directory);

        $this->imageUrlGenerator->resetIndex();
        foreach ($this->imageFinder->getFiles($directory->getRealPath()) as $file) {
            $image = new ImageNode(
                $this->imageUrlGenerator->getImageUrlAndRenameFile($file->getRealPath(), $productName)
            );
            $message->addImage($image);
        }

        return $message;
    }

    public function generateEmptyImagesMessage(SplFileInfo $directory): ProductImages
    {
        $id = $this->getProductId($directory);
        $message = new ProductImages();
        $message->setId($id);

        return $message;
    }


    public function getProductId(SplFileInfo $directory): int
    {
        return (int)strtr($directory->getPath(), [$this->dirImagesSrc => '', '/' => '',]);
    }
}