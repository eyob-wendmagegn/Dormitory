<?php

namespace Database\Seeders;

use App\Models\Announcement;
use App\Models\AssetInventory;
use App\Models\DormitoryBlock;
use App\Models\ProctorAssetAssignment;
use App\Models\StudentAllocation;
use App\Models\StudentAssetIssue;
use App\Models\User;
use Illuminate\Database\Seeder;

class AssetAndNoticeSeeder extends Seeder
{
    public function run(): void
    {
        // 1. Warehouse Assets Inventory
        $mattress = AssetInventory::updateOrCreate(
            ['item_code' => 'MATTRESS'],
            [
                'item_name' => 'Mattress',
                'total_quantity' => 5000,
                'available_quantity' => 4700,
                'allocated_quantity' => 300,
                'damaged_quantity' => 0,
                'unit_penalty_charge' => 1200.00,
                'description' => 'High-density foam student dormitory mattress (190cm x 90cm).',
            ]
        );

        $pillow = AssetInventory::updateOrCreate(
            ['item_code' => 'PILLOW'],
            [
                'item_name' => 'Pillow',
                'total_quantity' => 5000,
                'available_quantity' => 4700,
                'allocated_quantity' => 300,
                'damaged_quantity' => 0,
                'unit_penalty_charge' => 400.00,
                'description' => 'Standard hygienic washable dormitory pillow with fiber fill.',
            ]
        );

        $manager = User::where('role', User::ROLE_PROCTOR_MANAGER)->first();
        $proctor = User::where('role', User::ROLE_PROCTOR)->first();
        $block = DormitoryBlock::first();

        if ($manager && $proctor) {
            // 2. Stage 1: Transfers from Manager to Proctor
            // Confirmed transfer (already in proctor's stock)
            ProctorAssetAssignment::updateOrCreate(
                [
                    'manager_id' => $manager->id,
                    'proctor_id' => $proctor->id,
                    'item_type' => 'mattress',
                    'assigned_quantity' => 100,
                    'status' => ProctorAssetAssignment::STATUS_CONFIRMED,
                ],
                [
                    'block_id' => $block?->id,
                    'confirmed_quantity' => 100,
                    'transfer_date' => now()->subDays(5),
                    'confirmed_at' => now()->subDays(5),
                    'notes' => 'Semester intake block allocation: 100 mattresses.',
                ]
            );

            ProctorAssetAssignment::updateOrCreate(
                [
                    'manager_id' => $manager->id,
                    'proctor_id' => $proctor->id,
                    'item_type' => 'pillow',
                    'assigned_quantity' => 100,
                    'status' => ProctorAssetAssignment::STATUS_CONFIRMED,
                ],
                [
                    'block_id' => $block?->id,
                    'confirmed_quantity' => 100,
                    'transfer_date' => now()->subDays(5),
                    'confirmed_at' => now()->subDays(5),
                    'notes' => 'Semester intake block allocation: 100 pillows.',
                ]
            );

            // Pending handover transfer for proctor to accept
            ProctorAssetAssignment::updateOrCreate(
                [
                    'manager_id' => $manager->id,
                    'proctor_id' => $proctor->id,
                    'item_type' => 'mattress',
                    'assigned_quantity' => 50,
                    'status' => ProctorAssetAssignment::STATUS_PENDING,
                ],
                [
                    'block_id' => $block?->id,
                    'confirmed_quantity' => 0,
                    'transfer_date' => now(),
                    'notes' => 'Additional reserve transfer for Block ' . ($block?->block_code ?? '50'),
                ]
            );

            ProctorAssetAssignment::updateOrCreate(
                [
                    'manager_id' => $manager->id,
                    'proctor_id' => $proctor->id,
                    'item_type' => 'pillow',
                    'assigned_quantity' => 50,
                    'status' => ProctorAssetAssignment::STATUS_PENDING,
                ],
                [
                    'block_id' => $block?->id,
                    'confirmed_quantity' => 0,
                    'transfer_date' => now(),
                    'notes' => 'Additional reserve pillows transfer for Block ' . ($block?->block_code ?? '50'),
                ]
            );

            // 3. Stage 2 & 3: Student Asset Issues & Returns
            $allocations = StudentAllocation::with('student', 'room')->take(5)->get();
            if ($allocations->isNotEmpty()) {
                $firstAlloc = $allocations->first();
                $firstStudent = $firstAlloc->student;

                // Student 1: 1 confirmed mattress, 1 pending confirmation pillow
                StudentAssetIssue::updateOrCreate(
                    [
                        'student_id' => $firstStudent->id,
                        'item_type' => 'mattress',
                    ],
                    [
                        'proctor_id' => $proctor->id,
                        'block_id' => $firstAlloc->room?->block_id,
                        'room_id' => $firstAlloc->room_id,
                        'quantity' => 1,
                        'issue_date' => now()->subDays(3),
                        'status' => StudentAssetIssue::STATUS_CONFIRMED,
                        'student_confirmed_at' => now()->subDays(3),
                        'condition_status' => StudentAssetIssue::CONDITION_GOOD,
                    ]
                );

                StudentAssetIssue::updateOrCreate(
                    [
                        'student_id' => $firstStudent->id,
                        'item_type' => 'pillow',
                    ],
                    [
                        'proctor_id' => $proctor->id,
                        'block_id' => $firstAlloc->room?->block_id,
                        'room_id' => $firstAlloc->room_id,
                        'quantity' => 1,
                        'issue_date' => now()->subDays(1),
                        'status' => StudentAssetIssue::STATUS_PENDING, // Pending confirmation by student
                        'remarks' => 'Issued during morning check-in',
                    ]
                );

                // Student 2: Returned all items -> Eligible for Clearance
                if ($allocations->count() > 1) {
                    $secondAlloc = $allocations->get(1);
                    $secondStudent = $secondAlloc->student;

                    StudentAssetIssue::updateOrCreate(
                        [
                            'student_id' => $secondStudent->id,
                            'item_type' => 'mattress',
                        ],
                        [
                            'proctor_id' => $proctor->id,
                            'block_id' => $secondAlloc->room?->block_id,
                            'room_id' => $secondAlloc->room_id,
                            'quantity' => 1,
                            'issue_date' => now()->subDays(10),
                            'status' => StudentAssetIssue::STATUS_RETURNED,
                            'student_confirmed_at' => now()->subDays(10),
                            'return_date' => now()->subDay(),
                            'inspected_by' => $proctor->id,
                            'condition_status' => StudentAssetIssue::CONDITION_GOOD,
                            'remarks' => 'Returned intact, verified condition.',
                        ]
                    );

                    StudentAssetIssue::updateOrCreate(
                        [
                            'student_id' => $secondStudent->id,
                            'item_type' => 'pillow',
                        ],
                        [
                            'proctor_id' => $proctor->id,
                            'block_id' => $secondAlloc->room?->block_id,
                            'room_id' => $secondAlloc->room_id,
                            'quantity' => 1,
                            'issue_date' => now()->subDays(10),
                            'status' => StudentAssetIssue::STATUS_RETURNED,
                            'student_confirmed_at' => now()->subDays(10),
                            'return_date' => now()->subDay(),
                            'inspected_by' => $proctor->id,
                            'condition_status' => StudentAssetIssue::CONDITION_GOOD,
                            'remarks' => 'Returned clean and intact.',
                        ]
                    );
                }
            }

            // 4. Targeted Announcements / Notices
            // Manager-to-Proctors Notice (Internal Announcements)
            Announcement::updateOrCreate(
                ['title' => 'Internal Notice: Afternoon Warehouse Asset Count & Verification'],
                [
                    'author_id' => $manager->id,
                    'summary' => 'Proctor Manager memo regarding physical verification of mattress and pillow reserves.',
                    'content' => 'Mattress and pillow inventory count is scheduled for this afternoon at the Central Warehouse; please ensure all block handover slips are updated and verified before 3:00 PM.',
                    'category' => 'inventory',
                    'target_role' => Announcement::TARGET_PROCTOR,
                    'target_audience' => 'block_proctors',
                    'block_id' => null,
                    'priority' => Announcement::PRIORITY_URGENT,
                    'is_published' => true,
                    'published_at' => now()->subHours(2),
                ]
            );

            // Proctor-to-Students Notice
            Announcement::updateOrCreate(
                ['title' => 'Important: Asset Return Deadline & Dormitory Clearance Schedule'],
                [
                    'author_id' => $proctor->id,
                    'summary' => 'Mandatory deadline for returning issued mattresses and pillows for end-of-semester clearance.',
                    'content' => 'The deadline to return mattresses and pillows is Friday at 5:00 PM to the block proctor office. Ensure items are clean and undamaged to receive your clearance sign-off.',
                    'category' => 'clearance',
                    'target_role' => Announcement::TARGET_STUDENT,
                    'target_audience' => 'block_students',
                    'block_id' => $block?->id,
                    'priority' => Announcement::PRIORITY_CRITICAL,
                    'is_published' => true,
                    'published_at' => now()->subHours(1),
                ]
            );
        }
    }
}
