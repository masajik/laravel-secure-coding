<?php

namespace App\Http\Middleware;

use Closure;
use App\Models\User;
use Firebase\JWT\JWT;
use Firebase\JWT\Key;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Response;

class AuthJWT
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next)
    {
        // mendapatkan token dari request
        $token = $request->bearerToken();
        //tidak ada token pada request, autentikasi gagal
        if(!$token){
            $error['message'] = 'Authentication failed!';
            return response()->json($error,401);
        }
        // dekripsi token yang dikirimkan user
        try {
            // generate Key
            $tokenKey = new Key(env('JWT_SECRET_KEY'), env('JWT_ALGORITHM'));
            // decode token
            $data = JWT::decode($token, $tokenKey);
            // ambil data user, dan set sebagai terautentikasi
            $activeUser = User::find($data->sub);
            Auth::SetUser($activeUser);
        // jika token tidak valid, berikan error response dan simpan log
        } catch (\Throwable $th) {
            Log::error('JWT Auth: ' . $th->getMessage());
            $error['message'] = 'Authentication failed!';
            return response()->json($error, 401);
        }
        // autentikasi berhasil, lanjutkan request
        return $next($request);
    }
}
