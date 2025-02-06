<?php

namespace App\Http\Middleware;

use Closure;
use Auth;

class EmployeeKeyPerformanceMaintenance
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

        //Employee Master Data Key Performance Record
        if($request->is('api/employee_master_data/key_performance/index')){
            if($user->can('employee-master-data-list')){
                return $next($request); 
            }
        }

        //Employee Master Data Key Performance Create
        if($request->is('api/employee_master_data/key_performance/create') || $request->is('api/employee_master_data/key_performance/store')){
            if($user->can('employee-master-data-create')){
                return $next($request); 
            }
        }

        //Employee Master Data Key Performance Edit
        if($request->is('api/employee_master_data/key_performance/edit/*') || $request->is('api/employee_master_data/key_performance/update/*')){
            if($user->can('employee-master-data-edit')){
                return $next($request); 
            }
        }

        //Employee Master Data Key Performance Delete
        if($request->is('api/employee_master_data/key_performance/delete')){
            if($user->can('employee-master-data-delete')){
                return $next($request); 
            }
        }

        return abort(401, 'Unauthorized');
    }
}
