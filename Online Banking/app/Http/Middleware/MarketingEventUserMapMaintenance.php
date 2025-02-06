<?php

namespace App\Http\Middleware;

use Closure;
use Auth;

class MarketingEventUserMapMaintenance
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
        if($request->is('api/marketing_event_user_map/index')){
            if($user->can('marketing-event-list')){
                return $next($request); 
            }
        }

        //Marketing Event Create
        if($request->is('api/marketing_event_user_map/create') || $request->is('api/marketing_event_user_map/store')){
            if($user->can('marketing-event-create')){
                return $next($request); 
            }
        }

        //Marketing Event Edit
        if($request->is('api/marketing_event_user_map/edit/*') || 
           $request->is('api/marketing_event_user_map/update/*') ||
           $request->is('api/marketing_event_user_map/update_approver_per_level')
            ){
            if($user->can('marketing-event-edit')){
                return $next($request); 
            }
        }

        //Marketing Event Delete
        if($request->is('api/marketing_event_user_map/delete')){
            if($user->can('marketing-event-delete')){
                return $next($request); 
            }
        }

        return abort(401, 'Unauthorized');
    }
}
