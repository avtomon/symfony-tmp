<?php

declare(strict_types=1);

namespace TmpApp\Helper;

class FileUrlGenerator
{
    public const IMAGE_NAME_POSTFIX = '-kupit-onlajn';

    private string $offersImagesUrl;

    private string $mainImageName;

    private int $index = 0;

    public function __construct(string $offersImagesUrl, string $mainImageName)
    {
        $this->offersImagesUrl = $offersImagesUrl;
        $this->mainImageName = $mainImageName;
    }

    public function resetIndex(): void
    {
        $this->index = 0;
    }

    private function isMainImage(string $imagePath): bool
    {
        $fileName = pathinfo($imagePath, PATHINFO_FILENAME);
        return $fileName === $this->mainImageName || false !== strpos($fileName, "--$this->mainImageName");
    }

    private function getPostfix(string $imagePath): string
    {
        return $this->isMainImage($imagePath) ? "--$this->mainImageName" : "--$this->index";
    }

    public function getImageUrlAndRenameFile(string $imagePath, string $productName): string
    {
        $newImagePath = $this->getNewImagePath($imagePath, $productName);
        if ($newImagePath !== $imagePath) {
            rename($imagePath, $newImagePath);
        }

        return $newImagePath . '?' . time();
    }

    public function getNewImagePath(string $imagePath, string $productName): string
    {
        $fileName = basename($imagePath);
        $ext = pathinfo($imagePath, PATHINFO_EXTENSION);
        $path = str_replace($fileName, '', $imagePath);

        return $this->offersImagesUrl
            . $path
            . static::createPrefixFromProductName($productName)
            . static::IMAGE_NAME_POSTFIX
            . $this->getPostfix($imagePath)
            . $ext;
    }

    private static function createPrefixFromProductName(string $string): string
    {
        $prefix = '';
        if (!empty($string)) {
            $prefix = mb_strtolower($prefix);
            $prefix = str_replace('\'', '', $prefix);
            $prefix = trim(preg_replace('/[^-_a-z0-9]+/iu', '-', $prefix), " \t\n\r\0\x0B-");
        }

        return $prefix;
    }
}