<?php

declare(strict_types=1);

namespace TmpApp\Helper;

use Symfony\Component\Finder\Finder;

class FileFinder
{
    private Finder $finder;

    /**
     * @var string[]
     */
    private array $allowFilesMask;

    private int $updatesListenerDirectoryLevel;

    public function __construct(Finder $finder, string $allowFilesMask, int $updatesListenerDirectoryLevel)
    {
        $this->finder = $finder;
        $this->allowFilesMask = explode(',', $allowFilesMask);
        $this->updatesListenerDirectoryLevel = $updatesListenerDirectoryLevel;
    }

    /**
     * Найти все файлы
     *
     * @param string $parentDir - путь к родительскому каталогу
     *
     * @return Finder
     */
    public function getFiles(string $parentDir): Finder
    {
        return $this->finder
            ->files()
            ->depth($this->updatesListenerDirectoryLevel)
            ->followLinks()
            ->in($parentDir)
            ->name($this->allowFilesMask)
            ->notName(basename($parentDir) . '.zip');
    }

    /**
     * Найти все директории
     *
     * @param string $parentDir - путь к родительскому каталогу
     *
     * @return Finder
     */
    public function getFilesDirectories(string $parentDir): Finder
    {
        return $this->finder
            ->depth($this->updatesListenerDirectoryLevel - 1)
            ->directories()
            ->followLinks()
            ->in($parentDir)
            ->name($this->allowFilesMask);
    }

    public function getFilesFromDirectory(string $path): Finder
    {
        static $files;
        if ($files) {
            $files = $this->finder
                ->files()
                ->followLinks()
                ->in($path)
                ->notName($this->getArchiveName($path));
        }

        return $files;
    }

    public function getArchiveName(string $path): string
    {
        return basename($path) . '.zip';
    }
}