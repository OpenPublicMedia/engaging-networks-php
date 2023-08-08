<?php

namespace OpenPublicMedia\EngagingNetworksServices\Rest\Resource;

use DateTime;
use OpenPublicMedia\EngagingNetworksServices\Rest\Enums\PageRequestResultType;
use OpenPublicMedia\EngagingNetworksServices\Rest\Enums\PageRequestResultStatus;
use OpenPublicMedia\EngagingNetworksServices\Rest\Enums\PaymentType;
use OpenPublicMedia\EngagingNetworksServices\Rest\Enums\RecurringFrequency;

/**
 * ENS REST API result object from a Page processioning request.
 */
final class PageRequestResult
{
    public function __construct(
        protected readonly int $id,
        protected readonly PageRequestResultStatus $status,
        protected readonly int $supporterId,
        protected readonly string $supporterEmailAddress,
        protected readonly ?PageRequestResultType $type = null,
        protected readonly ?string $transactionId = null,
        protected readonly ?string $error = null,
        protected readonly ?float $amount = null,
        protected readonly ?string $currency = null,
        protected readonly ?bool $recurringPayment = null,
        protected readonly ?PaymentType $paymentType = null,
        protected readonly ?RecurringFrequency $recurringFrequency = null,
        protected readonly ?int $recurringDay = null,
        protected readonly ?DateTime $createdOn = null,
    ) {
    }

    public static function fromJson(object $json): PageRequestResult
    {
        return new PageRequestResult(
            $json->id,
            PageRequestResultStatus::from(strtolower($json->status)),
            $json->supporterId,
            $json->supporterEmailAddress,
            property_exists($json, 'type') ? PageRequestResultType::tryFrom(strtolower($json->type)) : null,
            $json->transactionId ?? null,
            $json->error ?? null,
            $json->amount ?? null,
            $json->currency ?? null,
            $json->recurringPayment ?? null,
            property_exists($json, 'paymentType') ? PaymentType::tryFrom(strtolower($json->paymentType)) : null,
            property_exists($json, 'recurringFrequency')
                ? RecurringFrequency::tryFrom(strtolower($json->recurringFrequency)) : null,
            $json->recurringDay ?? null,
            property_exists($json, 'createdOn') ? (new DateTime('@' . $json->createdOn/1000)) : null,
        );
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function getStatus(): PageRequestResultStatus
    {
        return $this->status;
    }

    public function getSupporterId(): int
    {
        return $this->supporterId;
    }

    public function getSupporterEmailAddress(): string
    {
        return $this->supporterEmailAddress;
    }

    public function getType(): ?PageRequestResultType
    {
        return $this->type;
    }

    public function getTransactionId(): ?string
    {
        return $this->transactionId;
    }

    public function getError(): ?string
    {
        return $this->error;
    }

    public function getAmount(): ?float
    {
        return $this->amount;
    }

    public function getCurrency(): ?string
    {
        return $this->currency;
    }

    public function getRecurringPayment(): ?bool
    {
        return $this->recurringPayment;
    }

    public function getPaymentType(): ?PaymentType
    {
        return $this->paymentType;
    }

    public function getRecurringFrequency(): ?RecurringFrequency
    {
        return $this->recurringFrequency;
    }

    public function getRecurringDay(): ?int
    {
        return $this->recurringDay;
    }

    public function getCreatedOn(): ?DateTime
    {
        return $this->createdOn;
    }
}
