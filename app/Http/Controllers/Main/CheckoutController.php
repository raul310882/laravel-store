<?php

namespace App\Http\Controllers\Main;

use App\Enums\OrderPayment;
use App\Http\Controllers\Controller;
use App\Http\Requests\OrderRequest;
use App\Models\Order;
use App\Models\Setting;
use App\Models\User;
use App\Providers\RouteServiceProvider;
use App\Support\Cart;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Stripe\StripeClient;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Illuminate\Http\Request;
use Inertia\Inertia;

class CheckoutController extends Controller
{
    public function index()
    {
        if (!Cart::getCount()) {
            return redirect()->intended(RouteServiceProvider::HOME);
        }

        return inertia('Checkout/Index', [
            'deliveries' => Setting::whereGroup('delivery')->get(),
            'payments' => OrderPayment::asSelectArray(),
        ]);
    }

    public function store(OrderRequest $request)
    {
        
        $data = $request->validated();
        /** @var User $user */
        $user = $request->user();

        try {
            DB::beginTransaction();
            $order = Order::create(Arr::except($data, 'items'));
            $order->orderItems()->createMany($user->cartItems->setVisible(['good_id', 'quantity', 'unit_price'])->toArray());
            $stripe = new StripeClient(config('services.stripe.secret'));
            $currency = strtolower(config('services.stripe.currency', 'usd'));
            
            $lineItems = $order->orderItems->map(function($item) use ($currency) {
                return [
                    'price_data' => [
                        'currency' => $currency,
                        'product_data' => ['name' => substr($item->good->title, 0, 254)],
                        'unit_amount' => (int) round($item->unit_price * 100, 0)
                    ],
                    'quantity' => $item->quantity
                ];
            })->toArray();
            
            $session = $stripe->checkout->sessions->create([
                'payment_method_types' => ['card'],
                'line_items' => $lineItems,
                'mode' => 'payment',
                'success_url' => route('checkout.success', [], true) . "?session_id={CHECKOUT_SESSION_ID}",
                'cancel_url' => route('checkout.cancel', [], true),
                'customer_email' => $user->email,
                'metadata' => ['order_id' => $order->id]
            ]);
         
            $order->stripe_session_id = $session->id;
            $order->save();
            //dd($session);
            $user->cartItems()->delete();
            DB::commit();
            
            return response()->json([], 409, [
                'X-Inertia-Location' => $session->url
            ]);

        } catch (\Exception $exception) {
           
            DB::rollBack();
            return back()->withErrors(['payment' => 'Error en el pago']);
        }
    }

    public function success(Request $request)
    {
        $stripe = new StripeClient(config('services.stripe.secret'));
        $sessionId = $request->get('session_id');

        try {
            $session = $stripe->checkout->sessions->retrieve($sessionId);
            
            if (!$session) {
                throw new NotFoundHttpException;
            }

            $order = Order::where('stripe_session_id', $session->id)->first();
            
            if ($order->payment_status !== 'paid') {
                $order->update(['payment_status' => 'paid']);
                Setting::sendNotification('success', 'Payment Successful', 'Your payment was processed successfully');
            }

            return inertia('Checkout/Success', [
                'order' => $order
            ]);

        } catch (\Exception $e) {
            Log::error('Stripe Success Error: ' . $e->getMessage());
            return redirect()->route('checkout.cancel');
        }
    }

    public function cancel()
    {
        return inertia('Checkout/Cancel', [
            'message' => 'Your payment was cancelled. You can try again later.'
        ]);
    }
}
