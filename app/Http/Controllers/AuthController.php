<?php
namespace App\Http\Controllers;

use Laravel\Socialite\Facades\Socialite;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Student;
use Illuminate\Support\Facades\Log;
use Exception;

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

    public function redirectToGoogle()
    {
        return Socialite::driver('google')
            ->with([
                'hd' => 'dorsu.edu.ph',
                'prompt' => 'select_account consent',
                'access_type' => 'offline',
                'include_granted_scopes' => 'true'
            ])
            ->redirect();
    }

    public function handleGoogleCallback()
    {
        try {
            $googleUser = Socialite::driver('google')->user();
            
            Log::info('Google user data received:', [
                'email' => $googleUser->email
            ]);

            // Check email domain
            $emailDomain = explode('@', $googleUser->email)[1];
            if ($emailDomain !== 'dorsu.edu.ph') {
                return redirect()->route('login')
                    ->withErrors(['login' => 'Please use your DOrSU student email address.']);
            }

            // Debug database query
            $student = Student::where('school_email', $googleUser->email)->first();
            
            Log::info('Database query result:', [
                'email_searched' => $googleUser->email,
                'student_found' => $student ? 'yes' : 'no',
                'student_status' => $student ? $student->status : 'n/a'
            ]);

            if (!$student) {
                return redirect()->route('login')
                    ->withErrors(['login' => 'No student account found with email: ' . $googleUser->email]);
            }

            if ($student->status !== 'active') {
                return redirect()->route('login')
                    ->withErrors(['login' => 'Your account is not active.']);
            }

            Auth::guard('student')->login($student);
            session(['user_role' => 'student']);
            
            return redirect()->route('student-home');

        } catch (\Exception $e) {
            Log::error('Login error:', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            return redirect()->route('login')
                ->withErrors(['login' => 'Login failed: ' . $e->getMessage()]);
        }
    }
}
