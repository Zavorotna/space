<?php

namespace App\Services;

use App\Models\{User, BonusInventory, Course};

class BonusService
{
    protected WalletService $wallet;

    public function __construct(WalletService $wallet)
    {
        $this->wallet = $wallet;
    }

    /**
     * Purchase bonus item
     */
    public function purchase(User $user, string $type, Course $course, int $quantity = 1): BonusInventory
    {
        $pricePerUnit = BonusInventory::priceForType($type);

        // VIP 10% discount
        if ($user->isVip()) {
            $pricePerUnit = (int) ceil($pricePerUnit * 0.9);
        }

        $total = $pricePerUnit * $quantity;
        $this->wallet->deduct($user, $total, 'bonus_purchase', "Бонус: {$type} x{$quantity}");

        $inventory = BonusInventory::firstOrCreate(
            ['user_id' => $user->id, 'course_id' => $course->id, 'type' => $type],
            ['quantity' => 0, 'used' => 0]
        );
        $inventory->increment('quantity', $quantity);

        return $inventory;
    }

    /**
     * Use a test hint
     */
    public function useTestHint(User $user, int $courseId, int $questionId): bool
    {
        $inventory = BonusInventory::where('user_id', $user->id)
            ->where('course_id', $courseId)
            ->where('type', 'test_hint')
            ->first();

        if (!$inventory || $inventory->remaining() <= 0) return false;

        return $inventory->useOne('test_question', $questionId);
    }

    /**
     * Apply homework deadline freeze
     */
    public function freezeHomeworkDeadline(User $user, $submission, int $days): bool
    {
        if ($days < 1 || $submission->freeze_days_used + $days > 5) return false;

        $inventory = BonusInventory::where('user_id', $user->id)
            ->where('course_id', $submission->homework->course_id)
            ->where('type', 'homework_freeze')
            ->first();

        if (!$inventory || $inventory->remaining() < $days) return false;

        for ($i = 0; $i < $days; $i++) {
            $inventory->useOne('homework', $submission->id);
        }

        $deadline = $submission->effective_deadline ?? $submission->homework->deadline;
        $submission->update([
            'freeze_days_used' => $submission->freeze_days_used + $days,
            'effective_deadline' => \Carbon\Carbon::parse($deadline)->addDays($days),
        ]);

        return true;
    }

    /**
     * Apply graduation deadline freeze
     */
    public function freezeGraduationDeadline(User $user, $submission, int $days): bool
    {
        if ($days < 1 || $submission->freeze_days_used + $days > 20) return false;

        $inventory = BonusInventory::where('user_id', $user->id)
            ->where('course_id', $submission->project->course_id)
            ->where('type', 'graduation_freeze')
            ->first();

        if (!$inventory || $inventory->remaining() < $days) return false;

        for ($i = 0; $i < $days; $i++) {
            $inventory->useOne('graduation', $submission->id);
        }

        $deadline = $submission->effective_deadline ?? $submission->project->deadline;
        $submission->update([
            'freeze_days_used' => $submission->freeze_days_used + $days,
            'effective_deadline' => \Carbon\Carbon::parse($deadline)->addDays($days),
        ]);

        return true;
    }

    /**
     * Sell unused bonuses after course completion (at -10%)
     */
    public function sellUnused(User $user, BonusInventory $inventory): int
    {
        $remaining = $inventory->remaining();
        if ($remaining <= 0) return 0;

        $pricePerUnit = BonusInventory::priceForType($inventory->type);
        $sellPrice = (int) floor($pricePerUnit * 0.9 * $remaining);

        $this->wallet->reward($user, $sellPrice, "Продаж бонусів: {$inventory->type} x{$remaining}");
        $inventory->update(['used' => $inventory->quantity, 'sellable' => false]);

        return $sellPrice;
    }
}
