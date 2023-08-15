<?php

namespace OpenPublicMedia\EngagingNetworksServices\Rest\Exception;

use RuntimeException;

/**
 * Exception thrown when a non-existent supporter field is requested.
 */
class SupporterFieldNotFoundException extends RuntimeException
{

    public function __construct(private readonly string $fieldName)
    {
        parent::__construct("Field '$this->fieldName' not found in supporter fields.");
    }

    public function getFieldName(): ?string
    {
        return $this->fieldName;
    }
}
