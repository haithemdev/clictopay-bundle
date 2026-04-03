<?php

namespace Hdev\ClicToPayBundle\Controller;

use Hdev\ClicToPayBundle\Exception\PaymentVerificationException;
use Hdev\ClicToPayBundle\Service\ClicToPayManager;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

/**
 * Built-in webhook / return URL handler for ClicToPay notifications.
 *
 * Point your returnUrl or failUrl to:
 *   GET/POST /clictopay/webhook/{account}?orderId=CLICTOPAY_ORDER_ID
 *
 * The controller will automatically call verifyPayment() and dispatch
 * a PaymentVerifiedEvent or PaymentFailedEvent.
 */
class ClicToPayWebhookController extends AbstractController
{
    public function __construct(private readonly ClicToPayManager $manager)
    {
    }

    #[Route('/clictopay/webhook/{account}', name: 'clic_to_pay_webhook', methods: ['GET', 'POST'])]
    public function handle(Request $request, string $account = 'default'): JsonResponse
    {
        $ctpOrderId = $request->get('orderId');

        if (!$ctpOrderId) {
            return new JsonResponse(['error' => 'Missing orderId parameter'], 400);
        }

        try {
            $service = $this->manager->get($account);
            $result  = $service->verifyPayment($ctpOrderId);

            $orderStatus = $result['OrderStatus'] ?? $result['orderStatus'] ?? null;

            return new JsonResponse([
                'success'      => true,
                'orderId'      => $ctpOrderId,
                'orderStatus'  => $orderStatus,
                'approved'     => (string) $orderStatus === '2',
            ]);
        } catch (PaymentVerificationException $e) {
            return new JsonResponse(['error' => $e->getMessage()], 422);
        } catch (\Exception $e) {
            return new JsonResponse(['error' => $e->getMessage()], 500);
        }
    }
}
