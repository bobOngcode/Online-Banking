<?php

namespace App\Http\Middleware;

use Closure;
use Auth;

class BrandMaintenance
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

        //Brand Record
        if($request->is('api/brand/index')){
            if($user->can('brand-list')){
                return $next($request); 
            }
        }

        //Brand Create
        if($request->is('api/brand/create') || $request->is('api/brand/store')){
            if($user->can('brand-create')){
                return $next($request); 
            }
        }

        //Brand Edit
        if($request->is('api/brand/edit/*') || $request->is('api/brand/update/*')){
            if($user->can('brand-edit')){
                return $next($request); 
            }
        }

        //Brand Delete
        if($request->is('api/brand/delete')){
            if($user->can('brand-delete')){
                return $next($request); 
            }
        }

        return abort(401, 'Unauthorized');
    }
}
