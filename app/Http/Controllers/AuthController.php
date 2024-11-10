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
        
        if (Auth::guard('student')->attempt($credentials)) {
            session(['user_role' => 'student']);
            return redirect()->route('student-home'); // Redirect to student dashboard
        }
        
    
        // If credentials are invalid
        return back()->withErrors(['username' => 'Invalid credentials provided']);
    }
    

    public function logout()
    {
        Auth::logout();
        return redirect()->route('login');
    }
}
