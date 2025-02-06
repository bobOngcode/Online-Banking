<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Support\Facades\Auth;

class OnlineBankingMaintenance
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


        //Tactical Requisition Record
        if ($request->is('api/onlinebanking/index')) {
            if ($user->can('online-banking-list')) {
                return $next($request);
            }
        }


        //Tactical Requisition Create
        if ($request->is('api/onlinebanking/store')) {
            if ($user->can('online-banking-create')) {
                return $next($request);
            }
        }


        //Tactical Requisition Approve
        if ($request->is($request->is('api/onlinebanking/approve')) || $request->is($request->is('api/onlinebanking/disapprove')) || $request->is($request->is('api/onlinebanking/download'))) {
            if ($user->can('online-banking-approve') || $user->can('online-banking-disapprove')) {
                return $next($request);
            }
        }


        //Tactical Requisition delete
        if ($request->is($request->is('api/onlinebanking/delete'))) {
            if ($user->can('online-banking-delete')) {
                return $next($request);
            }
        }


        return abort(401, 'Unauthorized');
    }
}
