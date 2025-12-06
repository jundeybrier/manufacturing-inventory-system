<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BillOfMaterial extends Model
{
    protected $fillable = [
        'inventory_item_id',
        'material_id',
        'quantity',
        'unit',
    ];

    // Finished good (plywood)
    public function product()
    {
        return $this->belongsTo(InventoryItem::class, 'inventory_item_id');
    }

    // Raw material (veneer, core, glue)
    public function material()
    {
        return $this->belongsTo(InventoryItem::class, 'material_id');
    }
}
