<?php

declare(strict_types=1);

namespace TmpApp\Infrastructure\Bus\Out;

use JMS\Serializer\Annotation as JMS;

/**
 * @JMS\ExclusionPolicy("none")
 */
class ImageNode
{
    /**
     * @JMS\Type("string")
     * @JMS\Groups({"esb_out"})
     */
    private string $url;
    
    public function __construct(string $url)
    {
        $this->url = $url;
    }

    public function getUrl(): string
    {
        return $this->url;
    }

    public function setUrl(string $url): void
    {
        $this->url = $url;
    }
}