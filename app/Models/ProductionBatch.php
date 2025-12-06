<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ProductionBatch extends Model
{
    protected $fillable = [
        'batch_date',
        'inventory_item_id',
        'planned_quantity',
        'actual_output',
        'status',
    ];

    public function product()
    {
        return $this->belongsTo(InventoryItem::class, 'inventory_item_id');
    }

    public function item()
    {
        return $this->belongsTo(\App\Models\InventoryItem::class, 'inventory_item_id');
    }

    public function items()
    {
        return $this->hasMany(ProductionBatchItem::class);
    }

    public function outputs()
    {
        return $this->hasMany(ProductionOutput::class);
    }
}
