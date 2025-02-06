<?php

namespace App\Http\Middleware;

use Closure;
use Auth;

class DivisionMaintenance
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

        //Division Record
        if($request->is('api/division/index')){
            if($user->can('division-list')){
                return $next($request); 
            }
        }

        //Division Create
        if($request->is('api/division/create') || $request->is('api/division/store')){
            if($user->can('division-create')){
                return $next($request); 
            }
        }

        //Division Edit
        if($request->is('api/division/edit/*') || $request->is('api/division/update/*')){
            if($user->can('division-edit')){
                return $next($request); 
            }
        }

        //Division Delete
        if($request->is('api/division/delete')){
            if($user->can('division-delete')){
                return $next($request); 
            }
        }

        return abort(401, 'Unauthorized');
    }
}
