<?php

namespace App\Http\Middleware;

use Closure;
use Auth;

class ProductCategoryMaintenance
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

        // Product Category Record
        if($request->is('api/product_category/index')){
            if($user->can('product-category-list')){
                return $next($request); 
            }
        }

        // Product Category Create
        if($request->is('api/product_category/create') || $request->is('api/product_category/store')){
            if($user->can('product-category-create')){
                return $next($request); 
            }
        }

        // Product Category Edit
        if($request->is('api/product_category/edit/*') || $request->is('api/product_category/update/*')){
            if($user->can('product-category-edit')){
                return $next($request); 
            }
        }

        // Product Category Delete
        if($request->is('api/product_category/delete')){
            if($user->can('product-category-delete')){
                return $next($request); 
            }
        }

        return abort(401, 'Unauthorized');
    }
}
