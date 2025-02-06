<?php

namespace App\Http\Middleware;

use Closure;
use Auth;

class AccessModuleMaintenance
{
    /**
     * Handle an incoming request.
     *brand
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        $user = Auth::user();

        //Access Module Record
        if($request->is('api/access_module/index')){
            if($user->can('access-module-list')){
                return $next($request); 
            }
        }

        //Access Module Create
        if($request->is('api/access_module/create') || $request->is('api/access_module/store')){
            if($user->can('access-module-create')){
                return $next($request); 
            }
        }

        //Access Module Edit
        if($request->is('api/access_module/edit/*') || $request->is('api/access_module/update/*')){
            if($user->can('access-module-edit')){
                return $next($request); 
            }
        }

        //Access Module Delete
        if($request->is('api/access_module/delete')){
            if($user->can('access-module-delete')){
                return $next($request); 
            }
        }

        return abort(401, 'Unauthorized');
    }
}
