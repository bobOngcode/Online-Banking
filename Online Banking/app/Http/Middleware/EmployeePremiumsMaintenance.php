<?php

namespace App\Http\Middleware;

use Closure;
use Auth;

class EmployeePremiumsMaintenance
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

        //Employee Premiums Record
        if($request->is('api/employee_premiums/index') || $request->is('api/employee_premiums/list/view')){
            if($user->can('employee-premiums-list')){
                return $next($request); 
            }
        }

        //Employee Premiums Create
        if($request->is('api/employee_premiums/create') || $request->is('api/employee_premiums/store')){
            if($user->can('employee-premiums-create')){
                return $next($request); 
            }
        }

        //Employee Premiums Edit
        if($request->is('api/employee_premiums/edit/*') || $request->is('api/employee_premiums/update/*')){
            if($user->can('employee-premiums-edit')){
                return $next($request); 
            }
        }

        //Employee Premiums Delete
        if($request->is('api/employee_premiums/delete')){
            if($user->can('employee-premiums-delete')){
                return $next($request); 
            }
        }

        //Employee Premiums Import Data
        if($request->is('api/employee_premiums/import_premiums/*')){
            if($user->can('employee-premiums-import')){
                return $next($request); 
            }
        }

        //Employee Premiums Export Data
        if($request->is('api/employee_premiums/export_premiums')){
            if($user->can('employee-premiums-export')){
                return $next($request); 
            }
        }

        //Employee Premiums Delete All
        if($request->is('api/employee_premiums/delete')){
            if($user->can('employee-premiums-clear-list')){
                return $next($request); 
            }
        }
        

        return abort(401, 'Unauthorized');
    }
}
