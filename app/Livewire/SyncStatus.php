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
            // 🔹 Build full API URL from .env
            $baseUrl = rtrim(config('services.server.url'), '/');
            $url = $baseUrl . '/api/sync';

            // 🔹 Perform the HTTP call using env settings
            $response = Http::withToken(config('services.server.token'))
                ->timeout(5)
                ->post($url, [
                    'site_code' => config('app.site_code', 'default'),
                ]);

            if ($response->successful()) {
                $data = $response->json();

                $offices = $data['records'] ?? [];

                DB::transaction(function () use ($offices) {
                    foreach ($offices as $office) {
                        Office::updateOrCreate(
                            ['uuid' => $office['uuid']],
                            [
                                'name' => $office['name'],
                                'location' => $office['location'] ?? null,
                                'created_at' => $office['created_at'] ?? now(),
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
            ->layout('components.layouts.auth.simple');
    }
}
