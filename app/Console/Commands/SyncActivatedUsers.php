<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;
use App\Models\User;

class SyncActivatedUsers extends Command
{
    protected $signature = 'sync:activated-users';
    protected $description = 'Sync activated users from the central server';

    public function handle(): void
    {
        $this->info('Fetching activated users from server...');

        $branchId = config('services.client.branch_id');

        $response = Http::withToken(config('services.server.token'))
            ->get(config('services.server.url') . '/api/sync/users', [
                'branch_code' => config('services.client.branch_id'),
            ]);

        if (!$response->successful()) {
            $this->error('Failed to fetch data: ' . $response->status());
            return;
        }

        $users = $response->json();

        foreach ($users as $data) {
            User::updateOrCreate(
                ['email' => $data['email']],
                [
                    'name' => $data['name'],
                    'email' => $data['email'],
                    'branch_id' => $data['branch_id'],
                    'branch_code' => $data['branch_code'] ?? null,
                    'role' => $data['role'],
                    'is_active' => $data['is_active'],
                    'password' => $data['password'],
                ]
            );
        }

        $this->info(count($users) . ' users synced successfully.');
    }
}
