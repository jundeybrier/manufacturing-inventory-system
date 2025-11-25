<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\CashierSession;
use App\Models\SessionDenomination;
use App\Models\Transaction;
use App\Models\TransactionDetail;
use App\Models\Office;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Response;

class SyncTransactionsController extends Controller
{
    public function store(Request $request)
    {
        // Validate token
        $expectedToken = config('services.server.token');
        if ($request->bearerToken() !== $expectedToken) {
            return response()->json([
                'status' => 'error',
                'message' => 'Unauthorized'
            ], Response::HTTP_UNAUTHORIZED);
        }

        // Validate office
        $office = Office::where('uuid', $request->office_uuid)->first();
        if (! $office) {
            return response()->json([
                'status' => 'error',
                'message' => 'Office not found'
            ], Response::HTTP_NOT_FOUND);
        }

        $sessionData = $request->session;
        $denominations = $request->denominations ?? [];
        $transactions = $request->transactions ?? [];

        DB::transaction(function () use ($sessionData, $denominations, $transactions, $office) {

            // 1) Insert session (insert only, based on uuid)
            $session = CashierSession::firstOrCreate(
                ['uuid' => $sessionData['uuid']],
                [
                    'user_id' => $sessionData['user_id'],
                    'office_id' => $office->id,
                    'opened_at' => $sessionData['opened_at'],
                    'closed_at' => $sessionData['closed_at'],
                    'expected_total' => $sessionData['expected_total'],
                    'actual_total' => $sessionData['actual_total'],
                    'notes' => $sessionData['notes'] ?? null,
                ]
            );

            // 2) Insert denominations
            foreach ($denominations as $d) {
                SessionDenomination::firstOrCreate(
                    ['uuid' => $d['uuid']],
                    [
                        'session_id' => $session->id,
                        'denomination' => $d['denomination'],
                        'quantity' => $d['quantity'],
                        'total' => $d['total'],
                        'created_at' => $d['created_at'],
                        'updated_at' => $d['updated_at'],
                    ]
                );
            }

            // 3) Insert transactions + details
            foreach ($transactions as $t) {

                $transaction = Transaction::firstOrCreate(
                    ['uuid' => $t['uuid']],
                    [
                        'or_number' => $t['or_number'],
                        'user_id' => $t['user_id'],
                        'office_id' => $t['office_id'],
                        'session_id' => $session->id,
                        'firstname' => $t['firstname'],
                        'middlename' => $t['middlename'],
                        'lastname' => $t['lastname'],
                        'customer_name' => $t['customer_name'],
                        'remarks' => $t['remarks'],
                        'total_amount' => $t['total_amount'],
                        'currency' => $t['currency'],
                        'exchange_rate' => $t['exchange_rate'],
                        'status' => $t['status'],
                        'voided_by' => $t['voided_by'],
                        'voided_at' => $t['voided_at'],
                        'created_at' => $t['created_at'],
                        'updated_at' => $t['updated_at'],
                        'synced_at' => now(),
                    ]
                );

                // Insert details
                foreach ($t['details'] as $d) {
                    TransactionDetail::firstOrCreate(
                        ['uuid' => $d['uuid']],
                        [
                            'transaction_id' => $transaction->id,
                            'fee_component_id' => $d['fee_component_id'],
                            'service_id' => $d['service_id'],
                            'account_id' => $d['account_id'],
                            'description' => $d['description'],
                            'quantity' => $d['quantity'],
                            'amount' => $d['amount'],
                            'total' => $d['total'],
                            'currency' => $d['currency'],
                            'exchange_rate' => $d['exchange_rate'],
                            'created_at' => $d['created_at'],
                            'updated_at' => $d['updated_at'],
                            'synced_at' => now(),
                        ]
                    );
                }
            }
        });

        Log::info("✅ Sync successful for office {$office->name}");

        return response()->json([
            'status' => 'ok',
            'message' => 'Sync accepted and stored.',
            'timestamp' => now()->toDateTimeString(),
        ]);
    }
}
