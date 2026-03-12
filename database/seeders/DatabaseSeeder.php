<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\{User, Wallet, Achievement, Location, Classroom};
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // ── Superadmin ───────────────────────────────────────────
        $admin = User::create([
            'first_name' => 'Super',
            'last_name' => 'Admin',
            'phone' => '+380000000000',
            'email' => 'admin@hashtagspace.ua',
            'password' => Hash::make('password'),
            'role' => 'superadmin',
        ]);
        Wallet::create(['user_id' => $admin->id, 'balance' => 0]);

        // ── Demo teacher ─────────────────────────────────────────
        $teacher = User::create([
            'first_name' => 'Олена',
            'last_name' => 'Шевченко',
            'phone' => '+380991111111',
            'email' => 'teacher@hashtagspace.ua',
            'password' => Hash::make('password'),
            'role' => 'teacher',
        ]);
        Wallet::create(['user_id' => $teacher->id, 'balance' => 0]);

        // ── Demo student ─────────────────────────────────────────
        $student = User::create([
            'first_name' => 'Іван',
            'last_name' => 'Петренко',
            'phone' => '+380992222222',
            'email' => 'student@hashtagspace.ua',
            'password' => Hash::make('password'),
            'role' => 'student',
        ]);
        Wallet::create(['user_id' => $student->id, 'balance' => 100]);

        // ── Demo parent ──────────────────────────────────────────
        $parent = User::create([
            'first_name' => 'Марія',
            'last_name' => 'Петренко',
            'phone' => '+380993333333',
            'email' => 'parent@hashtagspace.ua',
            'password' => Hash::make('password'),
            'role' => 'parent',
        ]);
        Wallet::create(['user_id' => $parent->id, 'balance' => 0]);
        $parent->children()->attach($student->id);

        // ── Default location ─────────────────────────────────────
        $location = Location::create([
            'name' => 'Головний офіс Hashtag Space',
            'address' => 'м. Хмельницький, вул. Проскурівська 42',
            'work_start' => '09:00',
            'work_end' => '21:00',
        ]);
        Classroom::create([
            'location_id' => $location->id,
            'name' => 'Клас A',
            'capacity' => 12,
        ]);
        Classroom::create([
            'location_id' => $location->id,
            'name' => 'Клас B',
            'capacity' => 8,
        ]);

        // ── Achievements ─────────────────────────────────────────
        $achievements = [
            ['slug' => 'first_login', 'title' => 'Перший візит', 'description' => 'Ваш перший вхід в систему', 'icon' => '🎉', 'reward_coins' => 0, 'type' => 'custom'],
            ['slug' => 'streak_10', 'title' => 'Тижневий марафон', 'description' => '10 днів поспіль', 'icon' => '🔥', 'reward_coins' => 25, 'type' => 'login_streak', 'threshold' => 10],
            ['slug' => 'streak_25', 'title' => 'Впевнений крок', 'description' => '25 днів поспіль', 'icon' => '⚡', 'reward_coins' => 50, 'type' => 'login_streak', 'threshold' => 25],
            ['slug' => 'streak_50', 'title' => 'Незламний', 'description' => '50 днів поспіль', 'icon' => '💪', 'reward_coins' => 100, 'type' => 'login_streak', 'threshold' => 50],
            ['slug' => 'streak_100', 'title' => 'Сотня', 'description' => '100 днів поспіль', 'icon' => '💯', 'reward_coins' => 200, 'type' => 'login_streak', 'threshold' => 100],
            ['slug' => 'streak_250', 'title' => 'Легенда', 'description' => '250 днів поспіль', 'icon' => '🏆', 'reward_coins' => 500, 'type' => 'login_streak', 'threshold' => 250],
            ['slug' => 'streak_500', 'title' => 'Титан', 'description' => '500 днів поспіль', 'icon' => '👑', 'reward_coins' => 1000, 'type' => 'login_streak', 'threshold' => 500],
            ['slug' => 'streak_1000', 'title' => 'Безсмертний', 'description' => '1000 днів поспіль', 'icon' => '🌟', 'reward_coins' => 2500, 'type' => 'login_streak', 'threshold' => 1000],
            ['slug' => 'first_test', 'title' => 'Перший тест', 'description' => 'Пройшли перший тест', 'icon' => '📝', 'reward_coins' => 0, 'type' => 'custom'],
            ['slug' => 'first_homework', 'title' => 'Перша домашка', 'description' => 'Здали першу домашню роботу', 'icon' => '📚', 'reward_coins' => 0, 'type' => 'custom'],
            ['slug' => 'first_certificate', 'title' => 'Сертифікат', 'description' => 'Отримали перший сертифікат', 'icon' => '🎓', 'reward_coins' => 0, 'type' => 'custom'],
            ['slug' => 'vip_member', 'title' => 'VIP учасник', 'description' => 'Стали VIP', 'icon' => '⭐', 'reward_coins' => 0, 'type' => 'custom'],
            ['slug' => 'donor', 'title' => 'Меценат', 'description' => 'Зробили донат', 'icon' => '❤️', 'reward_coins' => 0, 'type' => 'custom'],
        ];

        foreach ($achievements as $a) {
            Achievement::create($a);
        }
    }
}
