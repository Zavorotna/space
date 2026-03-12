<?php

namespace App\Http\Controllers;

use App\Models\{User, Transaction, WithdrawalRequest};
use App\Services\{WalletService, VipService, LiqPayService};
use Illuminate\Http\Request;

class WalletController extends Controller
{
    protected WalletService $walletService;

    public function __construct(WalletService $walletService)
    {
        $this->walletService = $walletService;
    }

    public function index(Request $request)
    {
        $user = $request->user();
        $wallet = $user->getOrCreateWallet();
        $transactions = $user->transactions()->latest()->paginate(20);

        return view('wallet.index', compact('wallet', 'transactions'));
    }

    // ── Top-up via LiqPay ──────────────────────────────────────

    public function topUpForm(Request $request)
    {
        $user = $request->user();
        $wallet = $user->getOrCreateWallet();
        $transactions = $user->transactions()->latest()->limit(10)->get();

        return view('wallet.topup', compact('wallet', 'transactions'));
    }

    public function topUp(Request $request)
    {
        $validated = $request->validate([
            'amount' => 'required|integer|min:1',
        ]);

        $user = $request->user();
        $orderId = 'topup_' . $user->id . '_' . time();

        $liqpay = new LiqPayService();
        $paymentData = $liqpay->createPayment(
            $validated['amount'],
            $orderId,
            'Поповнення балансу Hashtag Space',
            route('wallet.index'),
            route('liqpay.callback')
        );

        return view('wallet.liqpay-redirect', compact('paymentData'));
    }

    // ── Transfer ───────────────────────────────────────────────

    public function transferForm(Request $request)
    {
        $user = $request->user();
        $wallet = $user->getOrCreateWallet();
        $transactions = $user->transactions()->latest()->limit(10)->get();
        return view('wallet.transfer', compact('wallet', 'transactions'));
    }

    public function transfer(Request $request)
    {
        $validated = $request->validate([
            'recipient' => 'required|string',
            'amount' => 'required|integer|min:1',
            'comment' => 'nullable|string|max:500',
        ]);

        $user = $request->user();
        $recipient = User::where('phone', $validated['recipient'])
            ->orWhere('id', $validated['recipient'])
            ->orWhereRaw("CONCAT(first_name, ' ', last_name) LIKE ?", ["%{$validated['recipient']}%"])
            ->first();

        if (!$recipient) {
            return back()->with('error', 'Отримувача не знайдено.');
        }

        try {
            $this->walletService->transfer($user, $recipient, $validated['amount'], $validated['comment']);
            return redirect()->route('wallet.index')
                ->with('success', "Переказано {$validated['amount']} монет для {$recipient->full_name}.");
        } catch (\Exception $e) {
            return back()->with('error', $e->getMessage())->withInput();
        }
    }

    // ── Withdrawal ─────────────────────────────────────────────

    public function withdrawForm(Request $request)
    {
        $user = $request->user();
        $wallet = $user->getOrCreateWallet();
        $transactions = $user->transactions()->latest()->limit(10)->get();
        return view('wallet.withdraw', compact('wallet', 'transactions'));
    }

    public function withdraw(Request $request)
    {
        $validated = $request->validate([
            'amount' => 'required|integer|min:100',
        ]);

        try {
            $wr = $this->walletService->requestWithdrawal($request->user(), $validated['amount']);
            return redirect()->route('wallet.index')
                ->with('success', 'Запит на виведення створено. Очікуйте підтвердження адміністратора.');
        } catch (\Exception $e) {
            return back()->with('error', $e->getMessage());
        }
    }

    // ── VIP Purchase ───────────────────────────────────────────

    public function purchaseVip(Request $request)
    {
        try {
            app(VipService::class)->purchaseVip($request->user());
            return back()->with('success', 'VIP статус активовано на 3 місяці!');
        } catch (\Exception $e) {
            return back()->with('error', $e->getMessage());
        }
    }

    // ── Donate ─────────────────────────────────────────────────

    public function donate(Request $request)
    {
        $validated = $request->validate(['amount' => 'required|integer|min:1']);

        try {
            $this->walletService->donate($request->user(), $validated['amount']);
            $msg = 'Донат прийнято. Дякуємо!';
            if ($validated['amount'] >= 500) $msg .= ' Вам присвоєно VIP статус!';
            return back()->with('success', $msg);
        } catch (\Exception $e) {
            return back()->with('error', $e->getMessage());
        }
    }

    // ── Admin: manage withdrawals ──────────────────────────────

    public function withdrawalRequests(Request $request)
    {
        $requests = WithdrawalRequest::with('user')
            ->when($request->status, fn($q, $s) => $q->where('status', $s))
            ->latest()
            ->paginate(20);

        return view('admin.withdrawals', compact('requests'));
    }

    public function approveWithdrawal(Request $request, WithdrawalRequest $withdrawal)
    {
        $validated = $request->validate(['pickup_note' => 'required|string|max:500']);
        $this->walletService->approveWithdrawal($withdrawal, $request->user(), $validated['pickup_note']);
        return back()->with('success', 'Виведення підтверджено.');
    }

    public function rejectWithdrawal(WithdrawalRequest $withdrawal)
    {
        $this->walletService->rejectWithdrawal($withdrawal, auth()->user());
        return back()->with('success', 'Виведення відхилено. Монети повернуто.');
    }

    // ── Admin: all transactions ────────────────────────────────

    public function allTransactions(Request $request)
    {
        $transactions = Transaction::with(['user', 'relatedUser'])
            ->when($request->type, fn($q, $t) => $q->where('type', $t))
            ->when($request->user_id, fn($q, $u) => $q->where('user_id', $u))
            ->latest()
            ->paginate(50);

        return view('admin.transactions', compact('transactions'));
    }
}
