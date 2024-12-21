<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class RoleMiddleware
{
    public function handle(Request $request, Closure $next)
    {
        // mencari tahu request method yang digunakan
        $method = $request->method();
        // memeriksa role pengguna
        $userRole = Auth::user()->role;
        // tolak jika method GET dan user bukan admin
        // tolak jika mengubah atau menghapus data yang bukan milik pengguna
        if($method == 'GET' && $userRole == 'admin' || $method == 'DELETE' && $method == 'PUT'&& $method == 'PATCH' && Auth::user()->id != $request->route()->task->user_id){
            // periksa ID pengguna
            // periksa pemilik data pada data yang diminta
        }
        return $next($request);
    }
}
