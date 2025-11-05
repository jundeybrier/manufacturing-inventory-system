<?php

namespace App\Jobs;

use App\Models\CashierSession;
use App\Models\Transaction;
use Illuminate\Bus\Queueable;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class SyncClosedSessionsJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function handle(): void
    {
        Log::info('🔄 SyncClosedSessionsJob started');

        $sessions = CashierSession::whereNotNull('closed_at')
            ->whereNull('synced_at')
            ->limit(3)
            ->get();

        Log::info("📦 Found {$sessions->count()} closed session(s) to sync.");

        if ($sessions->isEmpty()) {
            Log::info('✅ No sessions to sync. Job complete.');
            return;
        }

        foreach ($sessions as $session) {
            Log::info("➡️ Syncing Session UUID: {$session->uuid}");

            $transactions = Transaction::where('session_id', $session->id)
                ->whereNull('synced_at')
                ->with('details')
                ->get();

            Log::info("🧾 Found {$transactions->count()} transaction(s) in this session.");

            if ($transactions->isEmpty()) {
                Log::info("⚠️ No transactions found — marking session as synced.");
                $session->update(['synced_at' => now()]);
                continue;
            }

            $sessionDenominations = $session->denominations()
                ->select('uuid', 'denomination', 'quantity', 'total', 'created_at', 'updated_at')
                ->get();

            Log::info("💵 Found {$sessionDenominations->count()} denomination breakdown rows.");

            $payload = [
                'office_uuid' => config('app.site_code'),
                'session' => [
                    'uuid' => $session->uuid,
                    'user_id' => $session->user_id,
                    'opened_at' => $session->opened_at,
                    'closed_at' => $session->closed_at,
                    'expected_total' => $session->expected_total,
                    'actual_total' => $session->actual_total,
                    'notes' => $session->notes,
                ],
                'denominations' => $sessionDenominations,
                'transactions' => $transactions->map(function ($t) {
                    return [
                        'uuid' => $t->uuid,
                        'or_number' => $t->or_number,
                        'user_id' => $t->user_id,
                        'office_id' => $t->office_id,
                        'firstname' => $t->firstname,
                        'middlename' => $t->middlename,
                        'lastname' => $t->lastname,
                        'customer_name' => $t->customer_name,
                        'remarks' => $t->remarks,
                        'total_amount' => $t->total_amount,
                        'currency' => $t->currency,
                        'exchange_rate' => $t->exchange_rate,
                        'status' => $t->status,
                        'voided_by' => $t->voided_by,
                        'voided_at' => $t->voided_at,
                        'details' => $t->details->map(function ($d) {
                            return [
                                'uuid' => $d->uuid,
                                'fee_component_id' => $d->fee_component_id,
                                'service_id' => $d->service_id,
                                'account_id' => $d->account_id,
                                'description' => $d->description,
                                'quantity' => $d->quantity,
                                'amount' => $d->amount,
                                'total' => $d->total,
                                'currency' => $d->currency,
                                'exchange_rate' => $d->exchange_rate,
                            ];
                        }),
                    ];
                }),
            ];

            Log::info("🌐 Sending session to server...", [
                'url' => config('services.server.url') . '/api/sync/transactions',
                'payload_summary' => [
                    'transactions' => $transactions->count(),
                    'details' => $transactions->sum(fn($t) => $t->details->count()),
                    'denominations' => $sessionDenominations->count(),
                ]
            ]);

            try {
                $response = Http::withToken(config('services.server.token'))
                    ->post(config('services.server.url') . '/api/sync/transactions', $payload);

                if ($response->successful()) {
                    Log::info("✅ Server accepted sync. Updating local records.");

                    DB::transaction(function () use ($transactions, $session, $sessionDenominations) {
                        foreach ($transactions as $t) {
                            $t->update(['synced_at' => now()]);
                        }
                        foreach ($sessionDenominations as $d) {
                            $d->update(['synced_at' => now()]);
                        }
                        $session->update(['synced_at' => now()]);
                    });

                    Log::info("✅ Session {$session->uuid} marked as synced.");
                } else {
                    Log::error("❌ Server returned HTTP {$response->status()}", [
                        'response_body' => $response->body()
                    ]);
                }

            } catch (\Throwable $e) {
                Log::error("💥 Sync error: " . $e->getMessage(), [
                    'session_uuid' => $session->uuid,
                    'trace' => $e->getTraceAsString()
                ]);
            }
        }

        Log::info('🎉 SyncClosedSessionsJob finished.');
    }
}
