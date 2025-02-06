<?php

namespace App\Http\Middleware;

use Closure;
use Auth;

class ProductMaintenance
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
        $api = 'api/product/';

        //Product Record
        if($request->is($api . 'index') || $request->is($api . 'list/view') || $request->is($api . 'scanned_products')){
            if($user->can('product-list')){
                return $next($request); 
            }
        }

        //Product Create
        if($request->is($api . 'create') || 
           $request->is($api . 'store') || 
           $request->is($api . 'search_brand') ||
           $request->is($api . 'search_model') ||
           $request->is($api . 'search_category') || 
           $request->is($api . 'search_serial')
        ){
            if($user->can('product-create')){
                return $next($request); 
            }
        }

        //Product Edit
        if($request->is($api . 'edit/*') || $request->is($api . 'update/*')){
            if($user->can('product-edit')){
                return $next($request); 
            }
        }

        //Product Delete
        if($request->is($api . 'delete')){
            if($user->can('product-delete') || $user->can('product-clear-list')){
                return $next($request); 
            }
        }

        //Product import
        if($request->is($api . 'import/*')){
            if($user->can('product-import')){
                return $next($request); 
            }
        }

        //Product export
        if($request->is($api . 'export') || $request->is($api . 'export_merged_template')){
            if($user->can('product-export')){
                return $next($request); 
            }
            return $next($request); 
        }
        
        // Product template download
        if($request->is($api . 'template/download')){
            if($user->can('product-template-download')){
                return $next($request); 
            }
            return $next($request); 
        }
    

        // Product Serial Number Details
        if($request->is($api . 'serial_number_details')){
            if($user->can('serial-number-details')){
                return $next($request); 
            }
            return $next($request); 
        }

        // Sync Item Master Data
        if($request->is($api . 'sync_item_master_data')){
            if($user->can('sync-item-master-data')){
                return $next($request); 
            }
            return $next($request); 
        }

        // Inventory On Hand
        if($request->is($api . 'inventory_on_hand') ||
           $request->is($api . 'search_brand') ||
           $request->is($api . 'search_model') ||
           $request->is($api . 'search_category') ||
           $request->is($api . 'export_inventory_on_hand')
        ){
            if($user->can('inventory_on_hand')){
                return $next($request); 
            }
            return $next($request); 
        }

        return abort(401, 'Unauthorized');
    }
}
