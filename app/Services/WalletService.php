<?php

namespace App\Services;

use App\Models\{User, Wallet, Transaction, Donation, WithdrawalRequest};
use Illuminate\Support\Facades\DB;

class WalletService
{
    /**
     * Award coins to a user (homework, test, attendance, etc.)
     */
    public function reward(User $user, int $amount, string $description, ?string $refType = null, ?int $refId = null): Transaction
    {
        if ($amount <= 0) throw new \InvalidArgumentException('Reward amount must be positive.');

        // Only active students can earn bonus coins
        if ($user->isStudent() && !$user->isActiveStudent()) {
            throw new \Exception('Студент не активний — нарахування монет неможливе.');
        }

        return DB::transaction(function () use ($user, $amount, $description, $refType, $refId) {
            $wallet = $user->getOrCreateWallet();
            $wallet->credit($amount);

            return Transaction::create([
                'user_id' => $user->id,
                'type' => 'reward',
                'amount' => $amount,
                'description' => $description,
                'reference_type' => $refType,
                'reference_id' => $refId,
            ]);
        });
    }

    /**
     * Deduct coins (penalty, purchase, etc.)
     */
    public function deduct(User $user, int $amount, string $type, string $description, ?string $refType = null, ?int $refId = null): Transaction
    {
        if ($amount <= 0) throw new \InvalidArgumentException('Amount must be positive.');

        return DB::transaction(function () use ($user, $amount, $type, $description, $refType, $refId) {
            $wallet = $user->getOrCreateWallet();
            if (!$wallet->debit($amount)) {
                throw new \Exception('Недостатньо монет на балансі.');
            }

            return Transaction::create([
                'user_id' => $user->id,
                'type' => $type,
                'amount' => -$amount,
                'description' => $description,
                'reference_type' => $refType,
                'reference_id' => $refId,
            ]);
        });
    }

    /**
     * Top-up via LiqPay callback
     */
    public function deposit(User $user, int $amount, string $orderId, string $status): Transaction
    {
        return DB::transaction(function () use ($user, $amount, $orderId, $status) {
            $wallet = $user->getOrCreateWallet();
            $wallet->credit($amount);

            return Transaction::create([
                'user_id' => $user->id,
                'type' => 'deposit',
                'amount' => $amount,
                'description' => 'Поповнення карткою',
                'liqpay_order_id' => $orderId,
                'liqpay_status' => $status,
            ]);
        });
    }

    /**
     * Transfer between users (1% commission)
     */
    public function transfer(User $sender, User $recipient, int $amount, ?string $comment = null): array
    {
        if ($amount <= 0) throw new \InvalidArgumentException('Сума повинна бути більше 0.');
        if ($sender->id === $recipient->id) throw new \Exception('Не можна переказати самому собі.');

        $commission = max(1, (int) ceil($amount * 0.01));
        $total = $amount + $commission;

        return DB::transaction(function () use ($sender, $recipient, $amount, $commission, $total, $comment) {
            $senderWallet = $sender->getOrCreateWallet();
            $recipientWallet = $recipient->getOrCreateWallet();

            if (!$senderWallet->debit($total)) {
                throw new \Exception('Недостатньо монет (потрібно ' . $total . ' з урахуванням комісії).');
            }

            $recipientWallet->credit($amount);

            $txOut = Transaction::create([
                'user_id' => $sender->id,
                'type' => 'transfer_out',
                'amount' => -$total,
                'commission_amount' => $commission,
                'description' => 'Переказ для ' . $recipient->full_name . ($comment ? ': ' . $comment : ''),
                'related_user_id' => $recipient->id,
            ]);

            $txIn = Transaction::create([
                'user_id' => $recipient->id,
                'type' => 'transfer_in',
                'amount' => $amount,
                'description' => 'Переказ від ' . $sender->full_name . ($comment ? ': ' . $comment : ''),
                'related_user_id' => $sender->id,
            ]);

            // Commission to platform (tracked but no wallet — it's profit)
            Transaction::create([
                'user_id' => $sender->id,
                'type' => 'commission',
                'amount' => -$commission,
                'description' => 'Комісія за переказ 1%',
                'related_user_id' => $recipient->id,
            ]);

            return [$txOut, $txIn];
        });
    }

    /**
     * Request cash withdrawal (min 100, multiple of 100)
     */
    public function requestWithdrawal(User $user, int $amount): WithdrawalRequest
    {
        if (!WithdrawalRequest::isValidAmount($amount)) {
            throw new \Exception('Сума виведення повинна бути мінімум 100 та кратна 100.');
        }

        return DB::transaction(function () use ($user, $amount) {
            $wallet = $user->getOrCreateWallet();
            if (!$wallet->debit($amount)) {
                throw new \Exception('Недостатньо монет на балансі.');
            }

            Transaction::create([
                'user_id' => $user->id,
                'type' => 'withdrawal',
                'amount' => -$amount,
                'description' => 'Запит на виведення готівки',
                'status' => 'pending',
            ]);

            return WithdrawalRequest::create([
                'user_id' => $user->id,
                'amount' => $amount,
            ]);
        });
    }

    /**
     * Approve withdrawal
     */
    public function approveWithdrawal(WithdrawalRequest $request, User $admin, string $note): void
    {
        $request->update([
            'status' => 'approved',
            'pickup_note' => $note,
            'processed_by' => $admin->id,
            'processed_at' => now(),
        ]);

        Transaction::where('user_id', $request->user_id)
            ->where('type', 'withdrawal')
            ->where('status', 'pending')
            ->latest()
            ->first()
            ?->update(['status' => 'completed', 'description' => 'Видача готівки', 'admin_note' => $note]);
    }

    /**
     * Reject withdrawal (refund coins)
     */
    public function rejectWithdrawal(WithdrawalRequest $request, User $admin): void
    {
        DB::transaction(function () use ($request, $admin) {
            $wallet = $request->user->getOrCreateWallet();
            $wallet->credit($request->amount);

            $request->update([
                'status' => 'rejected',
                'processed_by' => $admin->id,
                'processed_at' => now(),
            ]);

            Transaction::create([
                'user_id' => $request->user_id,
                'type' => 'refund',
                'amount' => $request->amount,
                'description' => 'Повернення — запит на виведення відхилено',
            ]);
        });
    }

    /**
     * Donate to academy (500+ = VIP)
     */
    public function donate(User $user, int $amount): void
    {
        DB::transaction(function () use ($user, $amount) {
            $this->deduct($user, $amount, 'donation', 'Донат академії');

            $totalDonated = $user->totalDonated() + $amount;
            Donation::create([
                'user_id' => $user->id,
                'amount' => $amount,
                'total_donated' => $totalDonated,
            ]);

            if ($totalDonated >= 500 && !$user->isVip()) {
                app(VipService::class)->grantVip($user, 'Донат 500+ монет');
            }
        });
    }

    /**
     * Pay for course (with optional discount)
     */
    public function payCourse(User $user, $course, int $amount, float $discountPercent = 0): Transaction
    {
        $finalAmount = (int) round($amount * (1 - $discountPercent / 100));

        return DB::transaction(function () use ($user, $course, $finalAmount, $discountPercent) {
            $this->deduct($user, $finalAmount, 'course_payment', "Оплата курсу: {$course->title}");

            // Update enrollment
            $user->enrollments()->updateExistingPivot($course->id, [
                'is_paid' => true,
                'paid_amount' => $finalAmount,
                'discount_percent' => $discountPercent,
                'status' => 'active',
                'enrolled_at' => now(),
                'active_until' => now()->addYear(),
            ]);

            return Transaction::where('user_id', $user->id)
                ->where('type', 'course_payment')
                ->latest()
                ->first();
        });
    }
}
