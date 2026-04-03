<?php

namespace Hdev\ClicToPayBundle\DependencyInjection;

use Hdev\ClicToPayBundle\Service\ClicToPayManager;
use Hdev\ClicToPayBundle\Service\ClicToPayService;
use Hdev\ClicToPayBundle\Service\ClicToPayServiceFactory;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\Loader\YamlFileLoader;
use Symfony\Component\DependencyInjection\Reference;
use Symfony\Component\HttpKernel\DependencyInjection\Extension;

class ClicToPayExtension extends Extension
{
    public function load(array $configs, ContainerBuilder $container): void
    {
        $loader = new YamlFileLoader($container, new FileLocator(\dirname(__DIR__, 2) . '/config'));
        $loader->load('services.yaml');

        $configuration = new Configuration();
        $config = $this->processConfiguration($configuration, $configs);

        $apiBaseUrlTest = 'https://test.clictopay.com/payment/rest';
        $apiBaseUrlProd = 'https://ipay.clictopay.com/payment/rest';

        // Determine accounts to register
        $accounts = [];

        if (!empty($config['accounts'])) {
            // Advanced mode: multiple named accounts
            foreach ($config['accounts'] as $name => $accountConfig) {
                $accounts[$name] = $accountConfig;
            }
        } elseif ($config['user_name'] && $config['password']) {
            // Simple mode: single default account
            $accounts['default'] = [
                'user_name' => $config['user_name'],
                'password'  => $config['password'],
                'mode'      => $config['mode'],
                'language'  => $config['language'],
                'currency'  => $config['currency'],
            ];
        }

        // Build each ClicToPayService definition
        $managerDef = $container->getDefinition(ClicToPayManager::class);

        foreach ($accounts as $name => $accountConfig) {
            $baseUrl = $accountConfig['mode'] === 'prod' ? $apiBaseUrlProd : $apiBaseUrlTest;

            $serviceDef = new Definition(ClicToPayService::class, [
                new Reference('http_client'),
                $accountConfig['user_name'],
                $accountConfig['password'],
                $baseUrl,
                $accountConfig['language'] ?? 'fr',
                $accountConfig['currency'] ?? '788',
                new Reference('event_dispatcher'),
            ]);
            $serviceDef->setPublic(false);

            $serviceId = 'hdev.clic_to_pay.service.' . $name;
            $container->setDefinition($serviceId, $serviceDef);

            // Register account in manager via addService method call
            $managerDef->addMethodCall('addService', [$name, new Reference($serviceId)]);
        }

        // Default account name for manager
        $defaultAccount = array_key_first($accounts) ?? 'default';
        $container->getDefinition(ClicToPayManager::class)
            ->setArgument('$defaultAccount', $defaultAccount);
    }

    public function getAlias(): string
    {
        return 'clic_to_pay';
    }
}
