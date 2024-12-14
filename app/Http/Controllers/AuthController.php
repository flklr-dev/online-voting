<?php
namespace App\Http\Controllers;

use Laravel\Socialite\Facades\Socialite;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Student;
use Illuminate\Support\Facades\Log;
use Exception;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Mail;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Cache;

class AuthController extends Controller
{
    public function showLoginForm()
    {
        return view('auth.login');
    }

    public function login(Request $request)
    {
        $credentials = $request->only('username', 'password');
    
        // Check admin login first
        if (Auth::guard('admin')->attempt($credentials)) {
            // Set session data for admin
            $this->setSessionData(Auth::guard('admin')->user(), 'admin');
            return redirect()->route('home');
        }
    
        // For student login, validate that username is a school email
        if (!str_ends_with($credentials['username'], '@dorsu.edu.ph')) {
            return back()->withErrors(['login' => 'Please use your DOrSU email address as username.']);
        }
    
        // Check student login and ensure status is active
        $credentials['status'] = 'active';
        if (Auth::guard('student')->attempt($credentials)) {
            $student = Auth::guard('student')->user();
            
            // Generate and store OTP
            $otp = Str::random(6);
            DB::table('otp_codes')->insert([
                'student_id' => $student->student_id,
                'code' => $otp,
                'expires_at' => Carbon::now()->addMinutes(5),
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ]);

            // Send OTP email
            Mail::raw("Your verification code is: $otp\nThis code will expire in 5 minutes.", function($message) use ($student) {
                $message->to($student->school_email)
                        ->subject('DOrSU Voting System - Verification Code');
            });

            // Store student ID in session for verification
            session(['pending_student_id' => $student->student_id]);
            
            return redirect()->route('show.otp.form');
        }
    
        return back()->withErrors(['login' => 'Invalid credentials! Please try again.']);
    }
    
    public function logout()
    {
        $user = null;
        $userType = null;

        if (Auth::guard('admin')->check()) {
            $user = Auth::guard('admin')->user();
            $userType = 'admin';
            Auth::guard('admin')->logout();
        } elseif (Auth::guard('student')->check()) {
            $user = Auth::guard('student')->user();
            $userType = 'student';
            Auth::guard('student')->logout();
        }

        if ($user) {
            // Clear the session token from cache
            $userSessionKey = "user_session_{$userType}_{$user->getAuthIdentifier()}";
            Cache::forget($userSessionKey);
        }

        // Clear all session data
        Session::flush();
        
        // Regenerate the session ID
        Session::regenerate(true);
        
        // Invalidate and regenerate the CSRF token
        request()->session()->regenerateToken();

        return redirect()->route('login')
            ->with('message', 'You have been successfully logged out.');
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

    public function showOtpForm()
    {
        if (!session('pending_student_id')) {
            return redirect()->route('login');
        }
        return view('auth.verify-otp');
    }

    public function verifyOtp(Request $request)
    {
        $studentId = session('pending_student_id');
        if (!$studentId) {
            return redirect()->route('login');
        }

        $otp = DB::table('otp_codes')
            ->where('student_id', $studentId)
            ->where('code', $request->otp)
            ->where('expires_at', '>', Carbon::now())
            ->first();

        if (!$otp) {
            return back()->withErrors(['otp' => 'Invalid or expired verification code']);
        }

        // Clear used OTP
        DB::table('otp_codes')->where('student_id', $studentId)->delete();

        // Log in the student and set session data
        $student = Student::find($studentId);
        Auth::guard('student')->login($student);
        $this->setSessionData($student, 'student');
        session()->forget('pending_student_id');

        return redirect()->route('student-home');
    }

    // Helper method to set session data
    private function setSessionData($user, $role)
    {
        // Set session start time and last activity
        Session::put('session_start', now());
        Session::put('last_activity', now());
        Session::put('user_role', $role);
        
        // Generate and store session token
        $sessionId = Session::getId();
        $userSessionKey = "user_session_{$role}_{$user->getAuthIdentifier()}";
        Cache::put($userSessionKey, $sessionId, now()->addMinutes(config('session.lifetime')));
    }
}