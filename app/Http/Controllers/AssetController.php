<?php

namespace App\Http\Controllers;

use App\Models\AssetInventory;
use App\Models\AttendanceAlert;
use App\Models\BlockProctorAssignment;
use App\Models\DormitoryBlock;
use App\Models\ProctorAssetAssignment;
use App\Models\StudentAllocation;
use App\Models\StudentAssetIssue;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class AssetController extends Controller
{
    // =========================================================================
    // 1. PROCTOR MANAGER: Warehouse Ledger & Manager-to-Proctor Transfers
    // =========================================================================

    public function managerIndex(Request $request): View
    {
        $mattressInventory = AssetInventory::getWarehouseStock(AssetInventory::ITEM_MATTRESS);
        $pillowInventory = AssetInventory::getWarehouseStock(AssetInventory::ITEM_PILLOW);

        // All active proctors with their assigned blocks
        $proctors = User::where('role', User::ROLE_PROCTOR)
            ->with(['activeProctorAssignments.block'])
            ->orderBy('name')
            ->get();

        // Transfers history with filters
        $statusFilter = $request->get('status');
        $transfersQuery = ProctorAssetAssignment::with(['manager', 'proctor', 'block'])->latest();

        if ($statusFilter && in_array($statusFilter, [ProctorAssetAssignment::STATUS_PENDING, ProctorAssetAssignment::STATUS_CONFIRMED, ProctorAssetAssignment::STATUS_REJECTED])) {
            $transfersQuery->where('status', $statusFilter);
        }

        $transfers = $transfersQuery->paginate(15)->withQueryString();

        // Calculate Proctors Stock Overview
        $proctorStockStats = [];
        foreach ($proctors as $pr) {
            $assignedBlocks = $pr->activeProctorAssignments->pluck('block.block_code')->filter()->join(', ') ?: 'Unassigned';

            // Confirmed transfers from manager
            $mattressReceived = (int) ProctorAssetAssignment::where('proctor_id', $pr->id)
                ->where('item_type', 'mattress')
                ->where('status', ProctorAssetAssignment::STATUS_CONFIRMED)
                ->sum('confirmed_quantity');

            $pillowReceived = (int) ProctorAssetAssignment::where('proctor_id', $pr->id)
                ->where('item_type', 'pillow')
                ->where('status', ProctorAssetAssignment::STATUS_CONFIRMED)
                ->sum('confirmed_quantity');

            // Currently issued to students and active (not returned)
            $mattressIssued = (int) StudentAssetIssue::where('proctor_id', $pr->id)
                ->where('item_type', 'mattress')
                ->whereIn('status', [StudentAssetIssue::STATUS_PENDING, StudentAssetIssue::STATUS_CONFIRMED])
                ->sum('quantity');

            $pillowIssued = (int) StudentAssetIssue::where('proctor_id', $pr->id)
                ->where('item_type', 'pillow')
                ->whereIn('status', [StudentAssetIssue::STATUS_PENDING, StudentAssetIssue::STATUS_CONFIRMED])
                ->sum('quantity');

            $proctorStockStats[] = [
                'proctor' => $pr,
                'block_code' => $assignedBlocks,
                'mattress_total' => $mattressReceived,
                'mattress_issued' => $mattressIssued,
                'mattress_available' => max(0, $mattressReceived - $mattressIssued),
                'pillow_total' => $pillowReceived,
                'pillow_issued' => $pillowIssued,
                'pillow_available' => max(0, $pillowReceived - $pillowIssued),
            ];
        }

        $blocks = DormitoryBlock::orderBy('name')->get();

        return view('manager.assets.index', compact(
            'mattressInventory',
            'pillowInventory',
            'proctors',
            'transfers',
            'proctorStockStats',
            'blocks',
            'statusFilter'
        ));
    }

    public function managerTransfer(Request $request)
    {
        $validated = $request->validate([
            'proctor_id' => 'required|exists:users,id',
            'block_id' => 'nullable|exists:dormitory_blocks,id',
            'item_type' => 'required|in:mattress,pillow',
            'assigned_quantity' => 'required|integer|min:1',
            'notes' => 'nullable|string|max:1000',
        ]);

        $itemType = $validated['item_type'];
        $qty = (int) $validated['assigned_quantity'];

        $warehouse = AssetInventory::getWarehouseStock($itemType);

        if ($warehouse->available_quantity < $qty) {
            return back()->with('error', "Insufficient warehouse stock! Only {$warehouse->available_quantity} {$itemType}(s) available in warehouse.");
        }

        // Deduct from warehouse available and mark as allocated
        $warehouse->available_quantity -= $qty;
        $warehouse->allocated_quantity += $qty;
        $warehouse->save();

        // If block_id wasn't specified, attempt to infer from proctor's active assignment
        $blockId = $validated['block_id'] ?? null;
        if (!$blockId) {
            $assignment = BlockProctorAssignment::where('proctor_id', $validated['proctor_id'])->whereNull('ended_at')->first()
                ?? BlockProctorAssignment::where('proctor_id', $validated['proctor_id'])->latest('id')->first();
            $blockId = $assignment?->block_id;
        }

        $transfer = ProctorAssetAssignment::create([
            'manager_id' => Auth::id(),
            'proctor_id' => $validated['proctor_id'],
            'block_id' => $blockId,
            'item_type' => $itemType,
            'assigned_quantity' => $qty,
            'confirmed_quantity' => 0,
            'status' => ProctorAssetAssignment::STATUS_PENDING,
            'transfer_date' => now()->toDateString(),
            'notes' => $validated['notes'] ?? null,
        ]);

        // Notify Proctor
        AttendanceAlert::create([
            'user_id' => $validated['proctor_id'],
            'alert_type' => 'asset_transfer',
            'title' => 'Warehouse Asset Transfer Initiated',
            'message' => "Manager has transferred {$qty} {$itemType}(s) to your custody. Please physically inspect and click 'Accept / Confirm Handover'.",
            'action_url' => route('proctor.assets.index'),
            'priority' => 'high',
        ]);

        return back()->with('success', "Asset transfer of {$qty} {$itemType}(s) initiated. Waiting for proctor to accept handover.");
    }

    public function managerStockUpdate(Request $request)
    {
        $validated = $request->validate([
            'item_code' => 'required|in:MATTRESS,PILLOW',
            'additional_quantity' => 'required|integer|min:1',
            'unit_penalty_charge' => 'nullable|numeric|min:0',
        ]);

        $item = AssetInventory::where('item_code', $validated['item_code'])->firstOrFail();
        $item->total_quantity += (int)$validated['additional_quantity'];
        $item->available_quantity += (int)$validated['additional_quantity'];

        if (isset($validated['unit_penalty_charge'])) {
            $item->unit_penalty_charge = $validated['unit_penalty_charge'];
        }

        $item->save();

        return back()->with('success', "Warehouse inventory updated! Added {$validated['additional_quantity']} {$item->item_name}(s) to stock.");
    }

    // =========================================================================
    // 2. PROCTOR: Stock Balance, Handover Acceptance & Student Issuance/Return
    // =========================================================================

    public function proctorIndex(Request $request): View
    {
        $proctor = Auth::user();

        // Managing block
        $blockAssignment = BlockProctorAssignment::with('block')->where('proctor_id', $proctor->id)->whereNull('ended_at')->first()
            ?? BlockProctorAssignment::with('block')->where('proctor_id', $proctor->id)->latest('id')->first();
        $block = $blockAssignment?->block;

        // Stage 1: Pending Handover Transfers from Manager
        $pendingTransfers = ProctorAssetAssignment::where('proctor_id', $proctor->id)
            ->where('status', ProctorAssetAssignment::STATUS_PENDING)
            ->with(['manager', 'block'])
            ->latest()
            ->get();

        // Confirmed transfers history
        $confirmedTransfers = ProctorAssetAssignment::where('proctor_id', $proctor->id)
            ->where('status', ProctorAssetAssignment::STATUS_CONFIRMED)
            ->with(['manager', 'block'])
            ->latest()
            ->take(10)
            ->get();

        // Calculate Proctor's Current Stock
        $mattressReceived = (int) ProctorAssetAssignment::where('proctor_id', $proctor->id)
            ->where('item_type', 'mattress')
            ->where('status', ProctorAssetAssignment::STATUS_CONFIRMED)
            ->sum('confirmed_quantity');

        $pillowReceived = (int) ProctorAssetAssignment::where('proctor_id', $proctor->id)
            ->where('item_type', 'pillow')
            ->where('status', ProctorAssetAssignment::STATUS_CONFIRMED)
            ->sum('confirmed_quantity');

        $mattressIssuedActive = (int) StudentAssetIssue::where('proctor_id', $proctor->id)
            ->where('item_type', 'mattress')
            ->whereIn('status', [StudentAssetIssue::STATUS_PENDING, StudentAssetIssue::STATUS_CONFIRMED])
            ->sum('quantity');

        $pillowIssuedActive = (int) StudentAssetIssue::where('proctor_id', $proctor->id)
            ->where('item_type', 'pillow')
            ->whereIn('status', [StudentAssetIssue::STATUS_PENDING, StudentAssetIssue::STATUS_CONFIRMED])
            ->sum('quantity');

        $mattressReturned = (int) StudentAssetIssue::where('proctor_id', $proctor->id)
            ->where('item_type', 'mattress')
            ->where('status', StudentAssetIssue::STATUS_RETURNED)
            ->sum('quantity');

        $pillowReturned = (int) StudentAssetIssue::where('proctor_id', $proctor->id)
            ->where('item_type', 'pillow')
            ->where('status', StudentAssetIssue::STATUS_RETURNED)
            ->sum('quantity');

        $currentStock = [
            'mattress_total' => $mattressReceived,
            'mattress_issued' => $mattressIssuedActive,
            'mattress_available' => max(0, $mattressReceived - $mattressIssuedActive),
            'mattress_returned' => $mattressReturned,
            'pillow_total' => $pillowReceived,
            'pillow_issued' => $pillowIssuedActive,
            'pillow_available' => max(0, $pillowReceived - $pillowIssuedActive),
            'pillow_returned' => $pillowReturned,
        ];

        // Stage 2 & 3: Student Asset Issues
        $statusFilter = $request->get('status');
        $issuesQuery = StudentAssetIssue::where('proctor_id', $proctor->id)
            ->with(['student', 'room', 'block', 'inspector'])
            ->latest('issue_date');

        if ($statusFilter) {
            $issuesQuery->where('status', $statusFilter);
        }

        $studentIssues = $issuesQuery->paginate(15)->withQueryString();

        // Students in proctor's block available for asset issuance
        $blockStudents = collect();
        if ($block) {
            $blockStudents = StudentAllocation::whereHas('room', fn($q) => $q->where('block_id', $block->id))
                ->where('status', StudentAllocation::STATUS_ACTIVE)
                ->with(['student', 'room'])
                ->get()
                ->pluck('student', 'student_id')
                ->values();
        }

        return view('proctor.assets.index', compact(
            'block',
            'pendingTransfers',
            'confirmedTransfers',
            'currentStock',
            'studentIssues',
            'blockStudents',
            'statusFilter'
        ));
    }

    public function proctorConfirmTransfer(Request $request, ProctorAssetAssignment $assignment)
    {
        if ($assignment->proctor_id !== Auth::id()) {
            abort(403);
        }

        $action = $request->input('action', 'accept'); // 'accept' or 'reject'

        if ($action === 'accept') {
            $assignment->status = ProctorAssetAssignment::STATUS_CONFIRMED;
            $assignment->confirmed_quantity = $assignment->assigned_quantity;
            $assignment->confirmed_at = now();
            $assignment->save();

            return back()->with('success', "Handover confirmed! {$assignment->assigned_quantity} {$assignment->item_type}(s) added to your Proctor Current Stock.");
        }

        // Rejection
        $validated = $request->validate([
            'rejection_reason' => 'required|string|max:500',
        ]);

        $assignment->status = ProctorAssetAssignment::STATUS_REJECTED;
        $assignment->rejection_reason = $validated['rejection_reason'];
        $assignment->save();

        // Return stock back to warehouse
        $warehouse = AssetInventory::where('item_code', strtoupper($assignment->item_type))->first();
        if ($warehouse) {
            $warehouse->available_quantity += $assignment->assigned_quantity;
            $warehouse->allocated_quantity = max(0, $warehouse->allocated_quantity - $assignment->assigned_quantity);
            $warehouse->save();
        }

        return back()->with('info', "Handover transfer rejected. Quantities returned to warehouse balance.");
    }

    public function proctorIssueAsset(Request $request)
    {
        $validated = $request->validate([
            'student_id' => 'required|exists:users,id',
            'items' => 'required|array|min:1',
            'items.*' => 'in:mattress,pillow',
            'remarks' => 'nullable|string|max:500',
        ]);

        $proctor = Auth::user();
        $student = User::findOrFail($validated['student_id']);
        $allocation = $student->activeAllocation()->with('room.block')->first();

        $mattressQty = in_array('mattress', $validated['items']) ? 1 : 0;
        $pillowQty = in_array('pillow', $validated['items']) ? 1 : 0;

        // Calculate available stock
        $mattressReceived = (int) ProctorAssetAssignment::where('proctor_id', $proctor->id)
            ->where('item_type', 'mattress')
            ->where('status', ProctorAssetAssignment::STATUS_CONFIRMED)
            ->sum('confirmed_quantity');

        $mattressIssued = (int) StudentAssetIssue::where('proctor_id', $proctor->id)
            ->where('item_type', 'mattress')
            ->whereIn('status', [StudentAssetIssue::STATUS_PENDING, StudentAssetIssue::STATUS_CONFIRMED])
            ->sum('quantity');

        $pillowReceived = (int) ProctorAssetAssignment::where('proctor_id', $proctor->id)
            ->where('item_type', 'pillow')
            ->where('status', ProctorAssetAssignment::STATUS_CONFIRMED)
            ->sum('confirmed_quantity');

        $pillowIssued = (int) StudentAssetIssue::where('proctor_id', $proctor->id)
            ->where('item_type', 'pillow')
            ->whereIn('status', [StudentAssetIssue::STATUS_PENDING, StudentAssetIssue::STATUS_CONFIRMED])
            ->sum('quantity');

        if ($mattressQty > 0 && ($mattressReceived - $mattressIssued) < 1) {
            return back()->with('error', 'Insufficient mattress stock in your allocated balance! Please request more from Proctor Manager.');
        }

        if ($pillowQty > 0 && ($pillowReceived - $pillowIssued) < 1) {
            return back()->with('error', 'Insufficient pillow stock in your allocated balance! Please request more from Proctor Manager.');
        }

        // Issue items
        foreach ($validated['items'] as $itemType) {
            StudentAssetIssue::create([
                'student_id' => $student->id,
                'proctor_id' => $proctor->id,
                'block_id' => $allocation?->room?->block_id,
                'room_id' => $allocation?->room_id,
                'item_type' => $itemType,
                'quantity' => 1,
                'issue_date' => now()->toDateString(),
                'status' => StudentAssetIssue::STATUS_PENDING, // Student must confirm receipt
                'remarks' => $validated['remarks'] ?? null,
            ]);
        }

        // Notify Student
        AttendanceAlert::create([
            'user_id' => $student->id,
            'alert_type' => 'asset_issued',
            'title' => 'Dormitory Assets Issued',
            'message' => "Proctor {$proctor->name} has issued dormitory items (" . implode(', ', array_map('ucfirst', $validated['items'])) . ") to you. Please confirm receipt on your portal.",
            'action_url' => route('student.assets.index'),
            'priority' => 'high',
        ]);

        return back()->with('success', "Assets successfully issued to {$student->name}. Waiting for student confirmation on their portal.");
    }

    public function proctorReturnAsset(Request $request, StudentAssetIssue $issue)
    {
        if ($issue->proctor_id !== Auth::id()) {
            abort(403);
        }

        $validated = $request->validate([
            'condition_status' => 'required|in:good,damaged,missing',
            'penalty_charge' => 'nullable|numeric|min:0',
            'remarks' => 'nullable|string|max:500',
        ]);

        $condition = $validated['condition_status'];
        $penalty = (float) ($validated['penalty_charge'] ?? 0);

        if ($condition === 'good') {
            $issue->status = StudentAssetIssue::STATUS_RETURNED;
            $issue->condition_status = StudentAssetIssue::CONDITION_GOOD;
            $issue->penalty_charge = 0;
            $issue->is_penalty_paid = true;
        } elseif ($condition === 'damaged') {
            $issue->status = StudentAssetIssue::STATUS_DAMAGED;
            $issue->condition_status = StudentAssetIssue::CONDITION_DAMAGED;
            $issue->penalty_charge = $penalty > 0 ? $penalty : ($issue->item_type === 'mattress' ? 500.00 : 150.00);
            $issue->is_penalty_paid = false;
        } elseif ($condition === 'missing') {
            $issue->status = StudentAssetIssue::STATUS_MISSING;
            $issue->condition_status = StudentAssetIssue::CONDITION_MISSING;
            $issue->penalty_charge = $penalty > 0 ? $penalty : ($issue->item_type === 'mattress' ? 1200.00 : 400.00);
            $issue->is_penalty_paid = false;
        }

        $issue->return_date = now()->toDateString();
        $issue->inspected_by = Auth::id();
        $issue->remarks = $validated['remarks'] ?? $issue->remarks;
        $issue->save();

        $student = $issue->student;
        $eligible = StudentAssetIssue::isStudentEligibleForClearance($student->id);

        $msg = "Asset return logged as '{$condition}'. ";
        if ($eligible) {
            $msg .= "Student {$student->name} is now ELIGIBLE FOR CLEARANCE!";
        } else {
            $msg .= "Student {$student->name} has remaining items or pending penalty charges.";
        }

        return back()->with('success', $msg);
    }

    public function proctorClearanceList(Request $request): View
    {
        $proctor = Auth::user();
        $assignment = BlockProctorAssignment::with('block')->where('proctor_id', $proctor->id)->whereNull('ended_at')->first()
            ?? BlockProctorAssignment::with('block')->where('proctor_id', $proctor->id)->latest('id')->first();
        $block = $assignment?->block;

        $residentsQuery = StudentAllocation::with(['student', 'room'])
            ->where('status', StudentAllocation::STATUS_ACTIVE);

        if ($block) {
            $residentsQuery->whereHas('room', fn($q) => $q->where('block_id', $block->id));
        }

        $allocations = $residentsQuery->get();

        $clearanceData = [];
        $totalEligible = 0;
        $totalBlocked = 0;

        foreach ($allocations as $alloc) {
            $st = $alloc->student;
            $mattressIssue = StudentAssetIssue::where('student_id', $st->id)->where('item_type', 'mattress')->latest()->first();
            $pillowIssue = StudentAssetIssue::where('student_id', $st->id)->where('item_type', 'pillow')->latest()->first();

            $isEligible = StudentAssetIssue::isStudentEligibleForClearance($st->id);
            if ($isEligible) {
                $totalEligible++;
            } else {
                $totalBlocked++;
            }

            $unpaidPenalties = (float) StudentAssetIssue::where('student_id', $st->id)
                ->where('is_penalty_paid', false)
                ->sum('penalty_charge');

            $clearanceData[] = [
                'student' => $st,
                'room' => $alloc->room,
                'mattress_issue' => $mattressIssue,
                'pillow_issue' => $pillowIssue,
                'is_eligible' => $isEligible,
                'unpaid_penalties' => $unpaidPenalties,
            ];
        }

        return view('proctor.assets.clearance', compact(
            'block',
            'clearanceData',
            'totalEligible',
            'totalBlocked'
        ));
    }

    // =========================================================================
    // 3. STUDENT: Portal View, Receipt Confirmation & Clearance Slip
    // =========================================================================

    public function studentIndex(): View
    {
        $student = Auth::user();
        $allocation = $student->activeAllocation()->with(['room.block', 'allocator'])->first();

        $issuedAssets = StudentAssetIssue::where('student_id', $student->id)
            ->with(['proctor', 'room', 'block', 'inspector'])
            ->latest('issue_date')
            ->get();

        $hasPendingConfirmation = $issuedAssets->where('status', StudentAssetIssue::STATUS_PENDING)->isNotEmpty();
        $isEligibleForClearance = StudentAssetIssue::isStudentEligibleForClearance($student->id);

        $unpaidPenalties = (float) $issuedAssets->where('is_penalty_paid', false)->sum('penalty_charge');

        return view('student.assets.index', compact(
            'student',
            'allocation',
            'issuedAssets',
            'hasPendingConfirmation',
            'isEligibleForClearance',
            'unpaidPenalties'
        ));
    }

    public function studentConfirmAsset(StudentAssetIssue $issue)
    {
        if ($issue->student_id !== Auth::id()) {
            abort(403);
        }

        if ($issue->status !== StudentAssetIssue::STATUS_PENDING) {
            return back()->with('info', 'This asset has already been confirmed.');
        }

        $issue->status = StudentAssetIssue::STATUS_CONFIRMED;
        $issue->student_confirmed_at = now();
        $issue->save();

        return back()->with('success', "You have confirmed receipt of your {$issue->item_type}. Handover is logged.");
    }
}
