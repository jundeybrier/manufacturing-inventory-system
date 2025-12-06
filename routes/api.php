<?php
use Illuminate\Support\Facades\Route;

Route::get('/stage-inventory/{item}/{stage}', function ($item, $stage) {
    return \App\Models\StageInventory::where('inventory_item_id', $item)
        ->where('stage_id', $stage)
        ->firstOrFail();
});
