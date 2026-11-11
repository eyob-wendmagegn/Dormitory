<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AssetInventory extends Model
{
    use HasFactory;

    protected $table = 'assets_inventories';

    public const ITEM_MATTRESS = 'mattress';
    public const ITEM_PILLOW = 'pillow';

    protected $fillable = [
        'item_name',
        'item_code',
        'total_quantity',
        'available_quantity',
        'allocated_quantity',
        'damaged_quantity',
        'unit_penalty_charge',
        'description',
    ];

    protected function casts(): array
    {
        return [
            'total_quantity' => 'integer',
            'available_quantity' => 'integer',
            'allocated_quantity' => 'integer',
            'damaged_quantity' => 'integer',
            'unit_penalty_charge' => 'decimal:2',
        ];
    }

    /**
     * Get or create standard warehouse records
     */
    public static function getWarehouseStock(string $itemType): self
    {
        $code = strtoupper($itemType);
        $name = ucfirst($itemType);

        return static::firstOrCreate(
            ['item_code' => $code],
            [
                'item_name' => $name,
                'total_quantity' => 5000,
                'available_quantity' => 5000,
                'allocated_quantity' => 0,
                'damaged_quantity' => 0,
                'unit_penalty_charge' => $itemType === self::ITEM_MATTRESS ? 1200.00 : 400.00,
                'description' => "Standard Woldia University dormitory {$itemType} inventory.",
            ]
        );
    }
}
