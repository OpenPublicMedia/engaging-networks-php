<?php

namespace OpenPublicMedia\EngagingNetworksServices\Rest\Resource;

use DateTime;
use OpenPublicMedia\EngagingNetworksServices\Rest\Enums\PageStatus;
use OpenPublicMedia\EngagingNetworksServices\Rest\Enums\PageType;

/**
 * ENS REST API "Page" object.
 */
final class Page
{
    public function __construct(
        protected readonly int $id,
        protected readonly int $campaignId,
        protected readonly string $name,
        protected readonly string $title,
        protected readonly PageType $type,
        protected readonly ?PageType $subType,
        protected readonly int $clientId,
        protected readonly DateTime $createdOn,
        protected readonly DateTime $modifiedOn,
        protected readonly string $campaignBaseUrl,
        protected readonly PageStatus $campaignStatus,
        protected readonly string $defaultLocale
    ) {
    }

    public static function fromJson(object $json): Page
    {
        return new Page(
            $json->id,
            $json->campaignId,
            $json->name,
            $json->title,
            PageType::from(strtolower($json->type)),
            PageType::tryFrom(strtolower($json->subType)),
            $json->clientId,
            (new DateTime('@' . $json->createdOn/1000)),
            (new DateTime('@' . $json->modifiedOn/1000)),
            $json->campaignBaseUrl,
            PageStatus::from($json->campaignStatus),
            $json->defaultLocale,
        );
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function getCampaignId(): int
    {
        return $this->campaignId;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getTitle(): string
    {
        return $this->title;
    }

    public function getType(): PageType
    {
        return $this->type;
    }

    public function getSubType(): PageType
    {
        return $this->subType;
    }

    public function getClientId(): int
    {
        return $this->clientId;
    }

    public function getCreatedOn(): DateTime
    {
        return $this->createdOn;
    }

    public function getModifiedOn(): DateTime
    {
        return $this->modifiedOn;
    }

    public function getCampaignBaseUrl(): string
    {
        return $this->campaignBaseUrl;
    }

    public function getCampaignStatus(): PageStatus
    {
        return $this->campaignStatus;
    }

    public function getDefaultLocale(): string
    {
        return $this->defaultLocale;
    }
}
