<?php

namespace Database\Seeders;

use App\Models\Announcement;
use App\Models\AuditLog;
use App\Models\BlockProctorAssignment;
use App\Models\Comment;
use App\Models\DormitoryBlock;
use App\Models\Room;
use App\Models\StudentAllocation;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // 1. Initial Administrator Account
        $admin = User::updateOrCreate(
            ['university_id' => '1000'],
            [
                'name' => 'Eyob',
                'email' => 'eyobnigatu18@gmail.com',
                'password' => Hash::make('eyoba123'),
                'role' => User::ROLE_ADMIN,
                'gender' => 'male',
                'academic_year' => null,
                'registration_status' => User::REGISTRATION_COMPLETED,
                'email_verified_at' => now(),
            ]
        );

        // 2. Proctor Manager
        $manager = User::updateOrCreate(
            ['university_id' => '2001'],
            [
                'name' => 'Abebe Kebede',
                'email' => 'manager@woldia.edu.et',
                'password' => Hash::make('password123'),
                'role' => User::ROLE_PROCTOR_MANAGER,
                'gender' => 'male',
                'academic_year' => null,
                'registration_status' => User::REGISTRATION_COMPLETED,
                'email_verified_at' => now(),
            ]
        );

        // 3. Proctors
        $proctor1 = User::updateOrCreate(
            ['university_id' => '3001'],
            [
                'name' => 'Tigist Haile',
                'email' => 'tigist.proctor@woldia.edu.et',
                'password' => Hash::make('password123'),
                'role' => User::ROLE_PROCTOR,
                'gender' => 'female',
                'academic_year' => null,
                'registration_status' => User::REGISTRATION_COMPLETED,
                'email_verified_at' => now(),
            ]
        );

        $proctor2 = User::updateOrCreate(
            ['university_id' => '3002'],
            [
                'name' => 'Dawit Tadesse',
                'email' => 'dawit.proctor@woldia.edu.et',
                'password' => Hash::make('password123'),
                'role' => User::ROLE_PROCTOR,
                'gender' => 'male',
                'academic_year' => null,
                'registration_status' => User::REGISTRATION_COMPLETED,
                'email_verified_at' => now(),
            ]
        );

        // 4. Pre-registered (Unclaimed) Proctor
        User::updateOrCreate(
            ['university_id' => '3003'],
            [
                'name' => 'Kassahun Belay',
                'email' => null,
                'password' => null,
                'role' => User::ROLE_PROCTOR,
                'gender' => 'male',
                'academic_year' => null,
                'registration_status' => User::REGISTRATION_PENDING,
            ]
        );

        // 5. Dormitory Blocks with Gender Categories
        $blockA = DormitoryBlock::updateOrCreate(
            ['block_code' => 'BLK-45'],
            [
                'name' => 'Fasiledes Hall',
                'gender_category' => 'male',
                'location' => 'Main Campus, North Wing',
                'description' => 'Main dormitory block for undergraduate male students.',
                'status' => 'active',
            ]
        );

        $blockB = DormitoryBlock::updateOrCreate(
            ['block_code' => 'BLK-46'],
            [
                'name' => 'Lalibela Hall',
                'gender_category' => 'female',
                'location' => 'Main Campus, East Wing',
                'description' => 'Dedicated dormitory block for female engineering and medicine students.',
                'status' => 'active',
            ]
        );

        $blockC = DormitoryBlock::updateOrCreate(
            ['block_code' => 'BLK-47'],
            [
                'name' => 'Tewodros Hall',
                'gender_category' => 'male',
                'location' => 'Main Campus, West Wing',
                'description' => 'Freshman male student block with modernized amenities.',
                'status' => 'active',
            ]
        );

        // 6. Proctor Assignments
        // Dawit -> Male Block (Block 45)
        BlockProctorAssignment::updateOrCreate(
            ['block_id' => $blockA->id, 'proctor_id' => $proctor2->id, 'ended_at' => null],
            [
                'assigned_by' => $manager->id,
                'assigned_at' => now()->subMonths(2),
            ]
        );

        // Tigist -> Female Block (Block 46) & Block 45
        BlockProctorAssignment::updateOrCreate(
            ['block_id' => $blockB->id, 'proctor_id' => $proctor1->id, 'ended_at' => null],
            [
                'assigned_by' => $manager->id,
                'assigned_at' => now()->subMonths(1),
            ]
        );

        BlockProctorAssignment::updateOrCreate(
            ['block_id' => $blockA->id, 'proctor_id' => $proctor1->id, 'ended_at' => null],
            [
                'assigned_by' => $manager->id,
                'assigned_at' => now()->subMonths(1),
            ]
        );

        // 7. Rooms
        $roomsA = [];
        foreach (['101', '102', '103', '104'] as $rNum) {
            $roomsA[$rNum] = Room::updateOrCreate(
                ['block_id' => $blockA->id, 'room_number' => $rNum],
                [
                    'capacity' => 4,
                    'status' => 'available',
                    'notes' => 'Equipped with 4 study desks, lockers, and balcony access.',
                ]
            );
        }

        $roomsB = [];
        foreach (['201', '202'] as $rNum) {
            $roomsB[$rNum] = Room::updateOrCreate(
                ['block_id' => $blockB->id, 'room_number' => $rNum],
                [
                    'capacity' => 4,
                    'status' => 'available',
                    'notes' => 'Quiet zone floor with attached washrooms.',
                ]
            );
        }

        // 8. Registered Students with assigned genders
        // Yared (Male) -> Allocated to Block 45 (Male block)
        $student1 = User::updateOrCreate(
            ['university_id' => 'WLD/0101/14'],
            [
                'name' => 'Yared Tesfaye',
                'email' => 'yared@woldia.edu.et',
                'password' => Hash::make('password123'),
                'role' => User::ROLE_STUDENT,
                'gender' => 'male',
                'department' => 'Computer Science',
                'academic_year' => 3,
                'registration_status' => User::REGISTRATION_COMPLETED,
                'email_verified_at' => now(),
            ]
        );

        // Sara (Female) -> Allocated to Block 46 (Female block)
        $student2 = User::updateOrCreate(
            ['university_id' => 'WLD/0102/14'],
            [
                'name' => 'Sara Daniel',
                'email' => 'sara@woldia.edu.et',
                'password' => Hash::make('password123'),
                'role' => User::ROLE_STUDENT,
                'gender' => 'female',
                'department' => 'Medicine',
                'academic_year' => 2,
                'registration_status' => User::REGISTRATION_COMPLETED,
                'email_verified_at' => now(),
            ]
        );

        // Biruk (Male) -> Unallocated student eligible for allocation
        $student3 = User::updateOrCreate(
            ['university_id' => 'WLD/0103/14'],
            [
                'name' => 'Biruk Getachew',
                'email' => 'biruk@woldia.edu.et',
                'password' => Hash::make('password123'),
                'role' => User::ROLE_STUDENT,
                'gender' => 'male',
                'department' => 'Electrical Engineering',
                'academic_year' => 4,
                'registration_status' => User::REGISTRATION_COMPLETED,
                'email_verified_at' => now(),
            ]
        );

        // 9. Pre-registered (Unclaimed) Students
        User::updateOrCreate(
            ['university_id' => 'WLD/0201/16'],
            [
                'name' => 'Bethlehem Assefa',
                'email' => null,
                'password' => null,
                'role' => User::ROLE_STUDENT,
                'gender' => 'female',
                'department' => 'Software Engineering',
                'academic_year' => 1,
                'registration_status' => User::REGISTRATION_PENDING,
            ]
        );

        User::updateOrCreate(
            ['university_id' => 'WLD/0202/16'],
            [
                'name' => 'Nahom Kifle',
                'email' => null,
                'password' => null,
                'role' => User::ROLE_STUDENT,
                'gender' => 'male',
                'department' => 'Mechanical Engineering',
                'academic_year' => 2,
                'registration_status' => User::REGISTRATION_PENDING,
            ]
        );

        // 10. Student Allocations (respecting gender)
        StudentAllocation::updateOrCreate(
            ['student_id' => $student1->id, 'status' => StudentAllocation::STATUS_ACTIVE],
            [
                'room_id' => $roomsA['101']->id,
                'allocated_by' => $proctor1->id,
                'allocated_at' => now()->subWeeks(3),
                'remarks' => 'Assigned bed #1 near window',
            ]
        );

        StudentAllocation::updateOrCreate(
            ['student_id' => $student2->id, 'status' => StudentAllocation::STATUS_ACTIVE],
            [
                'room_id' => $roomsB['201']->id,
                'allocated_by' => $proctor1->id,
                'allocated_at' => now()->subWeeks(2),
                'remarks' => 'Assigned bed #2',
            ]
        );

        // 11. Comments / Feedback
        Comment::updateOrCreate(
            ['user_id' => $student1->id, 'title' => 'Water pressure low on 1st floor'],
            [
                'category' => 'maintenance',
                'message' => 'The water pressure in Block 45 first floor rest rooms drops drastically in morning hours. Please inspect the main pump.',
                'status' => Comment::STATUS_IN_PROGRESS,
                'response' => 'Maintenance team dispatched to inspect valve manifold on Thursday.',
                'responded_by' => $manager->id,
                'responded_at' => now()->subDays(1),
            ]
        );

        Comment::updateOrCreate(
            ['user_id' => $student2->id, 'title' => 'Reading Room WiFi Extender'],
            [
                'category' => 'suggestion',
                'message' => 'Could we please have a WiFi signal repeater added to the Lalibela Hall study hall for online research?',
                'status' => Comment::STATUS_PENDING,
            ]
        );

        // 12. Announcements
        Announcement::updateOrCreate(
            ['slug' => 'dormitory-registration-semester-1-2026'],
            [
                'author_id' => $admin->id,
                'title' => 'Academic Year Dormitory Clearance and Check-In Schedule',
                'summary' => 'Official dates and procedure for returning and new students checking into Woldia University residence halls.',
                'content' => "Welcome to Woldia University Residence Life.\n\nAll pre-registered students are required to complete their digital onboarding via this portal before physical room keys can be issued by your block proctor.\n\nPlease verify that your university identification number matches your registrar slip. Proctors will begin physical inspections from 8:00 AM each weekday.",
                'category' => 'General Notice',
                'is_published' => true,
                'published_at' => now()->subDays(3),
            ]
        );

        AuditLog::log('system_seed', 'System initialized with gender-segregated database seed data.', null, $admin);
    }
}
