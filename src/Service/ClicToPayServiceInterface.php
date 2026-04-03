<?php

namespace Hdev\ClicToPayBundle\Service;

use Hdev\ClicToPayBundle\Exception\PaymentRegistrationException;
use Hdev\ClicToPayBundle\Exception\PaymentVerificationException;

interface ClicToPayServiceInterface
{
    /**
     * Register a payment order on the SMT/ClicToPay gateway.
     *
     * @param string      $orderNumber  Your internal order reference
     * @param float       $amount       Amount in TND (e.g. 10.500)
     * @param string      $returnUrl    URL to redirect user on success
     * @param string|null $failUrl      URL to redirect user on failure (defaults to returnUrl)
     * @param string|null $description  Optional payment description
     *
     * @return array{orderId: string, formUrl: string}
     *
     * @throws PaymentRegistrationException
     */
    public function registerPayment(
        string $orderNumber,
        float $amount,
        string $returnUrl,
        ?string $failUrl = null,
        ?string $description = null
    ): array;

    /**
     * Check the status of an existing order using the ClicToPay orderId.
     *
     * @param string $ctpOrderId  The orderId returned by registerPayment()
     *
     * @return array The full SMT getOrderStatus response
     *
     * @throws PaymentVerificationException
     */
    public function verifyPayment(string $ctpOrderId): array;
}
