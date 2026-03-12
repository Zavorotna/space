<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BonusInventory extends Model
{
    protected $table = 'bonus_inventory';
    protected $fillable = ['user_id', 'course_id', 'type', 'quantity', 'used', 'sellable'];
    protected function casts(): array { return ['sellable' => 'boolean']; }

    public function user() { return $this->belongsTo(User::class); }
    public function course() { return $this->belongsTo(Course::class); }
    public function usages() { return $this->hasMany(BonusUsage::class, 'inventory_id'); }

    public function remaining(): int { return $this->quantity - $this->used; }

    public function useOne(string $onType, int $onId): bool
    {
        if ($this->remaining() <= 0) return false;
        $this->increment('used');
        BonusUsage::create([
            'inventory_id' => $this->id,
            'user_id' => $this->user_id,
            'used_on_type' => $onType,
            'used_on_id' => $onId,
        ]);
        return true;
    }

    public static function priceForType(string $type): int
    {
        return match($type) {
            'test_hint' => 15,
            'homework_freeze' => 15,
            'graduation_freeze' => 50,
            default => 0,
        };
    }
}
