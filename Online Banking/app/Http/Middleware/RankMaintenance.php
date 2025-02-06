<?php

namespace App\Http\Middleware;

use Closure;
use Auth;

class RankMaintenance
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

        //rank Record
        if($request->is('api/rank/index')){
            if($user->can('rank-list')){
                return $next($request); 
            }
        }

        //rank Create
        if($request->is('api/rank/create') || $request->is('api/rank/store')){
            if($user->can('rank-create')){
                return $next($request); 
            }
        }

        //rank Edit
        if($request->is('api/rank/edit/*') || $request->is('api/rank/update/*')){
            if($user->can('rank-edit')){
                return $next($request); 
            }
        }

        //rank Delete
        if($request->is('api/rank/delete')){
            if($user->can('rank-delete')){
                return $next($request); 
            }
        }

        return abort(401, 'Unauthorized');
    }
}
