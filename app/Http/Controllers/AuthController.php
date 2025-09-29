<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;
use App\Models\User;

class AuthController extends Controller
{
    public function index()
    {
        return view('auth.index');
    }

    public function login(Request $request)
    {
        // Validasi input
        $validator = Validator::make($request->all(), [
            'name' => 'required',
            'password' => 'required',
        ], [
            'name.required' => 'Field Email atau Username wajib diisi.',
            'password.required' => 'Field Password wajib diisi.',
            'g-recaptcha-response' => 'required|captcha',
        ]);
    
        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }
    
        // Mencari user berdasarkan nama atau email
        $user = User::where('status', 'active')
        ->where(function ($query) use ($request) {
            $query->where('name', $request->name)
                  ->orWhere('email', $request->name);
        })
        ->first();

    
        // Jika user ditemukan dan password cocok
        if ($user && Hash::check($request->password, $user->password)) {
            // Login berhasil, arahkan ke halaman dashboard
            Auth::loginUsingId($user->id);
            return redirect()->intended('/dashboard')->with('toast_success', 'Berhasil login!');
        }
    
        // Jika login gagal
        return redirect('/')->with('toast_error', 'Username atau password salah!');
    }

    public function logout(Request $request)
    {
        // Log the user out
        Auth::logout();

        // Invalidate the session to ensure security
        $request->session()->invalidate();

        // Regenerate the session token
        $request->session()->regenerateToken();

        // Redirect to the login page with a success message
        return redirect('/')->with('toast_success', 'Berhasil Logout.');
    }
}
