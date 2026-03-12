<?php

namespace App\Http\Controllers;

use App\Models\{BonusInventory, Course};
use App\Services\BonusService;
use Illuminate\Http\Request;

class BonusController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();
        $inventory = BonusInventory::where('user_id', $user->id)
            ->with('course')
            ->get()
            ->groupBy('course_id');

        return view('wallet.bonuses', compact('inventory'));
    }

    public function purchase(Request $request)
    {
        $validated = $request->validate([
            'type' => 'required|in:test_hint,homework_freeze,graduation_freeze',
            'course_id' => 'required|exists:courses,id',
            'quantity' => 'required|integer|min:1|max:20',
        ]);

        try {
            $course = Course::findOrFail($validated['course_id']);
            app(BonusService::class)->purchase(
                $request->user(), $validated['type'], $course, $validated['quantity']
            );
            return back()->with('success', 'Бонус придбано.');
        } catch (\Exception $e) {
            return back()->with('error', $e->getMessage());
        }
    }

    public function sell(Request $request, BonusInventory $inventory)
    {
        if ($inventory->user_id !== $request->user()->id) abort(403);

        try {
            $amount = app(BonusService::class)->sellUnused($request->user(), $inventory);
            return back()->with('success', "Продано за {$amount} монет.");
        } catch (\Exception $e) {
            return back()->with('error', $e->getMessage());
        }
    }
}
