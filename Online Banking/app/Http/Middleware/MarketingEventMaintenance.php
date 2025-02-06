<?php

namespace App\Http\Middleware;

use Closure;
use Auth;

class MarketingEventMaintenance
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

        //Marketing Event Record
        if($request->is('api/marketing_event/index')){
            if($user->can('marketing-event-list')){
                return $next($request); 
            }
        }

        //Marketing Event Create
        if($request->is('api/marketing_event/create') || $request->is('api/marketing_event/store')){
            if($user->can('marketing-event-create')){
                return $next($request); 
            }
        }

        //Marketing Event Edit
        if($request->is('api/marketing_event/edit') || $request->is('api/marketing_event/update/*')){
            if($user->can('marketing-event-edit')){
                return $next($request); 
            }
        }

        //Marketing Event Delete
        if($request->is('api/marketing_event/delete')){
            if($user->can('marketing-event-delete')){
                return $next($request); 
            }
        }

        return abort(401, 'Unauthorized');
    }
}
