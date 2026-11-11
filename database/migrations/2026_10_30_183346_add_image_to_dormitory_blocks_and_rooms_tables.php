<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (! Schema::hasColumn('dormitory_blocks', 'image')) {
            Schema::table('dormitory_blocks', function (Blueprint $table) {
                $table->string('image')->nullable()->after('description');
            });
        }

        if (! Schema::hasColumn('rooms', 'image')) {
            Schema::table('rooms', function (Blueprint $table) {
                $table->string('image')->nullable()->after('notes');
            });
        }

        // Backfill existing blocks
        $blocks = DB::table('dormitory_blocks')->get();
        foreach ($blocks as $b) {
            $image = $this->determineBlockImage($b->block_code);
            DB::table('dormitory_blocks')->where('id', $b->id)->update(['image' => $image]);
        }

        // Backfill existing rooms
        $rooms = DB::table('rooms')->get();
        foreach ($rooms as $r) {
            $image = $this->determineRoomImage($r->room_number);
            DB::table('rooms')->where('id', $r->id)->update(['image' => $image]);
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasColumn('dormitory_blocks', 'image')) {
            Schema::table('dormitory_blocks', function (Blueprint $table) {
                $table->dropColumn('image');
            });
        }

        if (Schema::hasColumn('rooms', 'image')) {
            Schema::table('rooms', function (Blueprint $table) {
                $table->dropColumn('image');
            });
        }
    }

    private function isPrime(int $n): bool
    {
        if ($n < 2) {
            return false;
        }
        if ($n === 2 || $n === 3) {
            return true;
        }
        if ($n % 2 === 0 || $n % 3 === 0) {
            return false;
        }
        for ($i = 5; $i * $i <= $n; $i += 6) {
            if ($n % $i === 0 || $n % ($i + 2) === 0) {
                return false;
            }
        }
        return true;
    }

    private function extractNumber(?string $value): ?int
    {
        if ($value === null) {
            return null;
        }
        if (preg_match('/\d+/', $value, $matches)) {
            return (int) $matches[0];
        }
        return null;
    }

    private function determineBlockImage(?string $code): string
    {
        $num = $this->extractNumber($code);
        if ($num === null) {
            return 'composit_only_block.png';
        }

        if ($this->isPrime($num)) {
            return 'prime_only_block.png';
        }

        if ($num % 2 !== 0) {
            return 'odd_not_prime_block.jpg';
        }

        return 'composit_only_block.png';
    }

    private function determineRoomImage(?string $roomNumber): string
    {
        $num = $this->extractNumber($roomNumber);
        if ($num === null) {
            return 'composit_only_room.jpg';
        }

        if ($this->isPrime($num)) {
            return 'prime_only_room.jpg';
        }

        if ($num % 2 !== 0) {
            return 'odd_not_prime_room.jpg';
        }

        return 'composit_only_room.jpg';
    }
};
