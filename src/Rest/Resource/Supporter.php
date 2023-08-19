<?php

namespace OpenPublicMedia\EngagingNetworksServices\Rest\Resource;

use OpenPublicMedia\EngagingNetworksServices\Rest\Exception\SupporterFieldNotFoundException;

/**
 * ENS REST API "Supporter" object.
 */
final class Supporter
{
    /**
     * @param array<string,string> $fields
     * @param array<string,mixed> $memberships
     * @param array<string,mixed> $questions
     */
    public function __construct(
        protected readonly int $supporterId,
        protected readonly bool $suppressed,
        protected readonly string $emailAddress,
        protected readonly ?array $fields = null,
        protected readonly ?array $memberships = null,
        protected readonly ?array $questions = null
    ) {
    }

    public static function fromJson(object $json): Supporter
    {
        $fields = (array) clone $json;
        unset(
            $fields['supporterId'],
            $fields['suppressed'],
            $fields['Email Address'],
            $fields['memberships]'],
            $fields['questions']
        );
        return new Supporter(
            $json->supporterId,
            $json->suppressed,
            $json->{'Email Address'},
            empty($fields) ? null : $fields,
            property_exists($json, 'memberships') ? $json->memberships : null,
            property_exists($json, 'questions') ? $json->questions : null,
        );
    }

    public function getSupporterId(): int
    {
        return $this->supporterId;
    }

    public function isSuppressed(): bool
    {
        return $this->suppressed;
    }

    public function getEmailAddress(): string
    {
        return $this->emailAddress;
    }

    /**
     * @return array<string,mixed>
     */
    public function getFields(): array
    {
        return $this->fields;
    }

    public function getField(string $fieldName): string
    {
        if (!isset($this->fields[$fieldName])) {
            throw new SupporterFieldNotFoundException($fieldName);
        }
        return $this->fields[$fieldName];
    }

    /**
     * @return array<string,mixed>|null
     */
    public function getMemberships(): ?array
    {
        return $this->memberships;
    }

    /**
     * @return array<string,mixed>|null
     */
    public function getQuestions(): ?array
    {
        return $this->questions;
    }
}
