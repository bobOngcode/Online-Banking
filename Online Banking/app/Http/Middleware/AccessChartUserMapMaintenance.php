<?php

namespace App\Http\Middleware;

use Closure;
use Auth;

class AccessChartUserMapMaintenance
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

        //Access Chart Record
        if($request->is('api/access_chart_user_map/index')){
            if($user->can('access-chart-list')){
                return $next($request); 
            }
        }

        //Access Chart Create
        if($request->is('api/access_chart_user_map/create') || $request->is('api/access_chart_user_map/store')){
            if($user->can('access-chart-create')){
                return $next($request); 
            }
        }

        //Access Chart Edit
        if($request->is('api/access_chart_user_map/edit/*') || $request->is('api/access_chart_user_map/update/*')){
            if($user->can('access-chart-edit')){
                return $next($request); 
            }
        }

        //Access Chart Delete
        if($request->is('api/access_chart_user_map/delete')){
            if($user->can('access-chart-delete')){
                return $next($request); 
            }
        }

        return abort(401, 'Unauthorized');
    }
}
