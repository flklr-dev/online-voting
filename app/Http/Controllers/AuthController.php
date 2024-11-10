<?php
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AuthController extends Controller
{
    public function showLoginForm()
    {
        return view('auth.login');
    }

    public function login(Request $request)
    {
        $credentials = $request->only('username', 'password');
    
        // Check admin login
        if (Auth::guard('admin')->attempt($credentials)) {
            session(['user_role' => 'admin']);
            return redirect()->route('home'); // Redirect to admin dashboard
        }
    
        // Check student login and ensure status is active
        $credentials['status'] = 'active'; // Only active students can log in
        if (Auth::guard('student')->attempt($credentials)) {
            session(['user_role' => 'student']);
            return redirect()->route('student-home'); // Redirect to student dashboard
        }
    
        // If credentials are invalid, set error with key 'login' for easy display
        return back()->withErrors(['login' => 'Invalid credentials! Please, try again.']);
    }
    
    public function logout()
    {
        Auth::logout();
        return redirect()->route('login');
    }
}
