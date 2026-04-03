<?php

namespace Hdev\ClicToPayBundle\Event;

use Symfony\Contracts\EventDispatcher\Event;

/**
 * Dispatched when a payment fails or is declined.
 */
class PaymentFailedEvent extends Event
{
    public function __construct(
        private readonly ?string $ctpOrderId,
        private readonly ?string $errorMessage,
        private readonly ?int $errorCode = null,
        private readonly array $data = []
    ) {
    }

    public function getCtpOrderId(): ?string
    {
        return $this->ctpOrderId;
    }

    public function getErrorMessage(): ?string
    {
        return $this->errorMessage;
    }

    public function getErrorCode(): ?int
    {
        return $this->errorCode;
    }

    public function getData(): array
    {
        return $this->data;
    }
}
