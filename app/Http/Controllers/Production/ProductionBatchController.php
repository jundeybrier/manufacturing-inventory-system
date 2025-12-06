<?php

namespace App\Http\Controllers\Production;

use App\Http\Controllers\Controller;
use App\Models\ProductionBatch;

class ProductionBatchController extends Controller
{
    public function index()
    {
        $batches = ProductionBatch::with('product')
            ->orderBy('batch_date', 'desc')
            ->paginate(20);

        return view('production.index', compact('batches'));
    }

    public function show(ProductionBatch $batch)
    {
        $batch->load(['product', 'items.material', 'outputs']);

        return view('production.show', compact('batch'));
    }
}
