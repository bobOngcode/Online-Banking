<?php

namespace App\Http\Middleware;

use Closure;
use Auth;

class EmployeeAttlogMaintenance
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

        //Employee Atlog Record
        if($request->is('api/employee_attlog/index') || $request->is('api/employee_attlog/list/view')){
            if($user->can('employee-attlog-list')){
                return $next($request); 
            }
        }

        //Employee Atlog Create
        if($request->is('api/employee_attlog/create') || $request->is('api/employee_attlog/store')){
            if($user->can('employee-attlog-create')){
                return $next($request); 
            }
        }

        //Employee Atlog Edit
        if($request->is('api/employee_attlog/edit/*') || $request->is('api/employee_attlog/update/*')){
            if($user->can('employee-attlog-edit')){
                return $next($request); 
            }
        }

        //Employee Atlog Delete
        if($request->is('api/employee_attlog/delete')){
            if($user->can('employee-attlog-delete')){
                return $next($request); 
            }
        }

        //Employee Atlog Import Data
        if($request->is('api/employee_attlog/import_attlog/*')){
            if($user->can('employee-attlog-import')){
                return $next($request); 
            }
        }

        //Employee Atlog Export Data
        if($request->is('api/employee_attlog/export_attlog') || $request->is('api/employee_attlog/file/download')){
            if($user->can('employee-attlog-export')){
                return $next($request); 
            }
            return $next($request); 
        }

        //Employee Atlog Delete All
        if($request->is('api/employee_attlog/delete')){
            if($user->can('employee-attlog-clear-list')){
                return $next($request); 
            }
        }

        //Employee Atlog File Download
        // if($request->is('api/employee_attlog/file/download')){
            
        //     return $next($request); 
            
        // }
        

        return abort(401, 'Unauthorized');
    }
}
