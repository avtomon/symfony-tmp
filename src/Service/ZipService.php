<?php

declare(strict_types=1);

namespace TmpApp\Service;

use TmpApp\Exception\ZipException;
use TmpApp\Helper\FileFinder;
use ZipArchive;

class ZipService
{
    private FileFinder $fileFinder;

    public function __construct(FileFinder $fileFinder)
    {
        $this->fileFinder = $fileFinder;
    }

    private function getArchive(string $path): ZipArchive
    {
        static $zip;
        if (!$zip) {
            $zip = new ZipArchive();
            if (true !== $zip->open("$path/" . $this->fileFinder->getArchiveName($path), ZipArchive::CREATE)) {
                throw new ZipException('Ошибка открытия архива.');
            }
        }

        return $zip;
    }

    public function addFilesFromDir(string $path): array
    {
        $result = [];
        foreach ($this->fileFinder->getFilesFromDirectory($path) as $file) {
            $result[] = $this->addFile($file);
            unlink($file->getRealPath());
        }

        return $result;
    }

    public function addFile(string $filePath): string
    {
        $fileName = basename($filePath);
        if (false === $this->getArchive(dirname($filePath))->addFile($filePath, $fileName)) {
            throw new ZipException('Добавление файла к архиву не удалось.');
        }

        return $fileName;
    }

    public function removeFile(string $path, string $fileName): void
    {
        if (false === $this->getArchive(dirname($path))->deleteName($fileName)) {
            throw new ZipException('Удаление файла из архива не удалось.');
        }
    }
}