<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Election;
use App\Models\Student;

class HomeController extends Controller
{
    public function index()
    {
        // Retrieve actual data from the database
        $totalElections = Election::count(); // Get total number of elections
        $ongoingElections = Election::where('election_status', 'Ongoing')->count(); // Count ongoing elections
        $totalVoters = Student::where('status', 'Active')->count(); // Get total number of voters\
        $totalVotesCast = 10241; // Example voters participated

        // Pass dummy data to the view
        return view('home', compact('totalElections', 'totalVoters', 'ongoingElections', 'totalVotesCast'));
    }
}
