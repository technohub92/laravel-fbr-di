<?php

namespace FbrDI\Exceptions;

use Exception;

class FbrApiException extends Exception
{
    protected array $responseBody = [];
    protected int $statusCode = 0;

    public function __construct(string $message = "", int $code = 0, array $responseBody = [], int $statusCode = 0, ?\Throwable $previous = null)
    {
        parent::__construct($message, $code, $previous);
        $this->responseBody = $responseBody;
        $this->statusCode = $statusCode;
    }

    public function getResponseBody(): array
    {
        return $this->responseBody;
    }

    public function getStatusCode(): int
    {
        return $this->statusCode;
    }
}
