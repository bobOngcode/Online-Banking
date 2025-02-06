<?php

namespace App\Http\Middleware;

use Closure;
use Auth;

class ProductModelMaintenance
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

        // Product Model Record
        if($request->is('api/product_model/index')){
            if($user->can('product-model-list')){
                return $next($request); 
            }
        }

        // Product Model Create
        if($request->is('api/product_model/create') || $request->is('api/product_model/store')){
            if($user->can('product-model-create')){
                return $next($request); 
            }
        }

        // Product Model Edit
        if($request->is('api/product_model/edit/*') || $request->is('api/product_model/update/*')){
            if($user->can('product-model-edit')){
                return $next($request); 
            }
        }

        // Product Model Delete
        if($request->is('api/product_model/delete')){
            if($user->can('product-model-delete')){
                return $next($request); 
            }
        }

        return abort(401, 'Unauthorized');
    }
}
