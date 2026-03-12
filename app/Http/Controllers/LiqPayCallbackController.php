<?php

namespace App\Http\Controllers;

use App\Models\{Transaction, ShopOrder, User};
use App\Services\{LiqPayService, WalletService};
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class LiqPayCallbackController extends Controller
{
    public function handle(Request $request)
    {
        $data = $request->input('data');
        $signature = $request->input('signature');

        $liqpay = new LiqPayService();
        $decoded = $liqpay->verifyCallback($data, $signature);

        if (!$decoded) {
            Log::warning('LiqPay: callback verification failed');
            return response('invalid signature', 403);
        }

        $orderId = $decoded['order_id'] ?? '';
        $status = $decoded['status'] ?? '';
        $amount = (int) ($decoded['amount'] ?? 0);

        if (!in_array($status, ['success', 'sandbox'])) {
            Log::info("LiqPay: non-success status '{$status}' for order {$orderId}");
            return response('ok');
        }

        // Parse order ID to determine type
        if (str_starts_with($orderId, 'topup_')) {
            $parts = explode('_', $orderId);
            $userId = $parts[1] ?? null;
            $user = User::find($userId);
            if ($user) {
                app(WalletService::class)->deposit($user, $amount, $orderId, $status);
            }
        } elseif (str_starts_with($orderId, 'course_')) {
            // Course payment handled via course_payment flow
            $parts = explode('_', $orderId);
            $courseId = $parts[1] ?? null;
            $userId = $parts[3] ?? null;
            $user = User::find($userId);
            if ($user) {
                $course = \App\Models\Course::find($courseId);
                if ($course) {
                    $course->students()->updateExistingPivot($user->id, [
                        'is_paid' => true,
                        'paid_amount' => $amount,
                        'status' => 'active',
                        'enrolled_at' => now(),
                        'active_until' => now()->addYear(),
                        'telegram_link_shown' => false,
                    ]);
                    Transaction::create([
                        'user_id' => $user->id,
                        'type' => 'course_payment',
                        'amount' => -$amount,
                        'description' => "Оплата курсу: {$course->title}",
                        'liqpay_order_id' => $orderId,
                        'liqpay_status' => $status,
                    ]);
                }
            }
        } elseif (str_starts_with($orderId, 'shop_')) {
            $order = ShopOrder::where('liqpay_order_id', $orderId)->first();
            if ($order) {
                $order->update(['status' => 'paid']);
                $order->product->decrement('stock', $order->quantity);
            }
        }

        return response('ok');
    }
}
