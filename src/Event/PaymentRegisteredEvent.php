<?php

namespace Hdev\ClicToPayBundle\Event;

use Symfony\Contracts\EventDispatcher\Event;

/**
 * Dispatched after a payment is successfully registered on the SMT gateway.
 * Use this event to store the ClicToPay orderId in your database.
 */
class PaymentRegisteredEvent extends Event
{
    public function __construct(private readonly array $data)
    {
    }

    public function getData(): array
    {
        return $this->data;
    }

    /**
     * The ClicToPay orderId to persist, used later for verification.
     */
    public function getCtpOrderId(): ?string
    {
        return $this->data['orderId'] ?? null;
    }

    /**
     * The redirect URL to the SMT hosted payment page.
     */
    public function getFormUrl(): ?string
    {
        return $this->data['formUrl'] ?? null;
    }

    /**
     * Your own reference (orderNumber) sent to SMT.
     */
    public function getOrderNumber(): ?string
    {
        return $this->data['orderNumber'] ?? null;
    }
}
