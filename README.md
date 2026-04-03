# ClicToPay Symfony Bundle

A reusable Symfony bundle for integrating the **ClicToPay (SMT Tunisia)** payment gateway.

## Features

- **Direct API Integration**: Handles `register.do` and `getOrderStatus.do`.
- **Event-Driven Architecture**: Like modern Symfony bundles, it dispatches events for clean code decoupling (`ClicToPayEvents::PAYMENT_VERIFIED`).
- **Multi-Account / Multi-Tenant**: Supports having different API keys for different clients dynamically.
- **Built-in Webhook Controller**: Ready-to-use webhook route to verify payments.
- **TND Decimal Support**: Automatically converts amounts to millimes (subunits).

## Installation

Add the bundle to your `composer.json` (or use a local path repository if it's not on Packagist yet).

Then, register the bundle in `config/bundles.php`:

```php
return [
    // ...
    Hdev\ClicToPayBundle\ClicToPayBundle::class => ['all' => true],
];
```

## Configuration

### Mode Simple (Standard)
Create a `config/packages/clic_to_pay.yaml` file:

```yaml
clic_to_pay:
    mode: '%env(CLICTOPAY_MODE)%' # 'test' or 'prod'
    user_name: '%env(CLICTOPAY_USER_NAME)%'
    password: '%env(CLICTOPAY_PASSWORD)%'
```

### Mode Avancé (Multiple Accounts)
```yaml
clic_to_pay:
    accounts:
        main:
            user_name: '...'
            password: '...'
            mode: 'prod'
        sandbox:
            user_name: '...'
            password: '...'
            mode: 'test'
```

And update your `.env`:

```env
CLICTOPAY_MODE=test
CLICTOPAY_USER_NAME=your_api_username
CLICTOPAY_PASSWORD=your_api_password
```

## Usage

### 1. Generate a Payment URL

In your controller, use the `ClicToPayManager` to register a payment and get the redirection URL.

```php
use Hdev\ClicToPayBundle\Service\ClicToPayManager;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/checkout/payment", name="app_payment")
 */
public function pay(ClicToPayManager $manager)
{
    $service = $manager->getDefault(); // or $manager->get('sandbox')
    
    $response = $service->registerPayment(
        'ORD-12345',
        150.500, // TND
        $this->generateUrl('app_payment_confirm', [], UrlGeneratorInterface::ABSOLUTE_URL)
    );

    // Redirect the user to ClicToPay secure page
    return $this->redirect($response['formUrl']);
}
```

### 2. Handling Payments (Events)

Create a listener to handle successful payments cleanly:

```php
use Hdev\ClicToPayBundle\Event\ClicToPayEvents;
use Hdev\ClicToPayBundle\Event\PaymentVerifiedEvent;
use Symfony\Component\EventDispatcher\Attribute\AsEventListener;

#[AsEventListener(event: ClicToPayEvents::PAYMENT_VERIFIED)]
public function onPaymentVerified(PaymentVerifiedEvent $event): void
{
    // ClicToPay OrderStatus = 2 means Approved
    if ($event->isApproved()) {
        $orderId = $event->getCtpOrderId();
        $details = $event->getPaymentData();
        
        // Update your order in database!
    }
}
```

### 3. Built-in Webhook

The bundle includes a ready-to-use controller. You can give this link to your clients or use it directly as your `returnUrl`:

```
https://your-app.com/clictopay/webhook/default
```


## Credits

Created by **hdev**. Based on the PrestaShop module for ClicToPay SMT.
