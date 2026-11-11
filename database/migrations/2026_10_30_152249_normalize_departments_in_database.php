<?php

use App\Models\BlockProctorAssignment;
use App\Models\User;
use App\Services\DepartmentCatalog;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // 1. Normalize departments for all existing users in the database
        $users = User::whereNotNull('department')->where('department', '!=', '')->get();
        foreach ($users as $user) {
            $normalized = DepartmentCatalog::normalize($user->department);
            if ($normalized !== $user->department) {
                $user->update(['department' => $normalized]);
            }
        }

        // 2. Normalize assigned_departments in block_proctor_assignments
        $assignments = BlockProctorAssignment::whereNotNull('assigned_departments')->get();
        foreach ($assignments as $asg) {
            if (is_array($asg->assigned_departments)) {
                $normalizedDepts = array_values(array_unique(array_filter(array_map(
                    fn($d) => DepartmentCatalog::normalize($d),
                    $asg->assigned_departments
                ))));
                $asg->update(['assigned_departments' => $normalizedDepts]);
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // No reverse needed as normalization improves data integrity
    }
};
