<?php

namespace App\Http\Controllers\Api;

use Symfony\Component\HttpFoundation\Response;
use App\Http\Controllers\Controller;
use App\Models\Office;
use App\Models\User;
use App\Models\Account;
use App\Models\Product;
use App\Models\Service;
use App\Models\FeeComponent;
use App\Models\ProductService;
use Illuminate\Http\Request;

class SyncController extends Controller
{
    public function sync(Request $request)
    {
        // Step 1: Token validation
        $expectedToken = config('services.server.token');
        $providedToken = $request->bearerToken();

        if (! $expectedToken || $providedToken !== $expectedToken) {
            return response()->json([
                'status' => 'error',
                'message' => 'Unauthorized: Invalid or missing token.',
            ], Response::HTTP_UNAUTHORIZED);
        }

        // Step 2: Expect UUID from client
        $uuid = $request->input('uuid');

        if (! $uuid) {
            return response()->json([
                'status' => 'error',
                'message' => 'Missing required parameter: uuid.',
            ], Response::HTTP_BAD_REQUEST);
        }

        // Step 3: Find the office by UUID
        $office = Office::where('uuid', $uuid)->first();

        if (! $office) {
            return response()->json([
                'status' => 'error',
                'message' => 'Office not found for provided UUID.',
            ], Response::HTTP_NOT_FOUND);
        }

        // Step 4: Offices (all, same as client uses them)
        $offices = Office::select('id','uuid','name','location','created_at','updated_at')->get();

        // Step 5: Users (only those belonging to this office)
        $users = User::where('office_id', $office->id)
            ->select('id','uuid','password','name','email','office_id','created_at','updated_at')
            ->get();

        // Step 6: Accounts (office-scoped)
        $accounts = Account::select('uuid','office_id','name','code','description','is_active','created_at','updated_at')
            ->get();

        // Step 7: Products (global catalog)
        $products = Product::select('uuid','name','description','is_active','created_at','updated_at','deleted_at')
            ->get();

        // Step 8: Services (office-scoped)
        $services = Service::select('uuid','office_id','name','type','description','is_active','created_at','updated_at')
            ->get();

        // Step 9: Fee Components (depends on accounts + services)
        $feeComponents = FeeComponent::select('uuid','service_id','account_id','office_id','name','base_amount','is_variable','currency','is_active','created_at','updated_at')
            ->get();

        // Step 10: Product-Service mapping (office services + global products)
        $productServices = ProductService::select('uuid','product_id','service_id','created_at','updated_at','deleted_at')
            ->whereIn('service_id', $services->pluck('id'))
            ->get();

        return response()->json([
            'status' => 'ok',
            'message' => 'Sync completed successfully.',
            'timestamp' => now()->toDateTimeString(),

            'records' => [
                'offices' => $offices,
                'users' => $users,
                'accounts' => $accounts,
                'products' => $products,
                'services' => $services,
                'fee_components' => $feeComponents,
                'product_services' => $productServices,
            ],

            'counts' => [
                'offices' => $offices->count(),
                'users' => $users->count(),
                'accounts' => $accounts->count(),
                'products' => $products->count(),
                'services' => $services->count(),
                'fee_components' => $feeComponents->count(),
                'product_services' => $productServices->count(),
            ],
        ]);
    }
}
