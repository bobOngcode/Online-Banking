<?php

namespace App\Http\Middleware;

use Closure;
use Auth;

class DepartmentMaintenance
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

        //Department Record
        if($request->is('api/department/index')){
            if($user->can('department-list')){
                return $next($request); 
            }
        }

        //Department Create
        if($request->is('api/department/create') || $request->is('api/department/store')){
            if($user->can('department-create')){
                return $next($request); 
            }
        }

        //Department Edit
        if($request->is('api/department/edit/*') || $request->is('api/department/update/*')){
            if($user->can('department-edit')){
                return $next($request); 
            }
        }

        //Department Delete
        if($request->is('api/department/delete')){
            if($user->can('department-delete')){
                return $next($request); 
            }
        }

        return abort(401, 'Unauthorized');
    }
}
