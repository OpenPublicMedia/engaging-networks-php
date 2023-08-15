<?php

namespace OpenPublicMedia\EngagingNetworksServices\Rest\Resource;

use OpenPublicMedia\EngagingNetworksServices\Rest\Enums\SupporterQuestionHtmlFieldType;
use OpenPublicMedia\EngagingNetworksServices\Rest\Enums\SupporterQuestionType;

/**
 * ENS REST API supporter question.
 */
final class SupporterQuestion
{
    /**
     * Indicates if the question has details included.
     *
     * This is necessary to distinguish loaded null detail values from default
     * null values.
     */
    protected bool $hasDetails = false;

    public function __construct(
        protected readonly int $id,
        protected readonly int $questionId,
        protected readonly string $name,
        protected readonly SupporterQuestionType $type,
        protected readonly ?string $locale = null,
        protected readonly ?string $label = null,
        protected readonly ?SupporterQuestionHtmlFieldType $htmlFieldType = null,
        protected readonly ?object $content = null,
    ) {
        $this->hasDetails = func_num_args() > 4;
    }

    public static function fromJson(object $json): SupporterQuestion
    {
        return new SupporterQuestion(
            $json->id,
            $json->questionId,
            $json->name,
            SupporterQuestionType::from(strtolower($json->type))
        );
    }

    public static function fromDetailJson(object $json): SupporterQuestion
    {
        return new SupporterQuestion(
            $json->id,
            $json->questionId,
            $json->name,
            SupporterQuestionType::from(strtolower($json->type)),
            $json->locale,
            $json->label,
            SupporterQuestionHtmlFieldType::tryFrom(strtolower($json->htmlFieldType)),
            $json->content
        );
    }

    public function hasDetails(): bool
    {
        return $this->hasDetails;
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function getQuestionId(): int
    {
        return $this->questionId;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getType(): SupporterQuestionType
    {
        return $this->type;
    }

    public function getLocale(): ?string
    {
        return $this->locale;
    }

    public function getLabel(): ?string
    {
        return $this->label;
    }

    public function getHtmlFieldType(): ?SupporterQuestionHtmlFieldType
    {
        return $this->htmlFieldType;
    }

    public function getContent(): ?object
    {
        return $this->content;
    }
}
