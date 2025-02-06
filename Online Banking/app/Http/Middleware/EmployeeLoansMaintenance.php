<?php

namespace App\Http\Middleware;

use Closure;
use Auth;

class EmployeeLoansMaintenance
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

        //Employee Loans Record
        if($request->is('api/employee_loans/index') || $request->is('api/employee_loans/list/view')){
            if($user->can('employee-loans-list')){
                return $next($request); 
            }
        }

        //Employee Loans Create
        if($request->is('api/employee_loans/create') || $request->is('api/employee_loans/store')){
            if($user->can('employee-loans-create')){
                return $next($request); 
            }
        }

        //Employee Loans Edit
        if($request->is('api/employee_loans/edit/*') || $request->is('api/employee_loans/update/*')){
            if($user->can('employee-loans-edit')){
                return $next($request); 
            }
        }

        //Employee Loans Delete
        if($request->is('api/employee_loans/delete')){
            if($user->can('employee-loans-delete')){
                return $next($request); 
            }
        }

        //Employee Loans Import Data
        if($request->is('api/employee_loans/import_loans/*')){
            if($user->can('employee-loans-import')){
                return $next($request); 
            }
        }

        //Employee Loans Export Data
        if($request->is('api/employee_loans/export_loans')){
            if($user->can('employee-loans-export')){
                return $next($request); 
            }
        }

        //Employee Loans Delete All
        if($request->is('api/employee_loans/delete')){
            if($user->can('employee-loans-clear-list')){
                return $next($request); 
            }
        }
        

        return abort(401, 'Unauthorized');
    }
}
