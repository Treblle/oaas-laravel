<?php

namespace Treblle\OaaS\Exceptions;

use Exception;
use Illuminate\Http\Client\Response;
use Throwable;

class OaaSException extends Exception
{
    public function __construct(
        string $message = '',
        int $code = 0,
        private readonly ?Response $response = null,
        ?Throwable $previous = null
    ) {
        parent::__construct($message, $code, $previous);
    }

    public function getResponse(): ?Response
    {
        return $this->response;
    }

    public function hasResponse(): bool
    {
        return $this->response !== null;
    }

    public function getResponseData(): ?array
    {
        return $this->response?->json();
    }
}