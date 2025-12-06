<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class StageInventory extends Model
{
    use HasFactory;

    protected $fillable = [
        'inventory_item_id',
        'stage_id',
        'current_qty',
    ];

    protected $casts = [
        'current_qty' => 'decimal:3',
    ];

    public static function getQty($itemId, $stageId): float
    {
        return (float) self::where('inventory_item_id', $itemId)
            ->where('stage_id', $stageId)
            ->value('current_qty') ?? 0;
    }

    /**
     * Add quantity to a stage.
     */
    public static function add($itemId, $stageId, $qty)
    {
        $record = self::firstOrCreate([
            'inventory_item_id' => $itemId,
            'stage_id'          => $stageId,
        ]);

        $record->current_qty += $qty;
        $record->save();

        return $record;
    }

    /**
     * Deduct quantity from a stage.
     */
    public static function deduct($itemId, $stageId, $qty)
    {
        $record = self::firstOrCreate([
            'inventory_item_id' => $itemId,
            'stage_id'          => $stageId,
        ]);

        $newQty = $record->current_qty - $qty;

        if ($newQty < 0) {
            $newQty = 0;
        }

        $record->current_qty = $newQty;
        $record->save();

        return $record;
    }

    public function item()
    {
        return $this->belongsTo(InventoryItem::class, 'inventory_item_id');
    }

    public function stage()
    {
        return $this->belongsTo(Stage::class);
    }
}
