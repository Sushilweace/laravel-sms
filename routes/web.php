<?php

use App\Http\Controllers\AcademicController;
use App\Http\Controllers\Admin\ErrorLogController;
use App\Http\Controllers\Admin\MailLogController;
use App\Http\Controllers\Admin\SchoolManagementController;
use App\Http\Controllers\AiController;
use App\Http\Controllers\AttendanceController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ExamController;
use App\Http\Controllers\FeeController;
use App\Http\Controllers\NoticeController;
use App\Http\Controllers\SchoolSettingsController;
use App\Http\Controllers\StaffController;
use App\Http\Controllers\StudentController;
use Illuminate\Support\Facades\Route;

// Public & Auth Routes
Route::get('/', function () {
    return redirect()->route('dashboard');
});

Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthController::class, 'showLoginForm'])->name('login');
    Route::post('/login', [AuthController::class, 'login']);
    Route::get('/quick-login/{role}', [AuthController::class, 'quickLogin'])->name('quick-login');
});

Route::post('/logout', [AuthController::class, 'logout'])->name('logout')->middleware('auth');

// Protected Application Routes
Route::middleware('auth')->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    // Academic Setup
    Route::prefix('academic')->name('academic.')->group(function () {
        Route::get('/', [AcademicController::class, 'index'])->name('index');
        Route::post('/years', [AcademicController::class, 'storeYear'])->name('years.store');
        Route::post('/years/{year}/current', [AcademicController::class, 'setCurrentYear'])->name('years.set-current');
        Route::post('/years/{year}/lock', [AcademicController::class, 'lockYear'])->name('years.lock');
        Route::post('/classes', [AcademicController::class, 'storeClass'])->name('classes.store');
        Route::post('/sections', [AcademicController::class, 'storeSection'])->name('sections.store');
        Route::post('/subjects', [AcademicController::class, 'storeSubject'])->name('subjects.store');
        Route::post('/assign-teacher', [AcademicController::class, 'assignTeaching'])->name('assign-teacher');
    });

    // Students Module
    Route::prefix('students')->name('students.')->middleware('feature:students')->group(function () {
        Route::get('/', [StudentController::class, 'index'])->name('index');
        Route::get('/create', [StudentController::class, 'create'])->name('create');
        Route::post('/', [StudentController::class, 'store'])->name('store');
        Route::get('/promote', [StudentController::class, 'promoteView'])->name('promote');
        Route::post('/promote', [StudentController::class, 'promoteSubmit'])->name('promote.submit');
        Route::get('/{student}', [StudentController::class, 'show'])->name('show');
    });

    // Staff Module
    Route::prefix('staff')->name('staff.')->middleware('feature:staff')->group(function () {
        Route::get('/', [StaffController::class, 'index'])->name('index');
        Route::get('/create', [StaffController::class, 'create'])->name('create');
        Route::post('/', [StaffController::class, 'store'])->name('store');
        Route::get('/{staff}', [StaffController::class, 'show'])->name('show');
    });

    // Attendance Module
    Route::prefix('attendance')->name('attendance.')->middleware('feature:attendance')->group(function () {
        Route::get('/', [AttendanceController::class, 'index'])->name('index');
        Route::post('/', [AttendanceController::class, 'save'])->name('save');
        Route::get('/reports', [AttendanceController::class, 'reports'])->name('reports');
    });

    // Exams & Marks Module
    Route::prefix('exams')->name('exams.')->middleware('feature:exams')->group(function () {
        Route::get('/', [ExamController::class, 'index'])->name('index');
        Route::get('/create', [ExamController::class, 'create'])->name('create');
        Route::post('/', [ExamController::class, 'store'])->name('store');
        Route::get('/{exam}', [ExamController::class, 'show'])->name('show');
        Route::post('/{exam}/open-marks', [ExamController::class, 'openMarks'])->name('open-marks');
        Route::post('/{exam}/lock', [ExamController::class, 'lockExam'])->name('lock');
        Route::post('/{exam}/compute', [ExamController::class, 'compute'])->name('compute');
        Route::post('/{exam}/publish', [ExamController::class, 'publish'])->name('publish');
        Route::get('/{exam}/subject/{examSubject}/marks', [ExamController::class, 'enterMarksView'])->name('enter-marks');
        Route::post('/{exam}/subject/{examSubject}/marks', [ExamController::class, 'saveMarks'])->name('save-marks');
        Route::get('/{exam}/report-card/{enrollment}', [ExamController::class, 'reportCard'])->name('report-card');
        Route::get('/{exam}/marksheet/{enrollment}', [ExamController::class, 'reportCard'])->name('marksheet');
        Route::get('/{exam}/class-marksheet', [ExamController::class, 'classMarksheet'])->name('class-marksheet');
        Route::get('/{exam}/subject/{examSubject}/download-marksheet', [ExamController::class, 'downloadSubjectMarksheet'])->name('subject-marksheet');
    });

    // Fees & Receipts Module
    Route::prefix('fees')->name('fees.')->middleware('feature:fees')->group(function () {
        Route::get('/', [FeeController::class, 'index'])->name('index');
        Route::post('/heads', [FeeController::class, 'storeHead'])->name('heads.store');
        Route::post('/structures', [FeeController::class, 'storeStructure'])->name('structures.store');
        Route::get('/invoices', [FeeController::class, 'invoices'])->name('invoices');
        Route::post('/invoices/generate', [FeeController::class, 'generateInvoices'])->name('invoices.generate');
        Route::get('/invoices/{invoice}/collect', [FeeController::class, 'collectView'])->name('collect');
        Route::post('/invoices/{invoice}/collect', [FeeController::class, 'collectSubmit'])->name('collect.submit');
        Route::post('/payments/{payment}/reverse', [FeeController::class, 'reverse'])->name('payments.reverse');
        Route::get('/receipt/{payment}', [FeeController::class, 'receipt'])->name('receipt');
        Route::get('/defaulters', [FeeController::class, 'defaulters'])->name('defaulters');
    });

    // Notices Module
    Route::prefix('notices')->name('notices.')->middleware('feature:notices')->group(function () {
        Route::get('/', [NoticeController::class, 'index'])->name('index');
        Route::get('/create', [NoticeController::class, 'create'])->name('create');
        Route::post('/', [NoticeController::class, 'store'])->name('store');
        Route::post('/{notice}/ack', [NoticeController::class, 'acknowledge'])->name('ack');
    });

    // AI Assistant
    Route::prefix('ai')->name('ai.')->middleware('feature:ai')->group(function () {
        Route::get('/', [AiController::class, 'index'])->name('index');
        Route::post('/generate-remarks', [AiController::class, 'generateRemarks'])->name('generate-remarks');
        Route::post('/draft-notice', [AiController::class, 'draftNotice'])->name('draft-notice');
        Route::post('/suggestions/{suggestion}/review', [AiController::class, 'reviewSuggestion'])->name('review-suggestion');
    });

    // Administration Zone (Protected by Super Admin & School Admin Roles)
    Route::middleware('role:Super Admin|School Admin')->group(function () {
        // School Management (Multi-Campus Registry & Child Entity Inspector)
        Route::resource('admin/schools', SchoolManagementController::class)->names('admin.schools');

        // School Settings & Feature Package Management
        Route::prefix('settings')->name('settings.')->group(function () {
            Route::get('/school', [SchoolSettingsController::class, 'edit'])->name('school');
            Route::post('/school', [SchoolSettingsController::class, 'update'])->name('school.update');
            Route::post('/school/calculate', [SchoolSettingsController::class, 'calculateApi'])->name('school.calculate');
        });

        // Admin Logs (Error Logs & Mail / Notification Sent Logs)
        Route::prefix('admin/logs')->name('admin.logs.')->group(function () {
            Route::get('/errors', [ErrorLogController::class, 'index'])->name('errors');
            Route::get('/errors/download', [ErrorLogController::class, 'download'])->name('errors.download');
            Route::post('/errors/clear', [ErrorLogController::class, 'clear'])->name('errors.clear');

            Route::get('/mail', [MailLogController::class, 'index'])->name('mail');
            Route::get('/mail/{log}', [MailLogController::class, 'show'])->name('mail.show');
            Route::post('/mail/{log}/resend', [MailLogController::class, 'resend'])->name('mail.resend');
            Route::post('/mail/test', [MailLogController::class, 'sendTest'])->name('mail.test');
        });
    });
});
