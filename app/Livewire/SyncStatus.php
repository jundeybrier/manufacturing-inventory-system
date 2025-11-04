<?php

namespace App\Livewire;

use App\Models\Office;
use Illuminate\Support\Facades\DB;
use Livewire\Component;
use Illuminate\Support\Facades\Http;

class SyncStatus extends Component
{
    public $status = [];
    public $isSyncing = false;

    public function mount()
    {
        $this->status = [
            'last_sync' => 'Never',
            'queued_records' => 0,
            'failed_records' => 0,
            'server_connection' => 'Idle',
        ];
    }

    public function syncNow()
    {
        $this->reset('status');
        $this->isSyncing = true;

        try {
            // Call the local API (self)
            $response = Http::timeout(30)->post(url('/api/sync'), [
                'site_code' => config('app.site_code', 'default'),
            ]);

            if ($response->successful()) {
                $data = $response->json();

                // Step 1: extract records
                $offices = $data['records'] ?? [];

                DB::transaction(function () use ($offices) {
                    foreach ($offices as $office) {
                        // Step 2: update or create (avoids duplicates)
                        Office::updateOrCreate(
                            ['code' => $office['code']],
                            [
                                'name' => $office['name'],
                                'address' => $office['address'] ?? null,
                                'updated_at' => $office['updated_at'] ?? now(),
                            ]
                        );
                    }
                });

                $this->status = [
                    'message' => "Synced {$data['count']} offices successfully.",
                    'timestamp' => now()->toDateTimeString(),
                ];
            } else {
                $this->status = [
                    'error' => 'Server returned ' . $response->status(),
                    'body' => $response->body(),
                ];
            }
        } catch (\Throwable $e) {
            $this->status = [
                'error' => $e->getMessage(),
            ];
        } finally {
            $this->isSyncing = false;
        }
    }

    public function render()
    {
        return view('livewire.sync-status')
            ->layout('components.layouts.standalone');
    }
}
