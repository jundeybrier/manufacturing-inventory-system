<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class InventoryItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'code',
        'category',
        'unit',
        'is_producible',
        'is_repairable',
    ];

    protected $casts = [
        'is_producible' => 'boolean',
        'is_repairable' => 'boolean',
    ];

    // --- Relationships

    public function stageInventories()
    {
        return $this->hasMany(StageInventory::class);
    }

    public function stageMovements()
    {
        return $this->hasMany(StageMovement::class);
    }

    public function boms()
    {
        return $this->hasMany(BOM::class, 'finished_item_id');
    }

    public function usedInBoms()
    {
        return $this->hasMany(BOM::class, 'raw_item_id');
    }

    public function productionOrders()
    {
        return $this->hasMany(ProductionOrder::class);
    }
}
