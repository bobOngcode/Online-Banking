<?php

namespace App\Http\Middleware;

use Closure;
use Auth;

class AccessChartMaintenance
{
    /**
     * Handle an incoming request.
     *access-chart
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        $user = Auth::user();

        //Access Chart Record
        if($request->is('api/access_chart/index')){
            if($user->can('access-chart-list')){
                return $next($request); 
            }
        }

        //Access Chart Create
        if($request->is('api/access_chart/create') || $request->is('api/access_chart/store')){
            if($user->can('access-chart-create')){
                return $next($request); 
            }
        }

        //Access Chart Edit
        if($request->is('api/access_chart/edit/*') || $request->is('api/access_chart/update/*')){
            if($user->can('access-chart-edit')){
                return $next($request); 
            }
        }

        //Access Chart Delete
        if($request->is('api/access_chart/delete')){
            if($user->can('access-chart-delete')){
                return $next($request); 
            }
        }

        //Access Level
        if($request->is('api/access_chart/get_access_level')){
            return $next($request); 
        }

        //Access Level Update
        if($request->is('api/access_chart/update_access_level/*')){
            if($user->can('access-level-edit')){
                return $next($request); 
            }
        }

        return abort(401, 'Unauthorized');
    }
}
