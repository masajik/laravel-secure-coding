<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class EncryptionController extends Controller
{
    public function encryptData(Request $request)
    {
        // Data yang akan dienkripsi
        $data = $request->encryptData;

        // Enkripsi menggunakan AES-256-CBC dengan kunci lemah
        $cipher = openssl_encrypt($data, 'AES-256-CBC', env('ENCRYPTION_KEY'), 0, env('ENCRYPTION_IV'));

        return response()->json([
            'encrypted_data' => $cipher
        ]);
    }
}
