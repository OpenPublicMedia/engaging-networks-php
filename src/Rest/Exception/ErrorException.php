<?php

namespace OpenPublicMedia\EngagingNetworksServices\Rest\Exception;

use Psr\Http\Message\ResponseInterface;
use RuntimeException;

/**
 * ENS REST API general error exception.
 */
class ErrorException extends RuntimeException
{

    private readonly ?string $developerMessage;
    private readonly ?string $errorMessage;
    private readonly ?int $errorMessageId;

    public function __construct(ResponseInterface $response)
    {
        $details = json_decode($response->getBody()->getContents());
        $this->developerMessage = $details?->developerMessage ?? null;
        $this->errorMessage = $details?->message ?? null;
        $this->errorMessageId = $details?->messageId ?? null;
        parent::__construct($response->getReasonPhrase(), $response->getStatusCode());
    }

    public function getDeveloperMessage(): ?string
    {
        return $this->developerMessage;
    }

    public function getErrorMessage(): ?string
    {
        return $this->errorMessage;
    }

    public function getErrorMessageId(): ?int
    {
        return $this->errorMessageId;
    }
}
