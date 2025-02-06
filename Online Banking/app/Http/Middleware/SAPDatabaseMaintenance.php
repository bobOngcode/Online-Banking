<?php

namespace App\Http\Middleware;

use Closure;
use Auth;

class SAPDatabaseMaintenance
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

        //SAP Database Record
        if($request->is('api/sap_database/index')){
            if($user->can('sap-database-list')){
                return $next($request); 
            }
        }

        //SAP Database Create
        if($request->is('api/sap_database/create') || $request->is('api/sap_database/store')){
            if($user->can('sap-database-create')){
                return $next($request); 
            }
        }

        //SAP Database Edit
        if($request->is('api/sap_database/edit') || $request->is('api/sap_database/update/*')){
            if($user->can('sap-database-edit')){
                return $next($request); 
            }
        }

        //SAP Database Delete
        if($request->is('api/sap_database/delete')){
            if($user->can('sap-database-delete')){
                return $next($request); 
            }
        }

        return abort(401, 'Unauthorized');
    }
}
