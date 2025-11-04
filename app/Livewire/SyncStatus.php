<?php

namespace App\Livewire;

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
            $baseUrl = rtrim(config('services.server.url'), '/');
            $endpoint = '/api/sync';
            $url = $baseUrl . $endpoint;

            $token = config('services.server.token');
            $verifySsl = config('services.server.verify_ssl', true);

            $http = Http::withToken($token);
            if (! $verifySsl) {
                $http = $http->withoutVerifying();
            }

            $response = $http->timeout(60)->post($url, [
                'site_code' => config('app.site_code', 'default'),
            ]);

            if ($response->successful()) {
                $data = $response->json();
                $this->status = [
                    'last_sync' => now()->toDateTimeString(),
                    'queued_records' => $data['queued_records'] ?? 0,
                    'failed_records' => $data['failed_records'] ?? 0,
                    'message' => 'Sync completed successfully!',
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
