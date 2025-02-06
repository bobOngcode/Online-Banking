<?php

namespace App\Http\Middleware;

use Closure;
use Auth;

class EmployeeMaintenance
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

        //Employee Record
        if($request->is('api/employee/index') || $request->is('api/employee/list/view')){
            if($user->can('employee-list')){
                return $next($request); 
            }
        }

        //Employee Create
        if($request->is('api/employee/create') || $request->is('api/employee/store')){
            if($user->can('employee-create')){
                return $next($request); 
            }
        }

        //Employee Edit
        if($request->is('api/employee/edit/*') || $request->is('api/employee/update/*')){
            if($user->can('employee-edit')){
                return $next($request); 
            }
        }

        //Employee Delete
        if($request->is('api/employee/delete')){
            if($user->can('employee-delete')){
                return $next($request); 
            }
        }

        //Employee Import Data
        if($request->is('api/employee/import_employee/*')){
            if($user->can('employee-list-import')){
                return $next($request); 
            }
        }

        //Employee Export Data
        if($request->is('api/employee/export_employee')){
            if($user->can('employee-list-export')){
                return $next($request); 
            }
        }

        //Employee Delete All
        if($request->is('api/employee/delete')){
            if($user->can('employee-clear-list')){
                return $next($request); 
            }
        }
        

        return abort(401, 'Unauthorized');
    }
}
