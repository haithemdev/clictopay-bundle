<?php

namespace Hdev\ClicToPayBundle\Service;

use Hdev\ClicToPayBundle\Event\ClicToPayEvents;
use Hdev\ClicToPayBundle\Event\PaymentFailedEvent;
use Hdev\ClicToPayBundle\Event\PaymentRegisteredEvent;
use Hdev\ClicToPayBundle\Event\PaymentVerifiedEvent;
use Hdev\ClicToPayBundle\Exception\PaymentRegistrationException;
use Hdev\ClicToPayBundle\Exception\PaymentVerificationException;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;

class ClicToPayService implements ClicToPayServiceInterface
{
    private HttpClientInterface $httpClient;
    private ?EventDispatcherInterface $eventDispatcher;
    private string $userName;
    private string $password;
    private string $apiBaseUrl;
    private string $language;
    private string $currency;

    public function __construct(
        HttpClientInterface $httpClient,
        string $userName,
        string $password,
        string $apiBaseUrl,
        string $language = 'fr',
        string $currency = '788',
        ?EventDispatcherInterface $eventDispatcher = null
    ) {
        $this->httpClient      = $httpClient;
        $this->userName        = $userName;
        $this->password        = $password;
        $this->apiBaseUrl      = rtrim($apiBaseUrl, '/');
        $this->language        = $language;
        $this->currency        = $currency;
        $this->eventDispatcher = $eventDispatcher;
    }

    /**
     * {@inheritdoc}
     *
     * Calls register.do and returns ['orderId' => '...', 'formUrl' => '...']
     * The formUrl is the SMT hosted page where the customer enters card details.
     */
    public function registerPayment(
        string $orderNumber,
        float $amount,
        string $returnUrl,
        ?string $failUrl = null,
        ?string $description = null
    ): array {
        $params = [
            'userName'    => $this->userName,
            'password'    => $this->password,
            'orderNumber' => $orderNumber,
            'amount'      => $this->convertAmountToSubunits($amount),
            'currency'    => $this->currency,
            'language'    => $this->language,
            'returnUrl'   => $returnUrl,
            'failUrl'     => $failUrl ?? $returnUrl,
        ];

        if ($description !== null) {
            $params['description'] = $description;
        }

        try {
            $response = $this->httpClient->request('POST', $this->apiBaseUrl . '/register.do', [
                'body' => $params,
            ]);

            $content = $response->toArray();

            if (isset($content['errorCode']) && (int) $content['errorCode'] !== 0) {
                throw new PaymentRegistrationException(
                    sprintf(
                        'ClicToPay registration error [%s]: %s',
                        $content['errorCode'],
                        $content['errorMessage'] ?? 'Unknown error'
                    )
                );
            }

            if (!isset($content['orderId'], $content['formUrl'])) {
                throw new PaymentRegistrationException('Invalid API response: missing orderId or formUrl.');
            }

            $result = [
                'orderId' => $content['orderId'],
                'formUrl' => $content['formUrl'],
                'orderNumber' => $orderNumber,
            ];

            if ($this->eventDispatcher) {
                $this->eventDispatcher->dispatch(
                    new PaymentRegisteredEvent($result),
                    ClicToPayEvents::PAYMENT_REGISTERED
                );
            }

            return $result;
        } catch (PaymentRegistrationException $e) {
            throw $e;
        } catch (\Exception $e) {
            throw new PaymentRegistrationException(
                'Could not register payment: ' . $e->getMessage(),
                0,
                $e
            );
        }
    }

    /**
     * {@inheritdoc}
     *
     * Calls getOrderStatus.do and dispatches PaymentVerifiedEvent or PaymentFailedEvent.
     * OrderStatus = 2 means fully paid.
     */
    public function verifyPayment(string $ctpOrderId): array
    {
        $params = [
            'userName' => $this->userName,
            'password' => $this->password,
            'orderId'  => $ctpOrderId,
            'language' => $this->language,
        ];

        try {
            $response = $this->httpClient->request('POST', $this->apiBaseUrl . '/getOrderStatus.do', [
                'body' => $params,
            ]);

            $content = $response->toArray();

            if (isset($content['errorCode']) && (int) $content['errorCode'] !== 0) {
                if ($this->eventDispatcher) {
                    $this->eventDispatcher->dispatch(
                        new PaymentFailedEvent(
                            $ctpOrderId,
                            $content['errorMessage'] ?? 'Unknown error',
                            (int) $content['errorCode'],
                            $content
                        ),
                        ClicToPayEvents::PAYMENT_FAILED
                    );
                }

                throw new PaymentVerificationException(
                    sprintf(
                        'ClicToPay verification error [%s]: %s',
                        $content['errorCode'],
                        $content['errorMessage'] ?? 'Unknown error'
                    )
                );
            }

            $orderStatus = (int) ($content['OrderStatus'] ?? $content['orderStatus'] ?? -1);

            if ($orderStatus === 2) {
                // Fully paid
                if ($this->eventDispatcher) {
                    $this->eventDispatcher->dispatch(
                        new PaymentVerifiedEvent($content),
                        ClicToPayEvents::PAYMENT_VERIFIED
                    );
                }
            } elseif ($this->eventDispatcher && $orderStatus !== 0) {
                // Any non-zero non-paid status is a failure
                $this->eventDispatcher->dispatch(
                    new PaymentFailedEvent(
                        $ctpOrderId,
                        'Payment not approved. OrderStatus: ' . $orderStatus,
                        $orderStatus,
                        $content
                    ),
                    ClicToPayEvents::PAYMENT_FAILED
                );
            }

            return $content;
        } catch (PaymentVerificationException $e) {
            throw $e;
        } catch (\Exception $e) {
            throw new PaymentVerificationException(
                'Could not verify payment: ' . $e->getMessage(),
                0,
                $e
            );
        }
    }

    /**
     * Convert TND decimal amount to millimes (subunits).
     * SMT expects no decimal point: 10.500 TND => '10500'
     */
    private function convertAmountToSubunits(float $amount): string
    {
        // number_format(10.500, 3, '', '') => '10500'
        return number_format($amount, 3, '', '');
    }
}
