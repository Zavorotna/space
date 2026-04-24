<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CalendarEvent extends Model
{
    protected $fillable = ['title', 'type', 'date', 'start_time', 'end_time', 'description', 'created_by'];

    protected $casts = ['date' => 'date'];

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public static function typeLabel(string $type): string
    {
        return match($type) {
            'graduation' => 'Випуск',
            'meeting'    => 'Зустріч',
            'holiday'    => 'Вихідний',
            default      => 'Подія',
        };
    }

    public static function typeColor(string $type): string
    {
        return match($type) {
            'graduation' => '#f5a623',
            'meeting'    => '#27ae60',
            'holiday'    => '#8e44ad',
            default      => '#7f8c8d',
        };
    }
}
