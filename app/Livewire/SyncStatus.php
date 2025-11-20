<?php

namespace App\Livewire;

use App\Models\Office;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Livewire\Component;
use Illuminate\Support\Facades\Http;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

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
                ->timeout(12)
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

            $offices         = $payload['records']['offices'] ?? [];
            $users           = $payload['records']['users'] ?? [];
            $accounts        = $payload['records']['accounts'] ?? [];
            $products        = $payload['records']['products'] ?? [];
            $services        = $payload['records']['services'] ?? [];
            $feeComponents   = $payload['records']['fee_components'] ?? [];
            $productServices = $payload['records']['product_services'] ?? [];
            $permissions = $payload['records']['permissions'] ?? [];
            $roles = $payload['records']['roles'] ?? [];
            $userRoles = $payload['records']['user_roles'] ?? [];
            $rolePermissions = $payload['records']['role_permissions'] ?? [];

            DB::transaction(function () use (
                $offices,
                $users,
                $accounts,
                $products,
                $services,
                $feeComponents,
                $productServices,
                $permissions,
                $roles,
                $userRoles,
                $rolePermissions
            ) {

                // --- Offices ---
                $this->logMessage("Syncing " . count($offices) . " office(s)…");
                foreach ($offices as $o) {
                    Office::updateOrCreate(['uuid' => $o['uuid'],'id' => $o['id']], [
                        'name'       => $o['name'],
                        'location'   => $o['location'] ?? null,
                        'created_at' => $o['created_at'] ?? now(),
                        'updated_at' => $o['updated_at'] ?? now(),
                    ]);
                    $this->logMessage("→ Office: {$o['name']} synced.");
                }

                // --- Users ---
                $this->logMessage("Syncing " . count($users) . " user(s)…");
                foreach ($users as $u) {
                    User::updateOrCreate(['id' => $u['id']], [
                        'uuid'       => $u['uuid'],
                        'name'       => $u['name'],
                        'password'   => $u['password'],
                        'email'      => $u['email'],
                        'office_id'  => $u['office_id'],
                        // password intentionally NOT synced
                        'created_at' => $u['created_at'] ?? now(),
                        'updated_at' => $u['updated_at'] ?? now(),
                    ]);
                    $this->logMessage("→ User: {$u['name']} synced.");
                }
                // --- Permissions ---
                $this->logMessage("Syncing " . count($permissions) . " permission(s)…");

                foreach ($permissions as $perm) {
                    Permission::firstOrCreate(['name' => $perm['name']]);
                    $this->logMessage("→ Permission: {$perm['name']} synced.");
                }

                // --- Roles ---
                $this->logMessage("Syncing " . count($roles) . " role(s)…");

                foreach ($roles as $role) {
                    Role::firstOrCreate(['name' => $role['name']]);
                    $this->logMessage("→ Role: {$role['name']} synced.");
                }

                // Clear permission cache before assignment
                app(\Spatie\Permission\PermissionRegistrar::class)->forgetCachedPermissions();

                // --- Role Assignments ---
                $this->logMessage("Syncing " . count($userRoles) . " user ↔ role assignment(s)…");

                foreach ($userRoles as $ur) {
                    $localUser = User::where('uuid', $ur['user_uuid'])->first();

                    if (! $localUser) {
                        $this->logMessage("⚠ Missing user: {$ur['user_uuid']}");
                        continue;
                    }

                    $localUser->syncRoles($ur['roles']);
                    $this->logMessage("→ Roles updated for: {$localUser->name}");
                }

                foreach ($rolePermissions as $rp) {
                    $role = Role::find($rp['role_id']);
                    $permission = Permission::find($rp['permission_id']);

                    if ($role && $permission) {
                        $role->givePermissionTo($permission->name);
                        $this->logMessage("→ {$role->name} granted {$permission->name}");
                    }
                }


                // --- Accounts ---
                $this->logMessage("Syncing " . count($accounts) . " account(s)…");
                foreach ($accounts as $a) {
                    \App\Models\Account::updateOrCreate(['id' => $a['id'],'uuid' => $a['uuid']], [
                        'office_id'  => $a['office_id'],
                        'name'       => $a['name'],
                        'code'       => $a['code'],
                        'description'=> $a['description'],
                        'is_active'  => $a['is_active'],
                        'created_at' => $a['created_at'] ?? now(),
                        'updated_at' => $a['updated_at'] ?? now(),
                    ]);
                    $this->logMessage("→ Account: {$a['name']} synced.");
                }

                // --- Products (global) ---
                $this->logMessage("Syncing " . count($products) . " product(s)…");
                foreach ($products as $p) {
                    \App\Models\Product::updateOrCreate(['id' => $p['id'],'uuid' => $p['uuid']], [
                        'name'       => $p['name'],
                        'description'=> $p['description'],
                        'is_active'  => $p['is_active'],
                        'created_at' => $p['created_at'] ?? now(),
                        'updated_at' => $p['updated_at'] ?? now(),
                        'deleted_at' => $p['deleted_at'] ?? null,
                    ]);
                    $this->logMessage("→ Product: {$p['name']} synced.");
                }

                // --- Services (office scoped) ---
                $this->logMessage("Syncing " . count($services) . " service(s)…");
                foreach ($services as $s) {
                    \App\Models\Service::updateOrCreate(['id' => $s['id'],'uuid' => $s['uuid']], [
                        'office_id'  => $s['office_id'],
                        'name'       => $s['name'],
                        'type'       => $s['type'],
                        'description'=> $s['description'],
                        'is_active'  => $s['is_active'],
                        'created_at' => $s['created_at'] ?? now(),
                        'updated_at' => $s['updated_at'] ?? now(),
                    ]);
                    $this->logMessage("→ Service: {$s['name']} synced.");
                }

                // --- Fee Components (depends on accounts + services) ---
                $this->logMessage("Syncing " . count($feeComponents) . " fee component(s)…");
                foreach ($feeComponents as $fc) {
                    \App\Models\FeeComponent::updateOrCreate(['id' => $fc['id'],'uuid' => $fc['uuid']], [
                        'service_id' => $fc['service_id'],
                        'account_id' => $fc['account_id'],
                        'office_id'  => $fc['office_id'],
                        'name'       => $fc['name'],
                        'base_amount'=> $fc['base_amount'],
                        'is_variable'=> $fc['is_variable'],
                        'currency'   => $fc['currency'],
                        'is_active'  => $fc['is_active'],
                        'created_at' => $fc['created_at'] ?? now(),
                        'updated_at' => $fc['updated_at'] ?? now(),
                    ]);
                    $this->logMessage("→ Fee Component: {$fc['name']} synced.");
                }

                // --- Product ↔ Service Pivot ---
                $this->logMessage("Syncing " . count($productServices) . " product-service relation(s)…");
                foreach ($productServices as $ps) {
                    \App\Models\ProductService::updateOrCreate(['id' => $ps['id'],'uuid' => $ps['uuid']], [
                        'product_id' => $ps['product_id'],
                        'service_id' => $ps['service_id'],
                        'created_at' => $ps['created_at'] ?? now(),
                        'updated_at' => $ps['updated_at'] ?? now(),
                        'deleted_at' => $ps['deleted_at'] ?? null,
                    ]);
                    $this->logMessage("→ Link synced: Product {$ps['product_id']} ↔ Service {$ps['service_id']}");
                }
            });

            $this->logMessage('Sync completed successfully.');

            $this->status = [
                'server_connection' => 'OK',
                'last_sync'         => now()->toDateTimeString(),
                'office_count'      => Office::count(),
                'user_count'        => User::count(),
                'message'           => "Sync completed successfully.",
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
