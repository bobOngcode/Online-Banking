<?php

namespace App\Http\Middleware;

use Closure;
use Auth;

class InventoryReconciliationMaintenance
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        $user = Auth::user();
        $api = 'api/inventory_reconciliation';

        //Inventory Reconciliation Record
        if($request->is($api . '/index') || 
           $request->is($api . '/discrepancy/*') ||
           $request->is($api . '/breakdown') ||
           $request->is($api . '/export_discrepancy/*') ||
           $request->is($api . '/export_breakdown')
           ){
            if($user->can('inventory-recon-list')){
                return $next($request); 
            }
        }

        //Inventory Reconciliation Import
        if($request->is($api . '/import')){
            if($user->can('inventory-recon-create')){
                return $next($request); 
            }
        }

        //Inventory Reconciliation Sync From SAP
        if($request->is($api . '/sync') || $request->is($api . '/get_branch_whse')){
            if($user->can('inventory-recon-sync')){
                return $next($request); 
            }
        }

        //Product Reconcile for Branch user
        if($request->is($api . '/reconcile') || $request->is($api . '/unreconcile/list')){
            if($user->can('product-reconcile')){
                return $next($request); 
            }
        }

        //Inventory Reconciliation Delete
        if($request->is($api . '/delete')){
            if($user->can('inventory-recon-delete')){
                return $next($request); 
            }
        }

        return abort(401, 'Unauthorized');
    }
}
