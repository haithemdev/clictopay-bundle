<?php

namespace Hdev\ClicToPayBundle\Event;

/**
 * Lists all events dispatched by the ClicToPay Symfony Bundle.
 */
final class ClicToPayEvents
{
    /**
     * Dispatched after a payment is successfully registered on the SMT gateway.
     * Event class: Hdev\ClicToPayBundle\Event\PaymentRegisteredEvent
     */
    public const PAYMENT_REGISTERED = 'clic_to_pay.payment_registered';

    /**
     * Dispatched after a payment status is verified as SUCCESSFUL (OrderStatus = 2).
     * Event class: Hdev\ClicToPayBundle\Event\PaymentVerifiedEvent
     */
    public const PAYMENT_VERIFIED = 'clic_to_pay.payment_verified';

    /**
     * Dispatched when a payment fails or is cancelled.
     * Event class: Hdev\ClicToPayBundle\Event\PaymentFailedEvent
     */
    public const PAYMENT_FAILED = 'clic_to_pay.payment_failed';
}
