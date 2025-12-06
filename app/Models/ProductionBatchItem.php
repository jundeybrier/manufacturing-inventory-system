<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ProductionBatchItem extends Model
{
    protected $fillable = [
        'production_batch_id',
        'material_id',
        'from_stage_id',
        'quantity_used',
        'unit',
    ];

    public function batch()
    {
        return $this->belongsTo(ProductionBatch::class, 'production_batch_id');
    }

    public function material()
    {
        return $this->belongsTo(InventoryItem::class, 'material_id');
    }

    public function fromStage()
    {
        return $this->belongsTo(Stage::class, 'from_stage_id');
    }
}
