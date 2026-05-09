<?php

namespace App\Http\Controllers;

use App\Models\{AdditionalMaterial, MaterialPurchase, Course};
use App\Services\WalletService;
use Illuminate\Http\Request;

class AdditionalMaterialController extends Controller
{
    public function store(Request $request, Course $course)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'url' => 'nullable|url',
            'price_coins' => 'nullable|integer|min:0',
        ]);

        $validated['course_id']   = $course->id;
        $validated['teacher_id']  = $request->user()->id;
        $validated['price_coins'] = $validated['price_coins'] ?? 0;

        AdditionalMaterial::create($validated);
        return back()->with('success', 'Матеріал додано.');
    }

    public function purchase(Request $request, AdditionalMaterial $material)
    {
        $user = $request->user();

        if (MaterialPurchase::where('material_id', $material->id)->where('user_id', $user->id)->exists()) {
            return back()->with('info', 'Ви вже придбали цей матеріал.');
        }

        if ($material->price_coins > 0) {
            $platformFee = (int) ceil($material->price_coins * 0.10); // 10% platform fee
            try {
                app(WalletService::class)->deduct($user, $material->price_coins, 'material_purchase', "Матеріал: {$material->title}");
                // Credit teacher (minus 10%)
                $teacherAmount = $material->price_coins - $platformFee;
                app(WalletService::class)->reward($material->teacher, $teacherAmount, "Продаж матеріалу: {$material->title}");
            } catch (\Exception $e) {
                return back()->with('error', $e->getMessage());
            }
        }

        MaterialPurchase::create([
            'material_id' => $material->id,
            'user_id' => $user->id,
            'price_paid' => $material->price_coins,
        ]);

        return back()->with('success', 'Матеріал придбано.');
    }
}
