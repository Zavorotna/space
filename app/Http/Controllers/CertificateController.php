<?php

namespace App\Http\Controllers;

use App\Models\{Certificate, Course};
use Illuminate\Http\Request;

class CertificateController extends Controller
{
    public function index(Request $request)
    {
        $certificates = Certificate::where('user_id', $request->user()->id)
            ->with(['course', 'media'])
            ->get();
        return view('student.certificates', compact('certificates'));
    }

    public function show(Certificate $certificate)
    {
        $certificate->load(['course', 'media', 'user']);
        return view('student.certificate-show', compact('certificate'));
    }

    /**
     * Admin/Teacher: issue certificate
     */
    public function issue(Request $request, Course $course, $userId)
    {
        $enrollment = $course->students()->where('user_id', $userId)->first();
        if (!$enrollment) return back()->with('error', 'Студент не на курсі.');

        $successRate = $enrollment->pivot->success_rate;
        $graduationDefended = $course->graduationProject
            ? $course->graduationProject->submissions()
                ->where('user_id', $userId)->where('is_defended', true)->exists()
            : false;

        $type = Certificate::determineType($successRate, $graduationDefended);
        $discount = Certificate::discountForType($type);

        $certificate = Certificate::updateOrCreate(
            ['course_id' => $course->id, 'user_id' => $userId],
            [
                'type' => $type,
                'success_rate' => $successRate,
                'discount_next_course' => $discount,
                'certificate_number' => 'HS-' . strtoupper(uniqid()),
            ]
        );

        if ($request->hasFile('certificate_image')) {
            $certificate->addMediaFromRequest('certificate_image')
                ->toMediaCollection('certificate_image');
        }

        return back()->with('success', "Сертифікат ({$type}) видано.");
    }
}
