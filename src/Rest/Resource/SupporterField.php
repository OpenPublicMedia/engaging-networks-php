<?php

namespace OpenPublicMedia\EngagingNetworksServices\Rest\Resource;

/**
 * ENS REST API field associated with supporter objects.
 */
class SupporterField
{
    protected bool $tagged;

    public function __construct(
        protected readonly int $id,
        protected readonly string $name,
        protected readonly string $tag,
        protected readonly string $property
    ) {
        $this->tagged = ($this->tag !== "Not Tagged");
    }

    public static function fromJson(object $json): SupporterField
    {
        return new SupporterField($json->id, $json->name, $json->tag, $json->property);
    }

    public function isTagged(): bool
    {
        return $this->tagged;
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getTag(): string
    {
        return $this->tag;
    }

    public function getProperty(): string
    {
        return $this->property;
    }
}
