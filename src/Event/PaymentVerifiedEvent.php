<?php

namespace Hdev\ClicToPayBundle\Event;

use Symfony\Contracts\EventDispatcher\Event;

/**
 * Dispatched after a payment is verified as APPROVED (OrderStatus = 2).
 * Listen to this event to confirm orders, send emails, etc.
 */
class PaymentVerifiedEvent extends Event
{
    public function __construct(private readonly array $data)
    {
    }

    public function getData(): array
    {
        return $this->data;
    }

    /**
     * SMT OrderStatus code.
     * 0 = registered, 1 = pre-authorised, 2 = paid, 3 = auth cancelled,
     * 4 = refunded, 5 = ACS waiting, 6 = declined.
     */
    public function getOrderStatus(): ?string
    {
        return $this->data['OrderStatus'] ?? ($this->data['orderStatus'] ?? null);
    }

    /**
     * Returns true if payment was fully approved (OrderStatus = 2).
     */
    public function isApproved(): bool
    {
        return (string) $this->getOrderStatus() === '2';
    }

    /**
     * The ClicToPay orderId (gateway reference).
     */
    public function getCtpOrderId(): ?string
    {
        return $this->data['orderId'] ?? null;
    }

    /**
     * The masked card PAN, if available.
     */
    public function getMaskedPan(): ?string
    {
        $pan = $this->data['Pan'] ?? null;
        if (!$pan) {
            return null;
        }
        return '**** **** **** ' . substr($pan, -4);
    }

    /**
     * Raw payment data from the SMT getOrderStatus.do response.
     */
    public function getPaymentData(): array
    {
        return $this->data;
    }
}
