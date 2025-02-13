<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Response;

class StripeWebhookController extends Controller
{
    public function handleWebhook(Request $request)
    {
        $payload = $request->getContent();
        $sigHeader = $request->header('Stripe-Signature');
        
        try {
            $event = \Stripe\Webhook::constructEvent(
                $payload, $sigHeader, config('services.stripe.webhook')
            );
        } catch (\Exception $e) {
            Log::error('Stripe webhook error: '.$e->getMessage());
            return response()->json(['error' => 'Invalid signature'], Response::HTTP_BAD_REQUEST);
        }

        // Manejar diferentes tipos de eventos
        switch ($event->type) {
            case 'payment_intent.succeeded':
                // Lógica para pago exitoso
                break;
            case 'payment_intent.payment_failed':
                // Lógica para pago fallido
                break;
            default:
                Log::info('Unhandled Stripe event: '.$event->type);
        }

        return response()->json(['success' => true]);
    }
}