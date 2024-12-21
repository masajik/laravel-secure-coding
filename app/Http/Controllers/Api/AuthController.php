<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Firebase\JWT\JWT;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class AuthController extends Controller
{
    // Registrasi pengguna baru
    public function register(Request $request)
    {
        // menghapus spasi pada nama untuk validasi input
        $request->merge(['trimmed_name' => str_replace(' ', '', $request->name)]);
        // validasi input pada request
        try{
            $request->validate(
                // menentukan aturan validasi input
                [
                    'trimmed_name' => 'required|string|max:255',// alfabet maksimal 225 karakter
                    'email' => 'required|email|max:255|unique:users',// format email sesuai
                    'password' => 'required|string|min:8|confirmed',// minimal 8 karakter, dikonfirmasi
                ]
            );
        } catch (\throwable $th){
            Log::info($th);
            // jika validasi gagal, beri respon berupa error message
            $error['message'] = 'Sorry, we can\'t process your request.';
            return response()->json($error, 401);
        }
        // menyiapkan password yang dienkripsi dengan Bcrypt
        // input password divalidasi dengan rule 'password'
        $encryptedPassword = password_hash($request->password, PASSWORD_BCRYPT);
        // Menyimpan data registrasi pengguna ke dalam tabel users dengan eloquent ORM
        $user = User::create([
            'name' => $request->name,// divalidasi dengan rule 'trimmed_name'
            'email' => $request->email,// divalidasi dengan rule 'email'
            'password' => $encryptedPassword,// Menyimpan password terenkripsi Bcrypt
        ]);
        // mempersiapkan payload token JWT
        $tokenPayload = [
            'sub' => $user->id,
            'email' => $user->email,
            'exp' => time() + env('JWT_EXPIRY_TIME')
        ];
        
        // membuat token JWT
        $token = JWT::encode($tokenPayload, env('JWT_SECRET_KEY'), env('JWT_ALGORITHM'));
        return response()->json([
            'access_token' => $token,
            'token_type' => 'Bearer',
        ]);

    }

    // Login pengguna
    public function login(Request $request)
    {
        // validasi input dari user
        $request->validate([
            'email' => 'required|email',
            'password' => 'required',
        ]);
        // autentikasi pengguna menggunakan fitur yang disediakan laravel
        $authenticated = Auth::attempt([
            'email' => $request->email,
            'password' => $request->password
        ]);
        // error response ketika autentikasi user gagal
        if(!$authenticated){
            $error['message'] = 'Your email is not registered or password is incorrect!';
            return response()->json($error, 400);
        }
        // mempersiapkan payload token JWT
        $user = Auth::user();
        $tokenPayload = [
            'sub' => $user->id,
            'email' => $user->email,
            'exp' => time() + env('JWT_EXPIRY_TIME')
        ];
        // membuat token JWT
        $token = JWT::encode($tokenPayload, env('JWT_SECRET_KEY'), env('JWT_ALGORITHM'));
        return response()->json([
            'access_token' => $token,
            'token_type' => 'Bearer',
        ]);
    }

    // Logout pengguna
    public function logout()
    {
        // menggunakan stateless API, logout ditangani pada sisi frontend
        return response()->json(['message' => 'Logged out successfully'], 200);
    }
}
