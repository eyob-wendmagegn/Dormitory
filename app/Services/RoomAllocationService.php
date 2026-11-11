<?php

namespace App\Services;

use App\Models\AuditLog;
use App\Models\DormitoryBlock;
use App\Models\Room;
use App\Models\StudentAllocation;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class RoomAllocationService
{
    /**
     * Allocate a student to a room with strict gender segregation, concurrency, and capacity protections.
     */
    public function allocate(User $student, Room $room, User $proctor, ?string $remarks = null): StudentAllocation
    {
        if ($student->role !== User::ROLE_STUDENT) {
            throw ValidationException::withMessages([
                'student_id' => 'Only student accounts can be allocated to a dormitory room.',
            ]);
        }

        // Student gender is mandatory for allocation
        if (empty($student->gender)) {
            throw ValidationException::withMessages([
                'student_id' => 'Allocation denied. Student record does not contain an assigned gender. Please update student profile first.',
            ]);
        }

        // Proctor authorization: proctors can only allocate within their assigned blocks and scopes
        if ($proctor->isProctor()) {
            $assignment = $proctor->activeProctorAssignments()->where('block_id', $room->block_id)->first();
            if (! $assignment) {
                throw ValidationException::withMessages([
                    'room_id' => 'You are not authorized to allocate rooms in this dormitory block.',
                ]);
            }

            // Enforce proctor's assigned departments scope (if configured, case-insensitive)
            $assignedDepts = $assignment->assigned_departments_list;
            if (!empty($assignedDepts)) {
                $studentDeptNorm = \App\Services\DepartmentCatalog::normalize($student->department);
                $isDeptMatch = collect($assignedDepts)->contains(function ($d) use ($student, $studentDeptNorm) {
                    return strcasecmp($d, $student->department) === 0 ||
                           strcasecmp(\App\Services\DepartmentCatalog::normalize($d), $studentDeptNorm) === 0;
                });

                if (!$isDeptMatch) {
                    throw ValidationException::withMessages([
                        'student_id' => "Allocation denied. Student {$student->name}'s department (" . ($student->department ?? 'Unassigned') . ") is outside your assigned departments for Block {$room->block->block_code}.",
                    ]);
                }
            }

            // Enforce proctor's assigned batches/years scope (if configured)
            $assignedYears = $assignment->assigned_years_list;
            if (!empty($assignedYears)) {
                if (empty($student->academic_year) || !in_array((int)$student->academic_year, $assignedYears)) {
                    throw ValidationException::withMessages([
                        'student_id' => "Allocation denied. Student {$student->name}'s academic year (" . ($student->academic_year ? "Year {$student->academic_year}" : 'Unassigned') . ") is outside your assigned batches for Block {$room->block->block_code}.",
                    ]);
                }
            }
        }

        return DB::transaction(function () use ($student, $room, $proctor, $remarks) {
            // Lock student row & check active allocations
            $activeAllocation = StudentAllocation::where('student_id', $student->id)
                ->where('status', StudentAllocation::STATUS_ACTIVE)
                ->lockForUpdate()
                ->first();

            if ($activeAllocation) {
                throw ValidationException::withMessages([
                    'student_id' => 'This student already has an active room allocation. Release or transfer their current allocation first.',
                ]);
            }

            // Lock room row and check status and capacity
            $lockedRoom = Room::with('block')->where('id', $room->id)->lockForUpdate()->first();

            if (! $lockedRoom) {
                throw ValidationException::withMessages(['room_id' => 'Room not found.']);
            }

            // ENFORCE GENDER MATCH WITH BLOCK CATEGORY
            if ($student->gender !== $lockedRoom->block->gender_category) {
                throw ValidationException::withMessages([
                    'room_id' => "Allocation denied. This student's recorded gender does not match the accommodation category of the selected block.",
                ]);
            }

            if ($lockedRoom->status !== 'available') {
                throw ValidationException::withMessages([
                    'room_id' => "This room is currently marked as '{$lockedRoom->status}' and cannot accept allocations.",
                ]);
            }

            $currentActiveCount = StudentAllocation::where('room_id', $lockedRoom->id)
                ->where('status', StudentAllocation::STATUS_ACTIVE)
                ->count();

            $maxAllowed = min(DormitoryBlock::MAX_ROOM_CAPACITY, $lockedRoom->capacity);
            if ($currentActiveCount >= $maxAllowed) {
                throw ValidationException::withMessages([
                    'room_id' => 'Room is already at full capacity.',
                ]);
            }

            // Create new allocation record
            $allocation = StudentAllocation::create([
                'student_id' => $student->id,
                'room_id' => $lockedRoom->id,
                'allocated_by' => $proctor->id,
                'allocated_at' => now(),
                'status' => StudentAllocation::STATUS_ACTIVE,
                'remarks' => $remarks,
            ]);

            // If room reached full capacity with this allocation, update room status
            if ($currentActiveCount + 1 >= $maxAllowed) {
                $lockedRoom->update(['status' => 'full']);
            }

            AuditLog::log(
                'student_allocated',
                "Student {$student->name} ({$student->gender}) allocated to Block {$lockedRoom->block->block_code} ({$lockedRoom->block->gender_category}) Room {$lockedRoom->room_number} by {$proctor->name}.",
                ['student_id' => $student->id, 'room_id' => $lockedRoom->id, 'gender' => $student->gender],
                $proctor
            );

            return $allocation;
        });
    }

    /**
     * Release an active allocation safely, maintaining complete history.
     */
    public function release(StudentAllocation $allocation, User $proctor, ?string $remarks = null): void
    {
        DB::transaction(function () use ($allocation, $proctor, $remarks) {
            $lockedAllocation = StudentAllocation::where('id', $allocation->id)
                ->lockForUpdate()
                ->firstOrFail();

            if ($lockedAllocation->status !== StudentAllocation::STATUS_ACTIVE) {
                throw ValidationException::withMessages([
                    'allocation' => 'Only active room allocations can be released.',
                ]);
            }

            $lockedAllocation->update([
                'status' => StudentAllocation::STATUS_RELEASED,
                'released_at' => now(),
                'remarks' => $remarks ? ($lockedAllocation->remarks . " | Release note: " . $remarks) : $lockedAllocation->remarks,
            ]);

            // Restore room availability if it was marked full
            $room = Room::where('id', $lockedAllocation->room_id)->lockForUpdate()->first();
            if ($room && $room->status === 'full') {
                $activeCount = StudentAllocation::where('room_id', $room->id)
                    ->where('status', StudentAllocation::STATUS_ACTIVE)
                    ->count();
                if ($activeCount < $room->capacity) {
                    $room->update(['status' => 'available']);
                }
            }

            AuditLog::log(
                'student_released',
                "Allocation #{$lockedAllocation->id} released by {$proctor->name}.",
                ['allocation_id' => $lockedAllocation->id, 'student_id' => $lockedAllocation->student_id],
                $proctor
            );
        });
    }

    /**
     * Transfer a student from their existing room to a new room.
     */
    public function transfer(User $student, Room $newRoom, User $proctor, ?string $reason = null): StudentAllocation
    {
        return DB::transaction(function () use ($student, $newRoom, $proctor, $reason) {
            $currentAllocation = StudentAllocation::where('student_id', $student->id)
                ->where('status', StudentAllocation::STATUS_ACTIVE)
                ->lockForUpdate()
                ->first();

            if ($currentAllocation) {
                $currentAllocation->update([
                    'status' => StudentAllocation::STATUS_TRANSFERRED,
                    'released_at' => now(),
                    'remarks' => $reason ? ($currentAllocation->remarks . " | Transferred: " . $reason) : $currentAllocation->remarks,
                ]);

                $oldRoom = Room::where('id', $currentAllocation->room_id)->lockForUpdate()->first();
                if ($oldRoom && $oldRoom->status === 'full') {
                    $activeCount = StudentAllocation::where('room_id', $oldRoom->id)
                        ->where('status', StudentAllocation::STATUS_ACTIVE)
                        ->count();
                    if ($activeCount < $oldRoom->capacity) {
                        $oldRoom->update(['status' => 'available']);
                    }
                }
            }

            return $this->allocate($student, $newRoom, $proctor, $reason);
        });
    }

    /**
     * Prevent changing block accommodation category if block currently houses students.
     */
    public function updateBlockCategory(DormitoryBlock $block, string $newCategory, User $manager): void
    {
        if ($block->gender_category === $newCategory) {
            return;
        }

        $activeStudentCount = $block->activeAllocations()->count();
        if ($activeStudentCount > 0) {
            throw ValidationException::withMessages([
                'gender_category' => "Cannot change block accommodation category to '{$newCategory}'. The block currently contains {$activeStudentCount} active student allocations. You must release or transfer all residents first.",
            ]);
        }

        $block->update(['gender_category' => $newCategory]);

        AuditLog::log(
            'block_category_changed',
            "Block {$block->block_code} accommodation category updated to {$newCategory} by {$manager->name}.",
            ['block_id' => $block->id, 'new_category' => $newCategory],
            $manager
        );
    }
}
