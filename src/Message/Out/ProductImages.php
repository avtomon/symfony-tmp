<?php

declare(strict_types=1);

namespace TmpApp\Message\Out;

use JMS\Serializer\Annotation as JMS;
use TmpApp\Infrastructure\Bus\Out\ImageNode;

/**
 * @JMS\ExclusionPolicy("none")
 * @JMS\XmlRoot("product-images")
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
     * @JMS\Type("array<TmpApp\Infrastructure\Esb\Out\ImageNode>")
     * @JMS\Groups({"esb_out"})
     */
    private array $images;

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
     * @return ImageNode[]
     */
    public function getImages() : array
    {
        return $this->images;
    }

    /**
     * @param ImageNode[] $images
     */
    public function setImages(array $images) : void
    {
        $this->images = $images;
    }

    public function addImage(ImageNode $image): void
    {
        $this->images[] = $image;
    }
}