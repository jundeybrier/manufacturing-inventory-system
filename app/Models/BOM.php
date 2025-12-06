<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class BOM extends Model
{
    use HasFactory;

    protected $table = 'boms';

    protected $fillable = [
        'finished_item_id',
        'raw_item_id',
        'required_qty_per_unit',
    ];

    protected $casts = [
        'required_qty_per_unit' => 'decimal:3',
    ];

    public function finishedItem()
    {
        return $this->belongsTo(InventoryItem::class, 'finished_item_id');
    }

    public function rawItem()
    {
        return $this->belongsTo(InventoryItem::class, 'raw_item_id');
    }
}
