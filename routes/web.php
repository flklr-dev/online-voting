<?php
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\StudentController;
use App\Http\Controllers\ElectionController;
use App\Http\Controllers\PositionController;
use App\Http\Controllers\CandidateController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\AdminController;
use App\Http\Controllers\ResultController;
use App\Http\Controllers\StudentHomeController;
use App\Http\Controllers\VotingHistoryController;
use App\Http\Controllers\PartylistController;
use App\Http\Controllers\StudentResultController;
use App\Http\Controllers\VoteController;
use App\Http\Controllers\ActivityLogController;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Auth;

// Authentication routes
Route::get('/', [AuthController::class, 'showLoginForm'])->name('login');
Route::post('/login', [AuthController::class, 'login'])->name('login.submit');
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

// Google OAuth routes (for students only)
Route::get('auth/google', [AuthController::class, 'redirectToGoogle'])->name('google.login');
Route::get('auth/google/callback', [AuthController::class, 'handleGoogleCallback'])->name('google.callback');

// OTP verification routes (for students only)
Route::get('/verify-otp', [AuthController::class, 'showOtpForm'])->name('show.otp.form');
Route::post('/verify-otp', [AuthController::class, 'verifyOtp'])->name('verify.otp');

// Admin Routes
Route::middleware(['auth:admin'])->group(function () {
    Route::get('/home', [HomeController::class, 'index'])->name('home');
    Route::get('/admin/profile', [ProfileController::class, 'index'])->name('admin.profile'); // Admin profile
    // Admin management routes
    Route::resource('admins', AdminController::class);
    Route::resource('students', StudentController::class);
    Route::resource('elections', ElectionController::class);
    Route::post('/elections/{id}/update-status', [ElectionController::class, 'updateStatus'])->name('elections.update-status'); // New route
    Route::resource('positions', PositionController::class);
    Route::resource('candidates', CandidateController::class);
    Route::resource('results', ResultController::class)->only(['index', 'show']);
    Route::get('/voting-results', [HomeController::class, 'getVotingResults'])->name('voting.results');
    Route::get('/elections/{election_id}/positions', [HomeController::class, 'getPositionsByElection']);
    Route::get('/election/{electionId}/download-results', [ResultController::class, 'downloadResults'])->name('election.download');
    Route::post('/partylists', [PartylistController::class, 'store'])->name('partylists.store');
    Route::get('/get-eligible-students/{election_id}', [CandidateController::class, 'getEligibleStudents']);
    Route::delete('/partylists/{partylist}', [PartylistController::class, 'destroy'])->name('partylists.destroy');
    Route::get('/admin/voting-history', [VoteController::class, 'adminVotingHistory'])->name('admin.voting-history');
    Route::get('/activity-logs', [ActivityLogController::class, 'index'])->name('activity-logs.index');
});

// Student Routes
Route::middleware(['auth:student'])->group(function () {
    // Student Dashboard
    Route::get('/student-home', [StudentHomeController::class, 'index'])->name('student-home');
    Route::get('/ongoing-elections', [ElectionController::class, 'ongoingElections'])->name('ongoing-elections.index');
    Route::get('/vote/{election_id}', [ElectionController::class, 'voteInterface'])->name('vote.interface');
    Route::post('/vote/store', [ElectionController::class, 'storeVote'])->name('vote.store');
    Route::get('/voting-history', [VotingHistoryController::class, 'index'])->name('voting-history.index');
    Route::get('/profile', [ProfileController::class, 'index'])->name('profile.index');
    Route::put('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::get('/student/results', [StudentResultController::class, 'index'])->name('student-results.index');
    Route::get('/student/results/{election}', [StudentResultController::class, 'show'])->name('student-results.show');
});

// Fallback route in case of undefined routes (Optional)
Route::fallback(function () {
    if (Auth::guard('admin')->check()) {
        return redirect()->route('home');
    } elseif (Auth::guard('student')->check()) {
        return redirect()->route('student-home');
    } else {
        return redirect()->route('login');
    }
});

Route::post('/refresh-session', function () {
    Session::put('last_activity', now());
    return response()->json(['success' => true]);
})->middleware('auth:admin,student');
