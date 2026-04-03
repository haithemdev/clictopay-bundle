<?php

namespace Hdev\ClicToPayBundle\Service;

use InvalidArgumentException;

/**
 * Manages one or multiple ClicToPay service instances.
 *
 * - get('default')       → returns the service registered as 'default'
 * - get('main')          → returns a specific named account
 * - getService($u, $p)   → creates a service on-the-fly for dynamic credentials
 */
class ClicToPayManager
{
    /** @var array<string, ClicToPayServiceInterface> */
    private array $services = [];

    private string $defaultAccount;

    public function __construct(
        private readonly ClicToPayServiceFactory $factory,
        string $defaultAccount = 'default'
    ) {
        $this->defaultAccount = $defaultAccount;
    }

    /**
     * The easiest way to get a ClicToPay service:
     * - If $userName and $password are provided, creates a new service on-the-fly.
     * - Otherwise, returns the default configured service.
     */
    public function getService(
        ?string $userName = null,
        ?string $password = null,
        string $mode = 'test',
        string $language = 'fr',
        string $currency = '788'
    ): ClicToPayServiceInterface {
        if ($userName && $password) {
            return $this->factory->create($userName, $password, $mode, $language, $currency);
        }

        return $this->getDefault();
    }

    public function addService(string $name, ClicToPayServiceInterface $service): void
    {
        $this->services[$name] = $service;
    }

    public function get(string $name): ClicToPayServiceInterface
    {
        if (!isset($this->services[$name])) {
            throw new InvalidArgumentException(
                sprintf('ClicToPay account "%s" is not configured.', $name)
            );
        }

        return $this->services[$name];
    }

    public function getDefault(): ClicToPayServiceInterface
    {
        return $this->get($this->defaultAccount);
    }

    /**
     * @return array<string, ClicToPayServiceInterface>
     */
    public function all(): array
    {
        return $this->services;
    }
}
