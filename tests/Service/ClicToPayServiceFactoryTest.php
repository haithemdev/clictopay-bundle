<?php

namespace Hdev\ClicToPayBundle\Tests\Service;

use Hdev\ClicToPayBundle\Service\ClicToPayService;
use Hdev\ClicToPayBundle\Service\ClicToPayServiceFactory;
use PHPUnit\Framework\TestCase;
use Symfony\Contracts\HttpClient\HttpClientInterface;

class ClicToPayServiceFactoryTest extends TestCase
{
    public function testCreateServiceFactory(): void
    {
        $httpClient = $this->createMock(HttpClientInterface::class);
        $factory = new ClicToPayServiceFactory($httpClient);
        
        $service = $factory->create('test_user', 'test_pass', 'test');
        
        $this->assertInstanceOf(ClicToPayService::class, $service);
    }
}
