<?php

namespace App\Http\Controllers\Api;

use Symfony\Component\HttpFoundation\Response;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\{Office, User, Account, Product, Service, FeeComponent, ProductService};
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class SyncController extends Controller
{
    public function sync(Request $request)
    {
        // Step 1: Validate Bearer Token
        $expectedToken = config('services.server.token');
        if ($request->bearerToken() !== $expectedToken) {
            return response()->json([
                'status' => 'error',
                'message' => 'Unauthorized: Invalid or missing token.',
            ], Response::HTTP_UNAUTHORIZED);
        }

        // Step 2: Validate UUID input
        $uuid = $request->input('uuid');
        if (! $uuid) {
            return response()->json([
                'status' => 'error',
                'message' => 'Missing required parameter: uuid.',
            ], Response::HTTP_BAD_REQUEST);
        }

        // Step 3: Locate Office
        $office = Office::where('uuid', $uuid)->first();
        if (! $office) {
            return response()->json([
                'status' => 'error',
                'message' => 'Office not found for provided UUID.',
            ], Response::HTTP_NOT_FOUND);
        }

        // Step 4: Data retrieval

        $offices = Office::select(
            'id','uuid','name','location','created_at','updated_at'
        )->get();

        $users = User::where('office_id', $office->id)
            ->select('id','uuid','password','name','email','office_id','created_at','updated_at')
            ->get();

        $accounts = Account::select(
            'id','uuid','office_id','name','code','description','is_active','created_at','updated_at'
        )->get();

        $products = Product::select(
            'id','uuid','name','description','is_active','created_at','updated_at','deleted_at'
        )->get();

        $services = Service::select(
            'id','uuid','office_id','name','type','description','is_active','created_at','updated_at'
        )->where('office_id', $office->id)->get();

        $feeComponents = FeeComponent::select(
            'id','uuid','service_id','account_id','office_id','name','base_amount','is_variable','currency','is_active','created_at','updated_at'
        )->where('office_id', $office->id)->get();

        $productServices = ProductService::select(
            'id','uuid','product_id','service_id','created_at','updated_at','deleted_at'
        )->whereIn('service_id', $services->pluck('id'))->get();


        // NEW: Roles & Permissions
        $permissions = Permission::select('name')->get();
        $roles = Role::select('name')->get();

        // NEW: User ↔ Role Assignments
        $userRoles = [];
        foreach ($users as $u) {
            $uRoles = $u->roles()->pluck('name');
            $userRoles[] = [
                'user_uuid' => $u->uuid,
                'roles' => $uRoles,
            ];
        }

        return response()->json([
            'status' => 'ok',
            'message' => 'Sync completed successfully.',
            'timestamp' => now()->toDateTimeString(),

            'records' => [
                'offices'          => $offices,
                'users'            => $users,
                'accounts'         => $accounts,
                'products'         => $products,
                'services'         => $services,
                'fee_components'   => $feeComponents,
                'product_services' => $productServices,
                'permissions'      => $permissions,
                'roles'            => $roles,
                'user_roles'       => $userRoles,
            ],

            'counts' => [
                'offices'          => $offices->count(),
                'users'            => $users->count(),
                'accounts'         => $accounts->count(),
                'products'         => $products->count(),
                'services'         => $services->count(),
                'fee_components'   => $feeComponents->count(),
                'product_services' => $productServices->count(),
                'permissions'      => $permissions->count(),
                'roles'            => $roles->count(),
                'user_roles'       => count($userRoles),
            ],
        ]);
    }
}
