<?php

namespace App\Http\Controllers;

use App\Models\ReceiptLayout;
use App\Models\Transaction;

class ReceiptController extends Controller
{

    public function show(Transaction $transaction)
    {
        $transaction->load('details');

        $layout = auth()->user()->receiptLayout
            ?? new ReceiptLayout(config('defaults'));

        return view('receipts.print', [
            'transaction' => $transaction,
            'layout'      => $layout,
        ]);
    }
}
