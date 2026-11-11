<?php

namespace App\Http\Controllers;

use App\Models\AuditLog;
use App\Models\BlockProctorAssignment;
use App\Models\Comment;
use App\Models\DormitoryBlock;
use App\Models\Room;
use App\Models\StudentAllocation;
use App\Models\User;
use App\Services\RoomAllocationService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class AdminController extends Controller
{
    /**
     * User Management: List, Search, Filter
     */
    public function users(Request $request): View
    {
        $query = User::query();
        $like = DB::connection()->getDriverName() === 'pgsql' ? 'ilike' : 'like';

        if ($request->filled('search')) {
            $s = trim($request->search);
            $query->where(function ($q) use ($s, $like) {
                $q->where('name', $like, "%{$s}%")
                  ->orWhere('university_id', $like, "%{$s}%")
                  ->orWhere('email', $like, "%{$s}%")
                  ->orWhere('department', $like, "%{$s}%");
            });
        }

        if ($request->filled('role')) {
            $query->where('role', $request->role);
        }

        if ($request->filled('gender')) {
            $query->where('gender', $request->gender);
        }

        if ($request->filled('department')) {
            $query->where('department', $request->department);
        }

        if ($request->filled('status')) {
            $query->where('registration_status', $request->status);
        }

        $users = \App\Services\QuerySortService::sortUsers($query, $request)->paginate(10)->withQueryString();
        $departments = \App\Services\DepartmentCatalog::all();

        return view('admin.users', compact('users', 'departments'));
    }

    /**
     * Store Pre-Registered User
     */
    public function storePreRegistered(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'university_id' => ['required', 'string', 'max:50', 'unique:users,university_id'],
            'role' => ['required', Rule::in(['admin', 'proctor_manager', 'proctor', 'student'])],
            'gender' => ['nullable', Rule::in(['male', 'female'])],
            'department' => ['nullable', 'string', 'max:100'],
            'academic_year' => ['nullable', 'integer', 'between:1,7'],
        ]);

        if ($validated['role'] === 'student' && empty($validated['gender'])) {
            return back()->with('error', 'Gender is required when pre-registering student records.')->withInput();
        }

        $user = User::create([
            'name' => $validated['name'],
            'university_id' => trim($validated['university_id']),
            'role' => $validated['role'],
            'gender' => $validated['gender'] ?? null,
            'department' => \App\Services\DepartmentCatalog::normalize($validated['department'] ?? null),
            'academic_year' => $validated['role'] === 'student' ? $validated['academic_year'] : null,
            'registration_status' => User::REGISTRATION_PENDING,
        ]);

        AuditLog::log('user_preregistered', "Admin created pre-registered record for {$user->name} ({$user->university_id}) as {$user->role} [Dept: " . ($user->department ?? 'N/A') . "].", null, auth()->user());

        return back()->with('success', "Pre-registration record for {$user->name} created successfully. The user can now complete registration with ID: {$user->university_id}.");
    }

    /**
     * Update User Record (e.g. edit profile details, role, status, credentials)
     */
    public function updateUser(Request $request, User $user): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'university_id' => ['required', 'string', 'max:50', Rule::unique('users', 'university_id')->ignore($user->id)],
            'email' => ['nullable', 'email', 'max:255', Rule::unique('users', 'email')->ignore($user->id)],
            'gender' => ['nullable', Rule::in(['male', 'female'])],
            'department' => ['nullable', 'string', 'max:100'],
            'academic_year' => ['nullable', 'integer', 'between:1,7'],
            'role' => ['required', Rule::in(['admin', 'proctor_manager', 'proctor', 'student'])],
            'registration_status' => ['required', Rule::in(['pending', 'completed'])],
            'is_active' => ['nullable', 'boolean'],
            'password' => ['nullable', 'string', 'min:6'],
        ]);

        $updateData = [
            'name' => $validated['name'],
            'university_id' => trim($validated['university_id']),
            'email' => !empty($validated['email']) ? strtolower(trim($validated['email'])) : null,
            'gender' => $validated['gender'] ?? null,
            'department' => \App\Services\DepartmentCatalog::normalize($validated['department'] ?? null),
            'academic_year' => $validated['role'] === 'student' ? $validated['academic_year'] : null,
            'role' => $validated['role'],
            'registration_status' => $validated['registration_status'],
            'is_active' => $request->boolean('is_active', true),
        ];

        if (!empty($validated['password'])) {
            $updateData['password'] = Hash::make($validated['password']);
        }

        $user->update($updateData);

        AuditLog::log('user_updated', "Admin updated profile record for {$user->name} ({$user->university_id}).", null, auth()->user());

        return back()->with('success', "User record for {$user->name} updated successfully.");
    }

    /**
     * Toggle Active / Deactivate User Status
     */
    public function toggleUserStatus(User $user): RedirectResponse
    {
        if ($user->id === auth()->id()) {
            return back()->with('error', 'You cannot deactivate your own administrator account.');
        }

        $newStatus = !($user->is_active ?? true);
        $user->update(['is_active' => $newStatus]);

        $action = $newStatus ? 'activated' : 'deactivated';
        AuditLog::log('user_status_changed', "Admin {$action} user account for {$user->name} ({$user->university_id}).", null, auth()->user());

        return back()->with('success', "User account for {$user->name} has been {$action}.");
    }

    /**
     * Delete user
     */
    public function destroyUser(User $user): RedirectResponse
    {
        if ($user->id === auth()->id()) {
            return back()->with('error', 'You cannot delete your own administrator account.');
        }

        $name = $user->name;
        $user->delete();

        AuditLog::log('user_deleted', "Admin deleted user {$name}.", null, auth()->user());

        return back()->with('success', "User {$name} removed successfully.");
    }

    /**
     * Bulk Delete Users by ID range (e.g. 2000-3000) or selected IDs
     */
    public function bulkDestroyUsers(Request $request): RedirectResponse
    {
        $adminId = auth()->id();
        $targetIds = collect();

        $driver = DB::connection()->getDriverName();

        // 1. Process Range or ID list from text input
        if ($request->filled('id_range')) {
            $raw = trim($request->id_range);
            $tokens = preg_split('/[,\r\n]+/', $raw);

            foreach ($tokens as $token) {
                $token = trim($token);
                if (empty($token)) {
                    continue;
                }

                // Match range pattern like 2000-3000
                if (preg_match('/^(\d+)\s*[-–—]\s*(\d+)$/', $token, $matches)) {
                    $start = (int) $matches[1];
                    $end = (int) $matches[2];
                    if ($start > $end) {
                        [$start, $end] = [$end, $start];
                    }

                    if ($driver === 'pgsql') {
                        $rangeUserIds = User::where('id', '!=', $adminId)
                            ->where(function ($q) use ($start, $end) {
                                $q->whereBetween('id', [$start, $end])
                                  ->orWhereRaw("university_id ~ '^[0-9]+$' AND CAST(university_id AS BIGINT) BETWEEN ? AND ?", [$start, $end]);
                            })
                            ->pluck('id');
                    } else {
                        // SQLite / MySQL safe fallback
                        $rangeUserIds = User::where('id', '!=', $adminId)->get()
                            ->filter(function ($u) use ($start, $end) {
                                if ($u->id >= $start && $u->id <= $end) return true;
                                if (is_numeric($u->university_id)) {
                                    $val = (int)$u->university_id;
                                    return $val >= $start && $val <= $end;
                                }
                                return false;
                            })
                            ->pluck('id');
                    }

                    $targetIds = $targetIds->merge($rangeUserIds);
                } else {
                    $singleUserIds = User::where('id', '!=', $adminId)
                        ->where(function ($q) use ($token) {
                            $q->where('university_id', $token)
                              ->orWhere('id', is_numeric($token) ? (int)$token : -1);
                        })
                        ->pluck('id');

                    $targetIds = $targetIds->merge($singleUserIds);
                }
            }
        }

        // 2. Process Checkboxes from User table
        if ($request->has('user_ids') && is_array($request->user_ids)) {
            $checkboxIds = collect($request->user_ids)
                ->filter(fn($id) => (int)$id !== $adminId)
                ->map(fn($id) => (int)$id);
            $targetIds = $targetIds->merge($checkboxIds);
        }

        $uniqueIds = $targetIds->unique()->values();

        if ($uniqueIds->isEmpty()) {
            return back()->with('error', 'No eligible users found matching the given IDs or range. (Administrator accounts are protected).');
        }

        $count = $uniqueIds->count();
        User::whereIn('id', $uniqueIds)->delete();

        AuditLog::log('bulk_users_deleted', "Admin bulk deleted {$count} users (Specification: " . ($request->id_range ?? 'Table Selection') . ").", null, auth()->user());

        return back()->with('success', "Successfully deleted {$count} user(s).");
    }

    /**
     * Pre-Registered Users List
     */
    public function preRegistered(Request $request): View
    {
        $like = DB::connection()->getDriverName() === 'pgsql' ? 'ilike' : 'like';
        $query = User::where('registration_status', User::REGISTRATION_PENDING);

        if ($request->filled('search')) {
            $s = trim($request->search);
            $query->where(function ($q) use ($s, $like) {
                $q->where('name', $like, "%{$s}%")
                  ->orWhere('university_id', $like, "%{$s}%")
                  ->orWhere('department', $like, "%{$s}%");
            });
        }

        if ($request->filled('department')) {
            $query->where('department', $request->department);
        }

        if ($request->filled('gender')) {
            $query->where('gender', $request->gender);
        }

        if ($request->filled('role')) {
            $query->where('role', $request->role);
        }

        if ($request->filled('year')) {
            $query->where('academic_year', $request->year);
        }

        $pendingUsers = \App\Services\QuerySortService::sortUsers($query, $request)->paginate(10)->withQueryString();
        $departments = \App\Services\DepartmentCatalog::all();

        return view('admin.preregistered', compact('pendingUsers', 'departments'));
    }

    /**
     * Students Directory
     */
    public function students(Request $request): View
    {
        $like = DB::connection()->getDriverName() === 'pgsql' ? 'ilike' : 'like';
        $query = User::where('role', User::ROLE_STUDENT)
            ->with(['activeAllocation.room.block']);

        if ($request->filled('search')) {
            $s = trim($request->search);
            $query->where(function ($q) use ($s, $like) {
                $q->where('name', $like, "%{$s}%")
                  ->orWhere('university_id', $like, "%{$s}%")
                  ->orWhere('email', $like, "%{$s}%")
                  ->orWhere('department', $like, "%{$s}%");
            });
        }

        if ($request->filled('department')) {
            $query->whereRaw('LOWER(department) = ?', [strtolower(trim($request->department))]);
        }

        if ($request->filled('gender')) {
            $query->where('gender', $request->gender);
        }

        if ($request->filled('year')) {
            $query->where('academic_year', $request->year);
        }

        if ($request->filled('status')) {
            if ($request->status === 'allocated') {
                $query->whereHas('activeAllocation');
            } elseif ($request->status === 'unallocated') {
                $query->whereDoesntHave('activeAllocation');
            }
        }

        $students = \App\Services\QuerySortService::sortUsers($query, $request)->paginate(10)->withQueryString();
        $departments = \App\Services\DepartmentCatalog::all();

        return view('admin.students', compact('students', 'departments'));
    }

    /**
     * Dormitory Blocks Management
     */
    public function blocks(Request $request): View
    {
        $query = DormitoryBlock::withCount(['rooms', 'activeAllocations'])
            ->with(['activeProctorAssignments.proctor']);
        $like = DB::connection()->getDriverName() === 'pgsql' ? 'ilike' : 'like';

        if ($request->filled('search')) {
            $s = trim($request->search);
            $query->where(function ($q) use ($s, $like) {
                $q->where('block_code', $like, "%{$s}%")
                  ->orWhere('name', $like, "%{$s}%")
                  ->orWhere('location', $like, "%{$s}%");
            });
        }

        if ($request->filled('gender')) {
            $query->where('gender_category', $request->gender);
        }

        $blocks = \App\Services\QuerySortService::sortBlocks($query, $request)->paginate(9)->withQueryString();

        return view('admin.blocks', compact('blocks'));
    }

    /**
     * Store new Dormitory Block
     */
    public function storeBlock(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'block_code' => ['required', 'string', 'max:50', 'unique:dormitory_blocks,block_code'],
            'gender_category' => ['required', Rule::in(['male', 'female'])],
            'location' => ['nullable', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
        ]);

        $validated['image'] = DormitoryBlock::determineBlockImage($validated['block_code']);
        $block = DormitoryBlock::create($validated);

        AuditLog::log('block_created', "Created block {$block->name} ({$block->block_code}) with {$block->gender_category} category.", null, auth()->user());

        return back()->with('success', "Dormitory block {$block->name} created successfully.");
    }

    /**
     * Update Dormitory Block
     */
    public function updateBlock(Request $request, DormitoryBlock $block): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'block_code' => ['required', 'string', 'max:50', Rule::unique('dormitory_blocks', 'block_code')->ignore($block->id)],
            'gender_category' => ['required', Rule::in(['male', 'female'])],
            'location' => ['nullable', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
        ]);

        // If gender category is changed, check if students are currently allocated
        if ($block->gender_category !== $validated['gender_category']) {
            $activeCount = $block->activeAllocations()->count();
            if ($activeCount > 0) {
                return back()->with('error', "Cannot change accommodation category because this block currently houses {$activeCount} {$block->gender_category} students. Reallocate or release students before changing gender category.");
            }
        }

        $validated['image'] = DormitoryBlock::determineBlockImage($validated['block_code']);
        $block->update($validated);

        AuditLog::log('block_updated', "Admin updated block {$block->block_code} ({$block->name}).", null, auth()->user());

        return back()->with('success', "Dormitory block {$block->block_code} updated successfully.");
    }

    /**
     * Delete Dormitory Block
     */
    public function destroyBlock(DormitoryBlock $block): RedirectResponse
    {
        $activeCount = $block->activeAllocations()->count();
        if ($activeCount > 0) {
            return back()->with('error', "Cannot delete block {$block->block_code} because it currently houses {$activeCount} resident students. Please reallocate them first.");
        }

        $code = $block->block_code;
        $name = $block->name;
        $block->delete();

        AuditLog::log('block_deleted', "Admin deleted block {$code} ({$name}).", null, auth()->user());

        return back()->with('success', "Dormitory block {$code} ({$name}) removed successfully.");
    }

    /**
     * Rooms Management (with search and filters)
     */
    public function rooms(Request $request): View
    {
        $blocks = DormitoryBlock::orderBy('block_code')->get();
        $like = DB::connection()->getDriverName() === 'pgsql' ? 'ilike' : 'like';

        $query = Room::with(['block', 'activeAllocations.student'])->withCount('activeAllocations');

        if ($request->filled('search')) {
            $s = trim($request->search);
            $query->where(function ($q) use ($s, $like) {
                $q->where('room_number', $like, "%{$s}%")
                  ->orWhereHas('block', function ($bq) use ($s, $like) {
                      $bq->where('block_code', $like, "%{$s}%")
                         ->orWhere('name', $like, "%{$s}%");
                  });
            });
        }

        if ($request->filled('block_id')) {
            $query->where('block_id', $request->block_id);
        }

        if ($request->filled('gender')) {
            $query->whereHas('block', fn($bq) => $bq->where('gender_category', $request->gender));
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $rooms = \App\Services\QuerySortService::sortRooms($query, $request)->paginate(16)->withQueryString();

        return view('admin.rooms', compact('blocks', 'rooms'));
    }

    /**
     * Store Room (Maximum 50 rooms per block, maximum 6 students capacity per room)
     */
    public function storeRoom(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'block_id' => ['required', 'exists:dormitory_blocks,id'],
            'room_number' => ['required', 'string', 'max:50'],
            'capacity' => ['required', 'integer', 'min:1', 'max:' . DormitoryBlock::MAX_ROOM_CAPACITY],
        ]);

        $block = DormitoryBlock::findOrFail($validated['block_id']);
        if ($block->rooms()->count() >= DormitoryBlock::ROOMS_PER_BLOCK) {
            return back()->with('error', "Cannot register room. Block {$block->block_code} has reached its maximum design capacity of 50 rooms.")->withInput();
        }

        // Check unique within block
        $exists = Room::where('block_id', $validated['block_id'])->where('room_number', $validated['room_number'])->exists();
        if ($exists) {
            return back()->with('error', "Room number {$validated['room_number']} already exists in this block.")->withInput();
        }

        $validated['image'] = Room::determineRoomImage($validated['room_number']);
        $room = Room::create($validated);

        AuditLog::log('room_created', "Admin registered Room {$room->room_number} in block {$block->block_code} (Capacity: {$room->capacity} beds).", null, auth()->user());

        return back()->with('success', "Room {$room->room_number} registered successfully in Block {$block->block_code}.");
    }

    /**
     * Allocations List & History
     */
    public function allocations(): View
    {
        $allocations = StudentAllocation::with(['student', 'room.block', 'allocator'])->latest()->paginate(12);
        return view('admin.allocations', compact('allocations'));
    }

    /**
     * Comments & Feedback (Administrator views proctor manager and proctor comments, with search and filters)
     */
    public function comments(Request $request): View
    {
        $query = Comment::with(['user', 'responder']);
        $like = DB::connection()->getDriverName() === 'pgsql' ? 'ilike' : 'like';

        // Author Role Scope: Administrator primarily views proctor manager and proctor comments
        if ($request->filled('author_role')) {
            if ($request->author_role !== 'all') {
                $query->whereHas('user', fn($uq) => $uq->where('role', $request->author_role));
            }
        } else {
            // Default: show Proctor Manager and Proctor comments as per institutional hierarchy
            $query->where(function ($q) {
                $q->whereHas('user', fn($uq) => $uq->whereIn('role', [User::ROLE_PROCTOR, User::ROLE_PROCTOR_MANAGER]))
                  ->orWhere('target_role', 'admin');
            });
        }

        if ($request->filled('search')) {
            $s = trim($request->search);
            $query->where(function ($q) use ($s, $like) {
                $q->where('title', $like, "%{$s}%")
                  ->orWhere('message', $like, "%{$s}%")
                  ->orWhere('category', $like, "%{$s}%")
                  ->orWhereHas('user', function ($uq) use ($s, $like) {
                      $uq->where('name', $like, "%{$s}%")
                         ->orWhere('university_id', $like, "%{$s}%");
                  });
            });
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('category')) {
            $query->where('category', $request->category);
        }

        $comments = $query->latest()->paginate(10)->withQueryString();

        return view('admin.comments', compact('comments'));
    }

    /**
     * Reply to comment
     */
    public function replyComment(Request $request, Comment $comment): RedirectResponse
    {
        $request->validate(['response' => 'required|string', 'status' => 'required|in:in_progress,resolved,closed']);

        $comment->update([
            'response' => $request->response,
            'status' => $request->status,
            'responded_by' => auth()->id(),
            'responded_at' => now(),
        ]);

        AuditLog::log('comment_replied', "Admin replied to feedback report #{$comment->id}.", null, auth()->user());

        return back()->with('success', 'Official response submitted successfully.');
    }

    /**
     * Delete comment
     */
    public function destroyComment(Comment $comment): RedirectResponse
    {
        $title = $comment->title;
        $comment->delete();

        AuditLog::log('comment_deleted', "Admin permanently deleted comment report: '{$title}'.", null, auth()->user());

        return back()->with('success', "Comment '{$title}' removed successfully.");
    }

    /**
     * Reports & Statistics View (Using Standard 50 rooms * 6 students = 300 students per block magic calculation)
     */
    public function reports(): View
    {
        $totalBlocks = DormitoryBlock::count();
        $totalCapacity = $totalBlocks * DormitoryBlock::STANDARD_BLOCK_CAPACITY; // 50 * 6 = 300 per block
        $allocatedStudents = StudentAllocation::where('status', StudentAllocation::STATUS_ACTIVE)->count();
        $overallOccupancyRate = $totalCapacity > 0 ? round(($allocatedStudents / $totalCapacity) * 100, 1) : 0;

        $stats = [
            'total_students' => User::where('role', User::ROLE_STUDENT)->count(),
            'male_students' => User::where('role', User::ROLE_STUDENT)->where('gender', 'male')->count(),
            'female_students' => User::where('role', User::ROLE_STUDENT)->where('gender', 'female')->count(),
            'allocated_students' => $allocatedStudents,
            'total_blocks' => $totalBlocks,
            'total_capacity' => $totalCapacity,
            'total_designed_rooms' => $totalBlocks * DormitoryBlock::ROOMS_PER_BLOCK,
            'overall_occupancy_rate' => $overallOccupancyRate,
            'blocks' => DormitoryBlock::withCount(['rooms', 'activeAllocations'])->get(),
        ];

        return view('admin.reports', compact('stats'));
    }

    /**
     * Download Sample Students Import CSV Template
     */
    public function downloadSampleCsv()
    {
        $csv = "name,university_id,gender,department,academic_year,role\n";
        $csv .= "Abebe Kebede,3001,male,Computer Science,1,student\n";
        $csv .= "Sara Daniel,3002,female,Medicine,2,student\n";
        $csv .= "Yared Tesfaye,3003,male,Electrical Engineering,3,student\n";
        $csv .= "Bethlehem Assefa,3004,female,Software Engineering,1,student\n";
        $csv .= "Nahom Kifle,3005,male,Mechanical Engineering,2,student\n";
        $csv .= "Tigist Haile,3006,female,Civil Engineering,4,student\n";

        return response($csv, 200, [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="students_import_template.csv"',
        ]);
    }

    /**
     * Bulk Import Students from CSV Excel Export with Range & Filter Controls
     */
    public function importStudents(Request $request): RedirectResponse
    {
        $request->validate([
            'file' => ['required', 'file', 'max:25600'], // 25MB max
            'single_id' => ['nullable', 'string', 'max:100'],
            'id_range' => ['nullable', 'string', 'max:100'],
            'row_range' => ['nullable', 'string', 'max:100'],
            'department' => ['nullable', 'string', 'max:100'],
            'academic_year' => ['nullable', 'integer', 'between:1,7'],
            'gender' => ['nullable', Rule::in(['male', 'female'])],
            'duplicate_handling' => ['nullable', Rule::in(['skip', 'update'])],
        ]);

        $file = $request->file('file');
        $extension = strtolower($file->getClientOriginalExtension());
        if (!in_array($extension, ['csv', 'txt'])) {
            return back()->with('error', 'Please upload a valid CSV file (.csv or .txt).');
        }

        $handle = fopen($file->getRealPath(), 'r');
        if (!$handle) {
            return back()->with('error', 'Unable to open and read the uploaded file.');
        }

        // Strip UTF-8 BOM if present
        $bom = fread($handle, 3);
        if ($bom !== "\xEF\xBB\xBF") {
            rewind($handle);
        }

        // Read header row
        $firstLine = fgets($handle);
        if (!$firstLine) {
            fclose($handle);
            return back()->with('error', 'The uploaded file is empty.');
        }

        // Detect delimiter (, or ; or \t or |)
        $delimiters = [',', ';', "\t", '|'];
        $bestDelimiter = ',';
        $maxCount = 0;
        foreach ($delimiters as $delim) {
            $count = substr_count($firstLine, $delim);
            if ($count > $maxCount) {
                $maxCount = $count;
                $bestDelimiter = $delim;
            }
        }

        $headers = str_getcsv(trim($firstLine), $bestDelimiter);
        // Normalize headers
        $headerMap = [];
        foreach ($headers as $index => $header) {
            $clean = strtolower(trim(preg_replace('/[^a-zA-Z0-9]/', '', $header)));
            if (in_array($clean, ['name', 'fullname', 'studentname', 'student'])) {
                $headerMap['name'] = $index;
            } elseif (in_array($clean, ['universityid', 'studentid', 'id', 'idnumber', 'code', 'regno', 'registrationnumber'])) {
                $headerMap['university_id'] = $index;
            } elseif (in_array($clean, ['gender', 'sex'])) {
                $headerMap['gender'] = $index;
            } elseif (in_array($clean, ['department', 'dept', 'faculty', 'field', 'stream'])) {
                $headerMap['department'] = $index;
            } elseif (in_array($clean, ['academicyear', 'year', 'classyear', 'batch'])) {
                $headerMap['academic_year'] = $index;
            } elseif (in_array($clean, ['role', 'userrole'])) {
                $headerMap['role'] = $index;
            } elseif (in_array($clean, ['email', 'mail'])) {
                $headerMap['email'] = $index;
            }
        }

        if (!isset($headerMap['name']) && !isset($headerMap['university_id'])) {
            fclose($handle);
            return back()->with('error', 'Invalid CSV format: Missing "Name" or "University ID" column headers.');
        }

        // Target Single ID (if importing a single user by ID)
        $targetSingleId = $request->filled('single_id') ? trim($request->single_id) : null;

        // Parse Row Range (e.g. "1500-2000" or "1-500")
        $minRow = null;
        $maxRow = null;
        if ($request->filled('row_range')) {
            $parts = explode('-', str_replace(' ', '', $request->row_range));
            if (count($parts) === 2 && is_numeric($parts[0]) && is_numeric($parts[1])) {
                $minRow = (int)$parts[0];
                $maxRow = (int)$parts[1];
            } elseif (count($parts) === 1 && is_numeric($parts[0])) {
                $minRow = 1;
                $maxRow = (int)$parts[0];
            }
        }

        // Parse ID Range or single ID in id_range (e.g. "3000-4000" or "WLD/1000-WLD/2000" or "3005")
        $minId = null;
        $maxId = null;
        $minIdNum = null;
        $maxIdNum = null;
        if ($request->filled('id_range')) {
            $rawIdRange = trim($request->id_range);
            if (str_contains($rawIdRange, '-')) {
                $parts = explode('-', str_replace(' ', '', $rawIdRange));
                if (count($parts) === 2) {
                    $minId = trim($parts[0]);
                    $maxId = trim($parts[1]);
                    preg_match('/\d+/', $minId, $minMatches);
                    preg_match('/\d+/', $maxId, $maxMatches);
                    if (!empty($minMatches) && !empty($maxMatches)) {
                        $minIdNum = (int)$minMatches[0];
                        $maxIdNum = (int)$maxMatches[0];
                    }
                }
            } else {
                // If user entered a single ID into id_range without hyphen
                if (empty($targetSingleId)) {
                    $targetSingleId = $rawIdRange;
                }
            }
        }

        $filterDept = $request->filled('department') ? trim(strtolower($request->department)) : null;
        $filterYear = $request->filled('academic_year') ? (int)$request->academic_year : null;
        $duplicateMode = $request->input('duplicate_handling', 'skip');

        $rowIndex = 0;
        $imported = 0;
        $updated = 0;
        $skippedDuplicates = 0;
        $filteredOut = 0;
        $invalidRows = 0;
        $lastMatchedUser = null;

        while (($data = fgetcsv($handle, 4096, $bestDelimiter)) !== false) {
            $rowIndex++;

            // Filter by row range if specified
            if ($minRow !== null && ($rowIndex < $minRow || $rowIndex > $maxRow)) {
                $filteredOut++;
                continue;
            }

            // Get row values
            $name = isset($headerMap['name'], $data[$headerMap['name']]) ? trim($data[$headerMap['name']]) : null;
            $univId = isset($headerMap['university_id'], $data[$headerMap['university_id']]) ? trim($data[$headerMap['university_id']]) : null;

            if (empty($name) || empty($univId)) {
                $invalidRows++;
                continue;
            }

            // Filter by Single Student ID if specified
            if ($targetSingleId !== null) {
                $isMatch = (strcasecmp($univId, $targetSingleId) === 0);
                if (!$isMatch && is_numeric($univId) && is_numeric($targetSingleId)) {
                    $isMatch = ((int)$univId === (int)$targetSingleId);
                }
                if (!$isMatch) {
                    $filteredOut++;
                    continue;
                }
            }

            // Filter by ID range if specified
            if ($minId !== null) {
                if ($minIdNum !== null && $maxIdNum !== null) {
                    preg_match('/\d+/', $univId, $idMatches);
                    $rowIdNum = !empty($idMatches) ? (int)$idMatches[0] : null;
                    if ($rowIdNum === null || $rowIdNum < $minIdNum || $rowIdNum > $maxIdNum) {
                        $filteredOut++;
                        continue;
                    }
                } else {
                    if (strcasecmp($univId, $minId) < 0 || strcasecmp($univId, $maxId) > 0) {
                        $filteredOut++;
                        continue;
                    }
                }
            }

            // Department
            $rawDept = isset($headerMap['department'], $data[$headerMap['department']]) ? trim($data[$headerMap['department']]) : null;
            $rowDept = !empty($rawDept) ? \App\Services\DepartmentCatalog::normalize($rawDept) : ($request->department ? \App\Services\DepartmentCatalog::normalize($request->department) : null);

            // Filter by Department if specified
            if ($filterDept !== null) {
                if (empty($rowDept) || strtolower($rowDept) !== $filterDept) {
                    $filteredOut++;
                    continue;
                }
            }

            // Academic Year
            $rawYear = isset($headerMap['academic_year'], $data[$headerMap['academic_year']]) ? (int)preg_replace('/[^0-9]/', '', $data[$headerMap['academic_year']]) : null;
            $rowYear = ($rawYear && $rawYear >= 1 && $rawYear <= 7) ? $rawYear : ($filterYear ?? 1);

            // Filter by Academic Year if specified
            if ($filterYear !== null) {
                if ($rowYear !== $filterYear) {
                    $filteredOut++;
                    continue;
                }
            }

            // Gender
            $rawGender = isset($headerMap['gender'], $data[$headerMap['gender']]) ? strtolower(trim($data[$headerMap['gender']])) : null;
            $gender = null;
            if ($rawGender) {
                if (str_starts_with($rawGender, 'm')) $gender = 'male';
                elseif (str_starts_with($rawGender, 'f')) $gender = 'female';
            }
            if (!$gender) {
                $gender = $request->input('gender', 'male');
            }

            // Role
            $rawRole = isset($headerMap['role'], $data[$headerMap['role']]) ? strtolower(trim($data[$headerMap['role']])) : 'student';
            $role = in_array($rawRole, [User::ROLE_STUDENT, User::ROLE_PROCTOR, User::ROLE_PROCTOR_MANAGER]) ? $rawRole : User::ROLE_STUDENT;

            // Check if user already exists
            $existing = User::where('university_id', $univId)->first();
            if ($existing) {
                if ($duplicateMode === 'update') {
                    $existing->update([
                        'name' => $name,
                        'gender' => $gender,
                        'department' => $rowDept,
                        'academic_year' => $role === User::ROLE_STUDENT ? $rowYear : null,
                    ]);
                    $updated++;
                    $lastMatchedUser = $existing;
                } else {
                    $skippedDuplicates++;
                    $lastMatchedUser = $existing;
                }
                if ($targetSingleId !== null) {
                    break; // Found the single user, can stop reading file
                }
                continue;
            }

            $newUser = User::create([
                'name' => $name,
                'university_id' => $univId,
                'role' => $role,
                'gender' => $gender,
                'department' => $rowDept,
                'academic_year' => $role === User::ROLE_STUDENT ? $rowYear : null,
                'registration_status' => User::REGISTRATION_PENDING,
                'is_active' => true,
            ]);

            $imported++;
            $lastMatchedUser = $newUser;

            if ($targetSingleId !== null) {
                break; // Found the single user, can stop reading file
            }
        }

        fclose($handle);

        // If user specifically requested a single user by ID
        if ($targetSingleId !== null) {
            if ($imported > 0) {
                AuditLog::log('single_student_imported', "Admin imported single user {$lastMatchedUser->name} (ID: {$lastMatchedUser->university_id}) from roster.", null, auth()->user());
                return back()->with('success', "Single user imported successfully: {$lastMatchedUser->name} (ID: {$lastMatchedUser->university_id}, Dept: {$lastMatchedUser->department}, Year: {$lastMatchedUser->academic_year}).");
            } elseif ($updated > 0) {
                AuditLog::log('single_student_updated', "Admin updated existing user {$lastMatchedUser->name} (ID: {$lastMatchedUser->university_id}) from roster.", null, auth()->user());
                return back()->with('success', "Existing user updated from roster: {$lastMatchedUser->name} (ID: {$lastMatchedUser->university_id}).");
            } elseif ($skippedDuplicates > 0) {
                return back()->with('error', "User with ID '{$targetSingleId}' ({$lastMatchedUser->name}) is already registered in the system. To update their details, choose 'Update Profile Info' in duplicate handling.");
            } else {
                return back()->with('error', "No user record with University ID '{$targetSingleId}' was found in the uploaded file.");
            }
        }

        $msg = "CSV Import completed: {$imported} student(s) imported successfully.";
        if ($updated > 0) $msg .= " {$updated} record(s) updated.";
        if ($skippedDuplicates > 0) $msg .= " {$skippedDuplicates} existing student(s) skipped.";
        if ($filteredOut > 0) $msg .= " {$filteredOut} row(s) excluded by range/filter rules.";
        if ($invalidRows > 0) $msg .= " {$invalidRows} empty/malformed row(s) skipped.";

        AuditLog::log('students_imported', "Admin imported {$imported} student records via CSV from Registrar export.", null, auth()->user());

        return back()->with('success', $msg);
    }
}
