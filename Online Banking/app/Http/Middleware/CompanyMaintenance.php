<?php

namespace App\Http\Middleware;

use Closure;
use Auth;

class CompanyMaintenance
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

        //Company Record
        if($request->is('api/company/index')){
            if($user->can('company-list')){
                return $next($request); 
            }
        }

        //Company Create
        if($request->is('api/company/create') || $request->is('api/company/store')){
            if($user->can('company-create')){
                return $next($request); 
            }
        }

        //Company Edit
        if($request->is('api/company/edit/*') || $request->is('api/company/update/*')){
            if($user->can('company-edit')){
                return $next($request); 
            }
        }

        //Company Delete
        if($request->is('api/company/delete')){
            if($user->can('company-delete')){
                return $next($request); 
            }
        }

        return abort(401, 'Unauthorized');
    }
}
