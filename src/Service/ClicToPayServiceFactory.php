<?php

namespace Hdev\ClicToPayBundle\Service;

use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;

/**
 * Creates ClicToPayService instances on demand.
 * Used for dynamic (per-client) account creation.
 */
class ClicToPayServiceFactory
{
    private const URL_TEST = 'https://test.clictopay.com/payment/rest';
    private const URL_PROD = 'https://ipay.clictopay.com/payment/rest';

    public function __construct(
        private readonly HttpClientInterface $httpClient,
        private readonly ?EventDispatcherInterface $eventDispatcher = null
    ) {
    }

    /**
     * Create a ClicToPayService for a given set of credentials.
     *
     * @param string $userName
     * @param string $password
     * @param string $mode     'test' or 'prod'
     * @param string $language
     * @param string $currency
     */
    public function create(
        string $userName,
        string $password,
        string $mode = 'test',
        string $language = 'fr',
        string $currency = '788'
    ): ClicToPayServiceInterface {
        $baseUrl = $mode === 'prod' ? self::URL_PROD : self::URL_TEST;

        return new ClicToPayService(
            $this->httpClient,
            $userName,
            $password,
            $baseUrl,
            $language,
            $currency,
            $this->eventDispatcher
        );
    }
}
