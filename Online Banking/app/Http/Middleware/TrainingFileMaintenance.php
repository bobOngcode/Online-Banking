<?php

namespace App\Http\Middleware;

use Auth;
use Closure;

class TrainingFileMaintenance
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

        //File Record
        if($request->is('api/training/files') || $request->is('api/training/user_files')){
            if($user->can('file-list')){
                return $next($request); 
            }
        }

        //File Create
        if($request->is('api/training/file/upload')){
            if($user->can('file-create')){
                return $next($request); 
            }
        }

        //File Edit
        if($request->is('api/training/file/edit/*') || $request->is('api/training/file/update/*')){
            if($user->can('file-edit')){
                return $next($request); 
            }
        }

        //File Delete
        if($request->is('api/training/file/delete')){
            if($user->can('file-delete')){
                return $next($request); 
            }
        }

        //File Download
        if($request->is('api/training/file/download')){
            
            return $next($request); 
            
        }

        return abort(401, 'Unauthorized');
    }
}
