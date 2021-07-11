<?php

declare(strict_types=1);

namespace TmpApp\Message\Out;

use JMS\Serializer\Annotation as JMS;
use TmpApp\Infrastructure\Bus\Out\FileNode;

/**
 * @JMS\ExclusionPolicy("none")
 * @JMS\XmlRoot("product-files")
 */
class ProductImages
{
    /**
     * @JMS\Type("integer")
     * @JMS\Groups({"esb_out"})
     */
    private int $id;

    /**
     * @JMS\XmlList(entry="image")
     * @JMS\Type("array<TmpApp\Infrastructure\Esb\Out\FileNode>")
     * @JMS\Groups({"esb_out"})
     */
    private array $files;

    /**
     * @return int
     */
    public function getId(): int
    {
        return $this->id;
    }

    /**
     * @param int $id
     */
    public function setId(int $id): void
    {
        $this->id = $id;
    }

    /**
     * @return FileNode[]
     */
    public function getFiles() : array
    {
        return $this->files;
    }

    /**
     * @param FileNode[] $files
     */
    public function setFiles(array $files) : void
    {
        $this->files = $files;
    }

    public function addFile(FileNode $file): void
    {
        $this->files[] = $file;
    }
}