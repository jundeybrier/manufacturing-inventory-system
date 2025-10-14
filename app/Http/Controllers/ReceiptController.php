<?php

namespace App\Http\Controllers;

use App\Models\Transaction;

class ReceiptController extends Controller
{
    public function show(Transaction $transaction)
    {
        $transaction->load('details');

        return view('receipts.print', [
            'transaction' => $transaction,
        ]);
    }
}
