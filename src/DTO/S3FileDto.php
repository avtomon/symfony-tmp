<?php

declare(strict_types=1);

namespace TmpApp\DTO;

use Symfony\Component\Validator\Constraints as Assert;
use JMS\Serializer\Annotation as JMS;

/**
 * @JMS\ExclusionPolicy("none")
 */
class S3FileDto extends AbstractRequestDto
{
    /**
     * @var string
     *
     * @JMS\Type("string")
     *
     * @Assert\Type(type="string", groups={"type"})
     * @Assert\NotBlank()
     */
    private $bucket;

    /**
     * @var string
     *
     * @JMS\Type("string")
     *
     * @Assert\Type(type="string", groups={"type"})
     * @Assert\NotBlank()
     */
    private $objectName;

    public function getBucket()
    {
        return $this->bucket;
    }

    public function getObjectName()
    {
        return $this->objectName;
    }
}
