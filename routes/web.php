<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\{
    DashboardController,
    CourseController,
    ScheduleController,
    HomeworkController,
    TestController,
    GraduationProjectController,
    WalletController,
    BonusController,
    ShopController,
    CertificateController,
    ResumeController,
    ProfileController,
    NoteController,
    NotificationController,
    AchievementController,
    AdminController,
    AdditionalMaterialController,
    LiqPayCallbackController,
};
use App\Http\Controllers\Auth\{RegisterController, LoginController, GoogleController};

// ═══════════════════════════════════════════════════════════════
// GUEST (public)
// ═══════════════════════════════════════════════════════════════
Route::get('/', function () {
    if (auth()->check()) return redirect()->route('dashboard');
    return view('public.home');
})->name('home');

// ── Auth ───────────────────────────────────────────────────────
Route::middleware('guest')->group(function () {
    Route::get('/register', [RegisterController::class, 'showForm'])->name('register');
    Route::post('/register', [RegisterController::class, 'register']);
    Route::get('/login', [LoginController::class, 'showForm'])->name('login');
    Route::post('/login', [LoginController::class, 'login']);
});

Route::post('/logout', [LoginController::class, 'logout'])->name('logout')->middleware('auth');

// ── Google OAuth ───────────────────────────────────────────────
Route::get('/auth/google', [GoogleController::class, 'redirect'])->name('auth.google');
Route::get('/auth/google/callback', [GoogleController::class, 'callback']);

// ── LiqPay Callback (no auth, server-to-server) ───────────────
Route::post('/liqpay/callback', [LiqPayCallbackController::class, 'handle'])->name('liqpay.callback');

// ── Public pages ───────────────────────────────────────────────
Route::get('/courses', [CourseController::class, 'publicIndex'])->name('courses.public');
Route::get('/courses/{course}/detail', [CourseController::class, 'publicShow'])->name('courses.detail');

// ═══════════════════════════════════════════════════════════════
// AUTHENTICATED (all roles)
// ═══════════════════════════════════════════════════════════════
Route::middleware(['auth', \App\Http\Middleware\TrackLoginStreak::class])->group(function () {

    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    // ── Profile ────────────────────────────────────────────────
    Route::get('/profile/edit', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::put('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::get('/profile/{user}', [ProfileController::class, 'show'])->name('profile.show');
    Route::post('/profile/avatar-extra', [ProfileController::class, 'uploadExtraAvatar'])->name('profile.avatar.extra');

    // ── Notifications ──────────────────────────────────────────
    Route::get('/notifications', [NotificationController::class, 'index'])->name('notifications.index');
    Route::post('/notifications/{notification}/read', [NotificationController::class, 'markRead'])->name('notifications.read');
    Route::post('/notifications/read-all', [NotificationController::class, 'markAllRead'])->name('notifications.readAll');
    Route::get('/notifications/unread-count', [NotificationController::class, 'unreadCount'])->name('notifications.unreadCount');
    Route::post('/notifications/push-subscribe', [NotificationController::class, 'subscribePush'])->name('notifications.pushSubscribe');

    // ── Notes ──────────────────────────────────────────────────
    Route::get('/notes', [NoteController::class, 'index'])->name('notes.index');
    Route::post('/notes', [NoteController::class, 'store'])->name('notes.store');
    Route::put('/notes/{note}', [NoteController::class, 'update'])->name('notes.update');
    Route::delete('/notes/{note}', [NoteController::class, 'destroy'])->name('notes.destroy');
    Route::post('/notes/{note}/read', [NoteController::class, 'markRead'])->name('notes.read');

    // ── Shop (all registered users can view) ───────────────────
    Route::get('/shop', [ShopController::class, 'index'])->name('shop.index');
    Route::get('/shop/{product}', [ShopController::class, 'show'])->name('shop.show');

    // ── Resumes (registered can view) ──────────────────────────
    Route::get('/resumes', [ResumeController::class, 'index'])->name('resumes.index');
    Route::get('/resumes/{resume}', [ResumeController::class, 'show'])->name('resumes.show');

    // ── Achievements ───────────────────────────────────────────
    Route::get('/achievements', [AchievementController::class, 'index'])->name('achievements.index');

    // ═════════════════════════════════════════════════════════
    // STUDENT routes
    // ═════════════════════════════════════════════════════════
    Route::middleware(\App\Http\Middleware\RoleMiddleware::class . ':student,teacher,admin,superadmin')->group(function () {

        // Course enrollment
        Route::post('/courses/{course}/apply', [CourseController::class, 'apply'])->name('courses.apply');
        Route::get('/courses/{course}/student', [CourseController::class, 'studentShow'])->name('courses.student.show');
        Route::post('/courses/{course}/review', [CourseController::class, 'submitReview'])->name('courses.review');
        Route::get('/courses/{course}/pay', [CourseController::class, 'payForm'])->name('courses.pay');
        Route::post('/courses/{course}/pay', [CourseController::class, 'payProcess'])->name('courses.pay.process');
        Route::get('/courses/{course}/pay/result', [CourseController::class, 'payResult'])->name('courses.pay.result');

        // Schedule
        Route::get('/schedule', [ScheduleController::class, 'index'])->name('schedule.index');
        Route::get('/schedule/calendar', [ScheduleController::class, 'calendarJson'])->name('schedule.calendar');

        // Homework (submit)
        Route::get('/homework/{homework}/submit', [HomeworkController::class, 'showSubmitForm'])->name('homework.submitForm');
        Route::post('/homework/{homework}/submit', [HomeworkController::class, 'submit'])->name('homework.submit');
        Route::post('/homework-submission/{submission}/freeze', [HomeworkController::class, 'freezeDeadline'])->name('homework.freeze');

        // Tests (take)
        Route::get('/tests/{test}', [TestController::class, 'show'])->name('tests.show');
        Route::post('/tests/{test}/start', [TestController::class, 'start'])->name('tests.start');
        Route::post('/test-attempts/{attempt}/submit', [TestController::class, 'submit'])->name('tests.submit');
        Route::get('/test-attempts/{attempt}/result', [TestController::class, 'result'])->name('tests.result');
        Route::post('/test-questions/{question}/hint', [TestController::class, 'useHint'])->name('tests.hint');

        // Graduation project (submit)
        Route::post('/graduation-projects/{project}/submit', [GraduationProjectController::class, 'submit'])->name('graduation.submit');
        Route::post('/graduation-submissions/{submission}/freeze', [GraduationProjectController::class, 'freezeDeadline'])->name('graduation.freeze');

        // Wallet
        Route::get('/wallet', [WalletController::class, 'index'])->name('wallet.index');
        Route::get('/wallet/topup', [WalletController::class, 'topUpForm'])->name('wallet.topup');
        Route::post('/wallet/topup', [WalletController::class, 'topUp'])->name('wallet.topup.process');
        Route::get('/wallet/transfer', [WalletController::class, 'transferForm'])->name('wallet.transfer');
        Route::post('/wallet/transfer', [WalletController::class, 'transfer'])->name('wallet.transfer.process');
        Route::get('/wallet/withdraw', [WalletController::class, 'withdrawForm'])->name('wallet.withdraw');
        Route::post('/wallet/withdraw', [WalletController::class, 'withdraw'])->name('wallet.withdraw.process');
        Route::post('/wallet/vip', [WalletController::class, 'purchaseVip'])->name('wallet.vip');
        Route::post('/wallet/donate', [WalletController::class, 'donate'])->name('wallet.donate');

        // Bonuses
        Route::get('/bonuses', [BonusController::class, 'index'])->name('bonuses.index');
        Route::post('/bonuses/purchase', [BonusController::class, 'purchase'])->name('bonuses.purchase');
        Route::post('/bonuses/{inventory}/sell', [BonusController::class, 'sell'])->name('bonuses.sell');

        // Shop purchase
        Route::post('/shop/{product}/purchase', [ShopController::class, 'purchase'])->name('shop.purchase');

        // Additional materials
        Route::post('/materials/{material}/purchase', [AdditionalMaterialController::class, 'purchase'])->name('materials.purchase');

        // Certificates
        Route::get('/certificates', [CertificateController::class, 'index'])->name('certificates.index');
        Route::get('/certificates/{certificate}', [CertificateController::class, 'show'])->name('certificates.show');

        // Resume
        Route::get('/resume/edit', [ResumeController::class, 'edit'])->name('resume.edit');
        Route::put('/resume', [ResumeController::class, 'update'])->name('resume.update');
        Route::post('/resume/publish', [ResumeController::class, 'publish'])->name('resume.publish');
    });

    // ═════════════════════════════════════════════════════════
    // TEACHER routes
    // ═════════════════════════════════════════════════════════
    Route::middleware(\App\Http\Middleware\RoleMiddleware::class . ':teacher,admin,superadmin')->prefix('teacher')->name('teacher.')->group(function () {

        // Courses
        Route::get('/courses/create', [CourseController::class, 'create'])->name('courses.create');
        Route::post('/courses', [CourseController::class, 'store'])->name('courses.store');
        Route::get('/courses/{course}/edit', [CourseController::class, 'edit'])->name('courses.edit');
        Route::put('/courses/{course}', [CourseController::class, 'update'])->name('courses.update');
        Route::post('/courses/{course}/duplicate', [CourseController::class, 'duplicate'])->name('courses.duplicate');
        Route::get('/courses/{course}/applications', [CourseController::class, 'applications'])->name('courses.applications');
        Route::post('/applications/{application}/approve', [CourseController::class, 'approveApplication'])->name('applications.approve');
        Route::post('/courses/{course}/add-student', [CourseController::class, 'addStudent'])->name('courses.addStudent');
        Route::get('/courses/{course}/students', [CourseController::class, 'students'])->name('courses.students');
        Route::put('/courses/{course}/end-date', [CourseController::class, 'updateEndDate'])->name('courses.endDate');

        // Homework
        Route::post('/courses/{course}/homework', [HomeworkController::class, 'store'])->name('homework.store');
        Route::put('/homework/{homework}', [HomeworkController::class, 'update'])->name('homework.update');
        Route::delete('/homework/{homework}', [HomeworkController::class, 'destroy'])->name('homework.destroy');
        Route::get('/homework/{homework}/submissions', [HomeworkController::class, 'submissions'])->name('homework.submissions');
        Route::post('/homework-submissions/{submission}/review', [HomeworkController::class, 'review'])->name('homework.review');

        // Tests
        Route::post('/courses/{course}/tests', [TestController::class, 'store'])->name('tests.store');
        Route::get('/tests/{test}/edit', [TestController::class, 'edit'])->name('tests.edit');
        Route::put('/tests/{test}', [TestController::class, 'update'])->name('tests.update');
        Route::post('/tests/{test}/questions', [TestController::class, 'addQuestion'])->name('tests.addQuestion');
        Route::put('/test-questions/{question}', [TestController::class, 'updateQuestion'])->name('tests.updateQuestion');
        Route::delete('/test-questions/{question}', [TestController::class, 'deleteQuestion'])->name('tests.deleteQuestion');
        Route::get('/tests/{test}/statistics', [TestController::class, 'statistics'])->name('tests.statistics');

        // Graduation projects
        Route::post('/courses/{course}/graduation', [GraduationProjectController::class, 'store'])->name('graduation.store');
        Route::put('/graduation-projects/{project}', [GraduationProjectController::class, 'update'])->name('graduation.update');
        Route::get('/graduation-projects/{project}/submissions', [GraduationProjectController::class, 'submissions'])->name('graduation.submissions');
        Route::post('/graduation-submissions/{submission}/review', [GraduationProjectController::class, 'review'])->name('graduation.review');

        // Additional materials
        Route::post('/courses/{course}/materials', [AdditionalMaterialController::class, 'store'])->name('materials.store');

        // Certificates
        Route::post('/courses/{course}/certificates/{user}', [CertificateController::class, 'issue'])->name('certificates.issue');

        // Schedule management
        Route::post('/schedule', [ScheduleController::class, 'store'])->name('schedule.store');
        Route::put('/schedule/{lesson}', [ScheduleController::class, 'update'])->name('schedule.update');
        Route::delete('/schedule/{lesson}', [ScheduleController::class, 'destroy'])->name('schedule.destroy');
        Route::post('/schedule/{lesson}/attendance', [ScheduleController::class, 'confirmAttendance'])->name('schedule.attendance');
    });

    // ═════════════════════════════════════════════════════════
    // ADMIN routes
    // ═════════════════════════════════════════════════════════
    Route::middleware(\App\Http\Middleware\RoleMiddleware::class . ':admin,superadmin')->prefix('admin')->name('admin.')->group(function () {
        Route::get('/users', [AdminController::class, 'users'])->name('users');
        Route::put('/users/{user}/role', [AdminController::class, 'updateUserRole'])->name('users.role');
        Route::post('/users/link-parent', [AdminController::class, 'linkParent'])->name('users.linkParent');

        Route::get('/locations', [AdminController::class, 'locations'])->name('locations');
        Route::post('/locations', [AdminController::class, 'storeLocation'])->name('locations.store');
        Route::post('/locations/{location}/classrooms', [AdminController::class, 'storeClassroom'])->name('classrooms.store');

        // Shop management
        Route::get('/shop', [ShopController::class, 'adminIndex'])->name('shop.index');
        Route::get('/shop/create', [ShopController::class, 'create'])->name('shop.create');
        Route::post('/shop', [ShopController::class, 'store'])->name('shop.store');
        Route::put('/shop/{product}', [ShopController::class, 'adminUpdate'])->name('shop.update');

        Route::post('/achievements/seed', [AdminController::class, 'seedAchievements'])->name('achievements.seed');
    });

    // ═════════════════════════════════════════════════════════
    // SUPERADMIN routes
    // ═════════════════════════════════════════════════════════
    Route::middleware(\App\Http\Middleware\RoleMiddleware::class . ':superadmin')->prefix('superadmin')->name('superadmin.')->group(function () {
        Route::get('/transactions', [WalletController::class, 'allTransactions'])->name('transactions');
        Route::get('/withdrawals', [WalletController::class, 'withdrawalRequests'])->name('withdrawals');
        Route::post('/withdrawals/{withdrawal}/approve', [WalletController::class, 'approveWithdrawal'])->name('withdrawals.approve');
        Route::post('/withdrawals/{withdrawal}/reject', [WalletController::class, 'rejectWithdrawal'])->name('withdrawals.reject');
        Route::put('/courses/{course}/liqpay', [AdminController::class, 'courseLiqpay'])->name('courses.liqpay');
        Route::post('/users/{user}/toggle-trusted', [AdminController::class, 'toggleTrustedTeacher'])->name('users.toggleTrusted');
    });

    // ═════════════════════════════════════════════════════════
    // PARENT routes
    // ═════════════════════════════════════════════════════════
    Route::middleware(\App\Http\Middleware\RoleMiddleware::class . ':parent')->prefix('parent')->name('parent.')->group(function () {
        // Parent dashboard is handled by DashboardController
    });
});
