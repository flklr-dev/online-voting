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

// Authentication routes
Route::get('/', [AuthController::class, 'showLoginForm'])->name('login'); // Login form display
Route::post('/login', [AuthController::class, 'login'])->name('login.post'); // Login form submission
Route::post('/logout', [AuthController::class, 'logout'])->name('logout'); // Logout

// Admin Routes - Protected by admin middleware
Route::middleware(['auth:admin'])->group(function () {
    Route::get('/home', [HomeController::class, 'index'])->name('home'); // Admin dashboard
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
});

// Student Routes - Protected by student middleware
Route::middleware(['auth:student'])->group(function () { 
    // Student Dashboard
    Route::get('/student-home', [StudentHomeController::class, 'index'])->name('student-home');
    Route::get('/ongoing-elections', [ElectionController::class, 'ongoingElections'])->name('ongoing-elections.index');
    Route::get('/vote/{election_id}', [ElectionController::class, 'voteInterface'])->name('vote.interface');
    Route::post('/vote/store', [ElectionController::class, 'storeVote'])->name('vote.store');
    Route::get('/voting-history', [VotingHistoryController::class, 'index'])->name('voting-history.index');
    Route::get('/profile', [ProfileController::class, 'index'])->name('profile.index');
    Route::put('/profile', [ProfileController::class, 'update'])->name('profile.update');
});


// Fallback route in case of undefined routes (Optional)
Route::fallback(function() {
    return redirect()->route('login');
});
