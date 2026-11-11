<?php

namespace App\Services;

use App\Models\DormitoryBlock;
use App\Models\Room;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class QuerySortService
{
    /**
     * Apply sorting to User / Student queries.
     *
     * Supported columns:
     * - 'name': Alphabetical sorting (A-Z, Z-A)
     * - 'university_id': Natural numeric sorting (Low-High, High-Low)
     */
    public static function sortUsers(Builder $query, Request $request, string $default = 'latest'): Builder
    {
        $sort = $request->query('sort');
        $direction = strtolower($request->query('direction', 'asc')) === 'desc' ? 'DESC' : 'ASC';
        $driver = DB::connection()->getDriverName();

        if ($sort === 'name') {
            return $query->orderByRaw("LOWER(name) {$direction}");
        }

        if ($sort === 'university_id') {
            if ($driver === 'pgsql') {
                return $query->orderByRaw("NULLIF(regexp_replace(university_id, '\D', '', 'g'), '')::bigint {$direction} NULLS LAST, university_id {$direction}");
            } elseif ($driver === 'mysql') {
                return $query->orderByRaw("CAST(NULLIF(regexp_replace(university_id, '[^0-9]', ''), '') AS UNSIGNED) {$direction}, university_id {$direction}");
            } else {
                return $query->orderByRaw("CAST(university_id AS INTEGER) {$direction}, university_id {$direction}");
            }
        }

        if ($default === 'latest') {
            return $query->latest('id');
        }

        return $query;
    }

    /**
     * Apply sorting to DormitoryBlock queries.
     *
     * Supported columns:
     * - 'block_code': Natural numeric code sorting (01, BLK-45, BLK-46...)
     * - 'name': Alphabetical sorting (A-Z, Z-A)
     */
    public static function sortBlocks(Builder $query, Request $request): Builder
    {
        $sort = $request->query('sort');
        $direction = strtolower($request->query('direction', 'asc')) === 'desc' ? 'DESC' : 'ASC';
        $driver = DB::connection()->getDriverName();

        if ($sort === 'name') {
            return $query->orderByRaw("LOWER(name) {$direction}");
        }

        if ($sort === 'block_code') {
            if ($driver === 'pgsql') {
                return $query->orderByRaw("NULLIF(regexp_replace(block_code, '\D', '', 'g'), '')::bigint {$direction} NULLS LAST, block_code {$direction}");
            } elseif ($driver === 'mysql') {
                return $query->orderByRaw("CAST(NULLIF(regexp_replace(block_code, '[^0-9]', ''), '') AS UNSIGNED) {$direction}, block_code {$direction}");
            } else {
                return $query->orderByRaw("CAST(block_code AS INTEGER) {$direction}, block_code {$direction}");
            }
        }

        // Default: Sort by block_code natural ascending
        if ($driver === 'pgsql') {
            return $query->orderByRaw("NULLIF(regexp_replace(block_code, '\D', '', 'g'), '')::bigint ASC NULLS LAST, block_code ASC");
        }

        return $query->orderBy('block_code', 'asc');
    }

    /**
     * Apply sorting to Room queries.
     *
     * Supported columns:
     * - 'room_number': Natural numeric room sorting (1, 2, ..., 101, 102...)
     * - 'block': Dormitory block code
     */
    public static function sortRooms(Builder $query, Request $request, string $default = 'latest'): Builder
    {
        $sort = $request->query('sort');
        $direction = strtolower($request->query('direction', 'asc')) === 'desc' ? 'DESC' : 'ASC';
        $driver = DB::connection()->getDriverName();

        if ($sort === 'room_number') {
            if ($driver === 'pgsql') {
                return $query->orderByRaw("NULLIF(regexp_replace(room_number, '\D', '', 'g'), '')::bigint {$direction} NULLS LAST, room_number {$direction}");
            } elseif ($driver === 'mysql') {
                return $query->orderByRaw("CAST(NULLIF(regexp_replace(room_number, '[^0-9]', ''), '') AS UNSIGNED) {$direction}, room_number {$direction}");
            } else {
                return $query->orderByRaw("CAST(room_number AS INTEGER) {$direction}, room_number {$direction}");
            }
        }

        if ($sort === 'block') {
            return $query->orderBy(
                DormitoryBlock::select('block_code')->whereColumn('dormitory_blocks.id', 'rooms.block_id'),
                $direction
            );
        }

        if ($default === 'room_number') {
            if ($driver === 'pgsql') {
                return $query->orderByRaw("NULLIF(regexp_replace(room_number, '\D', '', 'g'), '')::bigint ASC NULLS LAST, room_number ASC");
            }
            return $query->orderBy('room_number', 'asc');
        }

        return $query->latest('id');
    }

    /**
     * Apply sorting to StudentAllocation queries (Proctor Allocated Students).
     */
    public static function sortAllocations(Builder $query, Request $request): Builder
    {
        $sort = $request->query('sort');
        $direction = strtolower($request->query('direction', 'asc')) === 'desc' ? 'DESC' : 'ASC';
        $driver = DB::connection()->getDriverName();

        if ($sort === 'name') {
            return $query->orderBy(
                User::select('name')->whereColumn('users.id', 'student_allocations.student_id'),
                $direction
            );
        }

        if ($sort === 'university_id') {
            if ($driver === 'pgsql') {
                return $query->orderBy(
                    DB::raw("(SELECT NULLIF(regexp_replace(university_id, '\D', '', 'g'), '')::bigint FROM users WHERE users.id = student_allocations.student_id)"),
                    $direction
                );
            }
            return $query->orderBy(
                User::select('university_id')->whereColumn('users.id', 'student_allocations.student_id'),
                $direction
            );
        }

        if ($sort === 'room_number') {
            if ($driver === 'pgsql') {
                return $query->orderBy(
                    DB::raw("(SELECT NULLIF(regexp_replace(room_number, '\D', '', 'g'), '')::bigint FROM rooms WHERE rooms.id = student_allocations.room_id)"),
                    $direction
                );
            }
            return $query->orderBy(
                Room::select('room_number')->whereColumn('rooms.id', 'student_allocations.room_id'),
                $direction
            );
        }

        return $query->latest('allocated_at');
    }
}
