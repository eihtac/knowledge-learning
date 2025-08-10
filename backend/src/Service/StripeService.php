<?php

namespace App\Service;

use App\Entity\Lesson;
use App\Entity\Course;
use Doctrine\ORM\EntityManagerInterface;
use Stripe\Checkout\Session;
use Stripe\Stripe;

class StripeService
{
    public function __construct(private readonly string $stripeSecretKey, private readonly EntityManagerInterface $entityManager, private readonly string $frontendUrl)
    {
        Stripe::setApiKey($this->stripeSecretKey);
    }

    public function createCheckoutSession(string $type, int $id, int $userId): ?Session
    {
        $product = null;
        $name = '';
        $amount = 0;

        if ($type === 'lesson') {
            $product = $this->entityManager->getRepository(Lesson::class)->find($id);
        } elseif ($type === 'course') {
            $product = $this->entityManager->getRepository(Course::class)->find($id);
        }

        if (!$product) {
            return null;
        }

        $name = $product->getTitle();
        $amount = (int)($product->getPrice() * 100);

        $successUrl = $this->frontendUrl . "/catalog?payment=success&type=$type&id=$id";
        $cancelUrl = $this->frontendUrl . "/catalog?payment=cancel";

        return Session::create([
            'payment_method_types' => ['card'],
            'line_items' => [[
                'price_data' => [
                    'currency' => 'eur',
                    'product_data' => [
                        'name' => $name,
                    ],
                    'unit_amount' => $amount,
                ],
                'quantity' => 1,
            ]],
            'mode' => 'payment',
            'success_url' => $successUrl,
            'cancel_url' => $cancelUrl,
            'client_reference_id' => $userId,
        ]);
    }
}