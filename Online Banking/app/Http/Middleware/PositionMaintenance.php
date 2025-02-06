<?php

namespace App\Http\Middleware;

use Closure;
use Auth;

class PositionMaintenance
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

        //Position Record
        if($request->is('api/position/index')){
            if($user->can('position-list')){
                return $next($request); 
            }
        }

        //Position Create
        if($request->is('api/position/create') || $request->is('api/position/store')){
            if($user->can('position-create')){
                return $next($request); 
            }
        }

        //Position Edit
        if($request->is('api/position/edit/*') || $request->is('api/position/update/*')){
            if($user->can('position-edit')){
                return $next($request); 
            }
        }

        //Position Delete
        if($request->is('api/position/delete')){
            if($user->can('position-delete')){
                return $next($request); 
            }
        }

        return abort(401, 'Unauthorized');
    }
}
