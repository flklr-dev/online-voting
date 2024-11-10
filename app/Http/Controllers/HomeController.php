<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Election;
use App\Models\Student;

class HomeController extends Controller
{
    public function index()
    {
        // Fetch required statistics
        $totalElections = Election::count();
        $ongoingElections = Election::where('election_status', 'Ongoing')->count();
        $totalVoters = Student::where('status', 'Active')->count();
        $totalVotesCast = 10241; // Replace with actual vote counting if needed

        // Fetch the 5 most recent elections
        $recentElections = Election::orderBy('start_date', 'desc')->take(5)->get();

        // Pass the data to the view
        return view('home', compact('totalElections', 'totalVoters', 'ongoingElections', 'totalVotesCast', 'recentElections'));
    }
}
