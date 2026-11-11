<?php

use App\Http\Controllers\AdminController;
use App\Http\Controllers\AlertController;
use App\Http\Controllers\AssetController;
use App\Http\Controllers\AttendanceController;
use App\Http\Controllers\BackupController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\LeaveRequestController;
use App\Http\Controllers\NoticeController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\ProctorController;
use App\Http\Controllers\ProctorManagerController;
use App\Http\Controllers\StudentController;
use App\Models\Announcement;
use App\Models\Attendance;
use App\Models\DormitoryBlock;
use App\Models\Room;
use App\Models\StudentAllocation;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Public Routes
|--------------------------------------------------------------------------
*/

Route::get('/', function () {
    $announcements = Announcement::published()->latest('published_at')->take(3)->get();
    $totalBlocks = DormitoryBlock::count();
    $totalRooms = Room::count();
    $totalResidents = StudentAllocation::where('status', StudentAllocation::STATUS_ACTIVE)->count();
    $attTotal = Attendance::count();
    $attPresent = Attendance::where('status', Attendance::STATUS_PRESENT)->count();
    $complianceRate = $attTotal > 0 ? round(($attPresent / $attTotal) * 100, 1) . '%' : '0%';

    $stats = [
        'total_blocks' => $totalBlocks,
        'total_capacity' => (int) Room::sum('capacity') ?: ($totalBlocks * DormitoryBlock::STANDARD_BLOCK_CAPACITY),
        'total_students' => User::where('role', User::ROLE_STUDENT)->where('registration_status', User::REGISTRATION_COMPLETED)->count(),
        'allocated_students' => $totalResidents,
        'total_users' => User::count(),
    ];

    return view('landing', compact('announcements', 'stats', 'totalResidents', 'totalBlocks', 'totalRooms', 'complianceRate'));
})->name('landing');

Route::post('/contact', function (\Illuminate\Http\Request $request) {
    $request->validate([
        'name' => 'required|string|max:255',
        'email' => 'required|email|max:255',
        'university_id' => 'nullable|string|max:100',
        'topic' => 'nullable|string|max:100',
        'message' => 'required|string|max:2000',
    ]);

    return back()->with('success', 'Thank you! Your accommodation inquiry has been received by the Residence Life team. We will get back to you shortly.');
})->name('contact.submit');

/*
|--------------------------------------------------------------------------
| Authenticated Central Hub
|--------------------------------------------------------------------------
*/

Route::middleware(['auth'])->group(function () {

    Route::get('/dashboard', function () {
        $user = Auth::user();

        return match ($user->role) {
            'admin' => redirect()->route('admin.dashboard'),
            'proctor_manager' => redirect()->route('manager.dashboard'),
            'proctor' => redirect()->route('proctor.dashboard'),
            'student' => redirect()->route('student.dashboard'),
            default => redirect()->route('login'),
        };
    })->name('dashboard');

    // ---------------------------------------------------------------------
    // 1. Administrator Area
    // ---------------------------------------------------------------------
    Route::middleware(['role:admin'])->prefix('admin')->name('admin.')->group(function () {
        Route::get('/dashboard', [DashboardController::class, 'admin'])->name('dashboard');

        // User Management & Pre-Registration
        Route::get('/users', [AdminController::class, 'users'])->name('users.index');
        Route::post('/users/pre-register', [AdminController::class, 'storePreRegistered'])->name('users.preregister');
        Route::post('/users/import', [AdminController::class, 'importStudents'])->name('users.import');
        Route::get('/users/sample-csv', [AdminController::class, 'downloadSampleCsv'])->name('users.sample_csv');
        Route::put('/users/{user}', [AdminController::class, 'updateUser'])->name('users.update');
        Route::patch('/users/{user}/status', [AdminController::class, 'toggleUserStatus'])->name('users.status');
        Route::post('/users/bulk-destroy', [AdminController::class, 'bulkDestroyUsers'])->name('users.bulk_destroy');
        Route::delete('/users/{user}', [AdminController::class, 'destroyUser'])->name('users.destroy');
        Route::get('/pre-registered', [AdminController::class, 'preRegistered'])->name('preregister.index');

        // Students Information
        Route::get('/students', [AdminController::class, 'students'])->name('students.index');

        // Blocks & Rooms
        Route::get('/blocks', [AdminController::class, 'blocks'])->name('blocks.index');
        Route::post('/blocks', [AdminController::class, 'storeBlock'])->name('blocks.store');
        Route::put('/blocks/{block}', [AdminController::class, 'updateBlock'])->name('blocks.update');
        Route::delete('/blocks/{block}', [AdminController::class, 'destroyBlock'])->name('blocks.destroy');
        Route::get('/rooms', [AdminController::class, 'rooms'])->name('rooms.index');
        Route::post('/rooms', [AdminController::class, 'storeRoom'])->name('rooms.store');

        // Allocations & History
        Route::get('/allocations', [AdminController::class, 'allocations'])->name('allocations.index');

        // Comments & Feedback
        Route::get('/comments', [AdminController::class, 'comments'])->name('comments.index');
        Route::post('/comments/{comment}/reply', [AdminController::class, 'replyComment'])->name('comments.reply');
        Route::delete('/comments/{comment}', [AdminController::class, 'destroyComment'])->name('comments.destroy');

        // Reports
        Route::get('/reports', [AdminController::class, 'reports'])->name('reports.index');

        // Attendance & Absentee Oversight
        Route::get('/attendance', [AttendanceController::class, 'managerIndex'])->name('attendance.index');
        Route::post('/attendance/notify-absent', [AttendanceController::class, 'managerNotifyAbsent'])->name('attendance.notify_absent');
        Route::get('/attendance/reports', [AttendanceController::class, 'managerReports'])->name('attendance.reports');
        Route::post('/attendance/reports/{report}/review', [AttendanceController::class, 'managerReviewReport'])->name('attendance.reports.review');
        Route::get('/leaves', [LeaveRequestController::class, 'managerIndex'])->name('leaves.index');
        Route::post('/leaves/{leave}/approve', [LeaveRequestController::class, 'approve'])->name('leaves.approve');
        Route::post('/leaves/{leave}/reject', [LeaveRequestController::class, 'reject'])->name('leaves.reject');

        // System Maintenance: Database Backups & Restore
        Route::get('/backups', [BackupController::class, 'index'])->name('backups.index');
        Route::post('/backups', [BackupController::class, 'store'])->name('backups.store');
        Route::get('/backups/{backup}/download', [BackupController::class, 'download'])->name('backups.download');
        Route::post('/backups/{backup}/restore', [BackupController::class, 'restore'])->name('backups.restore');
        Route::post('/backups/upload-restore', [BackupController::class, 'uploadRestore'])->name('backups.upload_restore');
    });

    // ---------------------------------------------------------------------
    // 2. Proctor Manager Area
    // ---------------------------------------------------------------------
    Route::middleware(['role:proctor_manager'])->prefix('proctor-manager')->name('manager.')->group(function () {
        Route::get('/dashboard', [DashboardController::class, 'manager'])->name('dashboard');

        Route::get('/blocks', [ProctorManagerController::class, 'blocks'])->name('blocks.index');
        Route::post('/blocks', [ProctorManagerController::class, 'storeBlock'])->name('blocks.store');
        Route::put('/blocks/{block}', [ProctorManagerController::class, 'updateBlock'])->name('blocks.update');
        Route::delete('/blocks/{block}', [ProctorManagerController::class, 'destroyBlock'])->name('blocks.destroy');
        Route::patch('/blocks/{block}/category', [ProctorManagerController::class, 'updateCategory'])->name('blocks.category');

        Route::get('/rooms', [ProctorManagerController::class, 'rooms'])->name('rooms.index');

        Route::get('/assignments', [ProctorManagerController::class, 'assignments'])->name('assignments.index');
        Route::post('/assignments', [ProctorManagerController::class, 'storeAssignment'])->name('assignments.store');
        Route::put('/assignments/{assignment}', [ProctorManagerController::class, 'updateAssignment'])->name('assignments.update');
        Route::delete('/assignments/{assignment}', [ProctorManagerController::class, 'destroyAssignment'])->name('assignments.destroy');
        Route::post('/assignments/{assignment}/end', [ProctorManagerController::class, 'endAssignment'])->name('assignments.end');

        Route::get('/students', [ProctorManagerController::class, 'students'])->name('students.index');
        Route::get('/comments', [ProctorManagerController::class, 'comments'])->name('comments.index');
        Route::post('/comments', [ProctorManagerController::class, 'storeComment'])->name('comments.store');
        Route::post('/comments/{comment}/reply', [ProctorManagerController::class, 'replyComment'])->name('comments.reply');
        Route::delete('/comments/{comment}', [ProctorManagerController::class, 'destroyComment'])->name('comments.destroy');
        Route::get('/reports', [ProctorManagerController::class, 'reports'])->name('reports.index');

        // Attendance & Absentee Monitoring
        Route::get('/attendance', [AttendanceController::class, 'managerIndex'])->name('attendance.index');
        Route::post('/attendance/notify-absent', [AttendanceController::class, 'managerNotifyAbsent'])->name('attendance.notify_absent');
        Route::get('/attendance/reports', [AttendanceController::class, 'managerReports'])->name('attendance.reports');
        Route::post('/attendance/reports/{report}/review', [AttendanceController::class, 'managerReviewReport'])->name('attendance.reports.review');
        Route::get('/leaves', [LeaveRequestController::class, 'managerIndex'])->name('leaves.index');
        Route::post('/leaves/{leave}/approve', [LeaveRequestController::class, 'approve'])->name('leaves.approve');
        Route::post('/leaves/{leave}/reject', [LeaveRequestController::class, 'reject'])->name('leaves.reject');

        // Asset / Inventory Transfer (Manager to Proctor)
        Route::get('/assets', [AssetController::class, 'managerIndex'])->name('assets.index');
        Route::post('/assets/transfer', [AssetController::class, 'managerTransfer'])->name('assets.transfer');
        Route::post('/assets/stock-update', [AssetController::class, 'managerStockUpdate'])->name('assets.stock_update');

        // Internal Announcements (Manager to Proctors)
        Route::get('/notices', [NoticeController::class, 'managerIndex'])->name('notices.index');
        Route::post('/notices', [NoticeController::class, 'managerStore'])->name('notices.store');
        Route::delete('/notices/{notice}', [NoticeController::class, 'managerDestroy'])->name('notices.destroy');
    });

    // ---------------------------------------------------------------------
    // 3. Proctor Area
    // ---------------------------------------------------------------------
    Route::middleware(['role:proctor'])->prefix('proctor')->name('proctor.')->group(function () {
        Route::get('/dashboard', [DashboardController::class, 'proctor'])->name('dashboard');

        Route::get('/my-blocks', [ProctorController::class, 'myBlocks'])->name('blocks.index');
        Route::get('/my-blocks-list', [ProctorController::class, 'myBlocks'])->name('blocks');
        Route::get('/rooms', [ProctorController::class, 'rooms'])->name('rooms.index');
        Route::get('/rooms-list', [ProctorController::class, 'rooms'])->name('rooms');
        Route::post('/rooms', [ProctorController::class, 'storeRoom'])->name('rooms.store');

        Route::get('/allocate', [ProctorController::class, 'allocateForm'])->name('allocate.index');
        Route::get('/allocate-direct', [ProctorController::class, 'allocateForm'])->name('allocate');
        Route::post('/allocate', [ProctorController::class, 'storeAllocation'])->name('allocate.store');

        Route::get('/allocated-students', [ProctorController::class, 'allocatedStudents'])->name('allocated.index');
        Route::get('/students', [ProctorController::class, 'students'])->name('students.index');
        Route::post('/allocations/{allocation}/release', [ProctorController::class, 'releaseAllocation'])->name('allocated.release');

        Route::get('/comments', [ProctorController::class, 'comments'])->name('comments.index');
        Route::post('/comments', [ProctorController::class, 'storeComment'])->name('comments.store');
        Route::post('/comments/{comment}/reply', [ProctorController::class, 'replyComment'])->name('comments.reply');
        Route::delete('/comments/{comment}', [ProctorController::class, 'destroyComment'])->name('comments.destroy');
        Route::get('/reports', [ProctorController::class, 'reports'])->name('reports.index');

        // Daily Curfew Attendance Tracking (Room-by-Room Walk)
        Route::get('/attendance', [AttendanceController::class, 'proctorIndex'])->name('attendance.index');
        Route::post('/attendance/save-room', [AttendanceController::class, 'proctorSaveRoom'])->name('attendance.save_room');
        Route::post('/attendance/finalize', [AttendanceController::class, 'proctorFinalize'])->name('attendance.finalize');
        Route::get('/attendance/reports', [AttendanceController::class, 'proctorReports'])->name('attendance.reports');
        Route::post('/attendance/reports/submit', [AttendanceController::class, 'proctorSubmitReport'])->name('attendance.reports.submit');

        // Leave Requests & Gate Passes
        Route::get('/leaves', [LeaveRequestController::class, 'proctorIndex'])->name('leaves.index');
        Route::post('/leaves/{leave}/approve', [LeaveRequestController::class, 'approve'])->name('leaves.approve');
        Route::post('/leaves/{leave}/reject', [LeaveRequestController::class, 'reject'])->name('leaves.reject');

        // Asset Distribution, Returns & Clearance (Stage 1, 2, 3)
        Route::get('/assets', [AssetController::class, 'proctorIndex'])->name('assets.index');
        Route::post('/assets/transfers/{assignment}/confirm', [AssetController::class, 'proctorConfirmTransfer'])->name('assets.confirm_transfer');
        Route::post('/assets/issue', [AssetController::class, 'proctorIssueAsset'])->name('assets.issue');
        Route::post('/assets/issues/{issue}/return', [AssetController::class, 'proctorReturnAsset'])->name('assets.return');
        Route::get('/assets/clearance', [AssetController::class, 'proctorClearanceList'])->name('assets.clearance');

        // Internal & Student Notices
        Route::get('/notices', [NoticeController::class, 'proctorIndex'])->name('notices.index');
        Route::post('/notices', [NoticeController::class, 'proctorStoreStudentNotice'])->name('notices.store_student');
        Route::delete('/notices/{notice}', [NoticeController::class, 'proctorDestroy'])->name('notices.destroy');
    });

    // ---------------------------------------------------------------------
    // 4. Student Area
    // ---------------------------------------------------------------------
    Route::middleware(['role:student'])->prefix('student')->name('student.')->group(function () {
        Route::get('/dashboard', [DashboardController::class, 'student'])->name('dashboard');

        Route::get('/my-dorm', [StudentController::class, 'myDorm'])->name('dorm.index');
        Route::get('/room-info', [StudentController::class, 'roomInfo'])->name('room.index');

        Route::get('/comments/create', [StudentController::class, 'createComment'])->name('comments.create');
        Route::post('/comments', [StudentController::class, 'storeComment'])->name('comments.store');
        Route::get('/comments', [StudentController::class, 'comments'])->name('comments.index');

        // My Attendance History
        Route::get('/attendance', [AttendanceController::class, 'studentIndex'])->name('attendance.index');

        // Gate Passes & Overnight Leave Requests
        Route::get('/leaves', [LeaveRequestController::class, 'studentIndex'])->name('leaves.index');
        Route::post('/leaves', [LeaveRequestController::class, 'studentStore'])->name('leaves.store');
        Route::delete('/leaves/{leave}', [LeaveRequestController::class, 'studentCancel'])->name('leaves.cancel');

        // My Dorm Assets & Clearance (Stage 2 & 3)
        Route::get('/assets', [AssetController::class, 'studentIndex'])->name('assets.index');
        Route::post('/assets/issues/{issue}/confirm', [AssetController::class, 'studentConfirmAsset'])->name('assets.confirm');

        // Student Notices Feed
        Route::get('/notices', [NoticeController::class, 'studentIndex'])->name('notices.index');
    });

    // ---------------------------------------------------------------------
    // Shared Alert Notifications & Exports
    // ---------------------------------------------------------------------
    Route::post('/alerts/{alert}/read', [AlertController::class, 'markRead'])->name('alerts.read');
    Route::post('/alerts/read-all', [AlertController::class, 'markAllRead'])->name('alerts.read_all');
    Route::get('/attendance/export-csv', [AttendanceController::class, 'exportCsv'])->name('attendance.export_csv');

    // ---------------------------------------------------------------------
    // Shared Profile & Settings
    // ---------------------------------------------------------------------
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::put('/profile/password', [ProfileController::class, 'updatePassword'])->name('profile.password');
    Route::post('/profile/avatar', [ProfileController::class, 'updateAvatar'])->name('profile.avatar');
    Route::delete('/profile/avatar', [ProfileController::class, 'destroyAvatar'])->name('profile.avatar.destroy');
});

require __DIR__.'/auth.php';
