<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ProductionOutput extends Model
{
    protected $fillable = [
        'production_batch_id',
        'quantity',
        'to_stage_id',
    ];

    public function batch()
    {
        return $this->belongsTo(ProductionBatch::class, 'production_batch_id');
    }

    public function stage()
    {
        return $this->belongsTo(Stage::class, 'to_stage_id');
    }
}
