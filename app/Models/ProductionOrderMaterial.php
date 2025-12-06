<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class ProductionOrderMaterial extends Model
{
    use HasFactory;

    protected $fillable = [
        'production_order_id',
        'inventory_item_id',
        'planned_qty',
        'actual_qty',
    ];

    protected $casts = [
        'planned_qty' => 'decimal:3',
        'actual_qty' => 'decimal:3',
    ];

    public function productionOrder()
    {
        return $this->belongsTo(ProductionOrder::class);
    }

    public function item()
    {
        return $this->belongsTo(InventoryItem::class, 'inventory_item_id');
    }
}
