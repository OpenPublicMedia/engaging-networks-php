<?php

namespace OpenPublicMedia\EngagingNetworksServices\Rest\Exception;

use Psr\Http\Message\ResponseInterface;
use RuntimeException;

/**
 * ENS REST API general error exception.
 */
class RequestException extends RuntimeException
{

    private readonly ?string $developerMessage;
    private readonly ?string $errorMessage;
    private readonly ?int $errorMessageId;

    public function __construct(ResponseInterface $response)
    {
        $body = $response->getBody()->getContents();
        try {
            $details = json_decode($body, flags: JSON_THROW_ON_ERROR);
            $message = $response->getReasonPhrase();
        } catch (\JsonException) {
            // Use the body of the response if it is not JSON.
            $message = $body;
        }
        $this->developerMessage = $details?->developerMessage ?? null;
        $this->errorMessage = $details?->message ?? null;
        $this->errorMessageId = $details?->messageId ?? null;
        parent::__construct($message, $response->getStatusCode());
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
