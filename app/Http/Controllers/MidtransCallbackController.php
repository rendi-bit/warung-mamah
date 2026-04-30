<?php

namespace App\Http\Controllers;

use App\Models\Order;
use Illuminate\Http\Request;

class MidtransCallbackController extends Controller
{
    private function initMidtransConfig(): void
    {
        \Midtrans\Config::$serverKey = config('midtrans.server_key');
        \Midtrans\Config::$isProduction = (bool) config('midtrans.is_production');
        \Midtrans\Config::$isSanitized = (bool) config('midtrans.is_sanitized');
        \Midtrans\Config::$is3ds = (bool) config('midtrans.is_3ds');
    }

    public function handle(Request $request)
    {
        $this->initMidtransConfig();

        $notification = new \Midtrans\Notification();

        $transactionStatus = $notification->transaction_status;
        $paymentType = $notification->payment_type;
        $orderId = $notification->order_id;
        $fraudStatus = $notification->fraud_status ?? null;

        $order = Order::where('order_code', $orderId)->first();

        if (!$order) {
            return response()->json(['message' => 'Order tidak ditemukan'], 404);
        }

        if ($transactionStatus == 'capture') {
            if ($paymentType == 'credit_card') {
                if ($fraudStatus == 'challenge') {
                    $order->payment_status = 'pending';
                    $order->order_status = 'pending';
                } else {
                    $order->payment_status = 'paid';
                    $order->order_status = 'processed';
                }
            }
        } elseif ($transactionStatus == 'settlement') {
            $order->payment_status = 'paid';
            $order->order_status = 'processed';
        } elseif ($transactionStatus == 'pending') {
            $order->payment_status = 'pending';
            $order->order_status = 'pending';
        } elseif (in_array($transactionStatus, ['deny', 'expire', 'cancel'])) {
            $order->payment_status = 'failed';
            $order->order_status = 'cancelled';
        }

        $order->save();

        return response()->json(['message' => 'OK']);
    }
}