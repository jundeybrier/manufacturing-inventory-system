<?php

namespace App\Livewire;

use App\Models\Office;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Livewire\Component;
use Illuminate\Support\Facades\Http;

class SyncStatus extends Component
{
    public $status = [];
    public $log = [];
    public $isSyncing = false;

    public function mount()
    {
        if (config('app.mode') !== 'client') {
            abort(403, 'This feature is only available in Client Mode.');
        }

        $this->status = [
            'last_sync' => 'Never',
            'office_count' => Office::count(),
            'user_count' => User::count(),
            'server_connection' => 'Idle',
        ];
    }

    private function logMessage($message)
    {
        $this->log[] = now()->format('H:i:s') . ' — ' . $message;
        $this->dispatch('$refresh'); // Force UI update immediately
    }

    public function syncNow()
    {
        $this->isSyncing = true;
        $this->log = [];

        try {
            $this->logMessage('Starting sync process…');

            $url = rtrim(config('services.server.url'), '/') . '/api/sync';
            $this->logMessage("Connecting to server: {$url}");

            $response = Http::withToken(config('services.server.token'))
                ->timeout(8)
                ->post($url, [
                    'uuid' => config('app.site_code'),
                ]);

            if (! $response->successful()) {
                $this->logMessage("Server returned error HTTP {$response->status()}.");
                $this->status['server_connection'] = 'Failed';
                return;
            }

            $this->logMessage('Server connection OK.');
            $payload = $response->json();

            $offices = $payload['records']['offices'] ?? [];
            $users   = $payload['records']['users'] ?? [];

            DB::transaction(function () use ($offices, $users) {

                $this->logMessage("Syncing " . count($offices) . " office(s)…");
                foreach ($offices as $o) {
                    Office::updateOrCreate(['id' => $o['id'],'uuid' => $o['uuid']], [
                        'name' => $o['name'],
                        'location' => $o['location'] ?? null,
                        'created_at' => $o['created_at'] ?? now(),
                        'updated_at' => $o['updated_at'] ?? now(),
                    ]);

                    $this->logMessage("→ Office: {$o['name']} synced.");
                }

                $this->logMessage("Syncing " . count($users) . " user(s)…");
                foreach ($users as $u) {
                    User::updateOrCreate(['id' => $u['id']], [
                        'name' => $u['name'],
                        'password' => $u['password'],
                        'email' => $u['email'],
                        'office_id' => $u['office_id'],
                        'created_at' => $u['created_at'] ?? now(),
                        'updated_at' => $u['updated_at'] ?? now(),
                    ]);

                    $this->logMessage("→ User: {$u['name']} synced.");
                }
            });

            $this->logMessage('Sync completed successfully.');

            $this->status = [
                'server_connection' => 'OK',
                'last_sync' => now()->toDateTimeString(),
                'office_count' => Office::count(),
                'user_count' => User::count(),
                'message' => "Sync completed successfully.",
            ];

        } catch (\Throwable $e) {
            $this->logMessage("ERROR: " . $e->getMessage());
            $this->status['server_connection'] = 'Error';
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
