<?php

namespace App\Http\Middleware;

use Closure;
use Auth;

class BranchMaintenance
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
        if($request->is('api/branch/index')){
            if($user->can('branch-list')){
                return $next($request); 
            }
        }

        //Brand Create
        if($request->is('api/branch/create') || $request->is('api/branch/store')){
            if($user->can('branch-create')){
                return $next($request); 
            }
        }

        //Brand Edit
        if($request->is('api/branch/edit/*') || $request->is('api/branch/update/*')){
            if($user->can('branch-edit')){
                return $next($request); 
            }
        }

        //Brand Delete
        if($request->is('api/branch/delete')){
            if($user->can('branch-delete')){
                return $next($request); 
            }
        }

        return abort(401, 'Unauthorized');
    }
}
