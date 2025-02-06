<?php

namespace App\Http\Middleware;

use Closure;
use Auth;

class ExpenseParticularMaintenance
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

        // Expense Particular Record
        if($request->is('api/expense_particular/index')){
            if($user->can('expense-particular-list')){
                return $next($request); 
            }
        }

        // Expense Particular Create
        if($request->is('api/expense_particular/create') || $request->is('api/expense_particular/store')){
            if($user->can('expense-particular-create')){
                return $next($request); 
            }
        }

        // Expense Particular Edit
        if($request->is('api/expense_particular/edit/*') || $request->is('api/expense_particular/update/*')){
            if($user->can('expense-particular-edit')){
                return $next($request); 
            }
        }

        // Expense Particular Delete
        if($request->is('api/expense_particular/delete')){
            if($user->can('expense-particular-delete')){
                return $next($request); 
            }
        }

        return abort(401, 'Unauthorized');
    }
}
