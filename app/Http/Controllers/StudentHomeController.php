<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Election;
use Illuminate\Support\Facades\DB;

class StudentHomeController extends Controller
{
    public function index()
    {
        $studentId = auth()->guard('student')->id(); // Get logged-in student's ID

        // Get the first 4 ongoing elections
        $ongoingElections = Election::where('election_status', 'Ongoing')
            ->orderBy('start_date', 'asc')
            ->take(4)
            ->get();

        // Get the 3 nearest upcoming elections based on the start_date
        $upcomingElections = Election::where('election_status', 'Upcoming')
            ->orderBy('start_date', 'asc')
            ->take(3)
            ->get();

        // Get voting history for the logged-in student, one entry per election
        $votingHistory = DB::table('votes')
            ->join('elections', 'votes.election_id', '=', 'elections.election_id')
            ->select(
                'elections.election_id',
                'elections.election_name',
                DB::raw('MAX(votes.vote_date) as vote_date') // Get the most recent vote date per election
            )
            ->where('votes.student_id', $studentId)
            ->groupBy('elections.election_id', 'elections.election_name')
            ->orderBy('vote_date', 'desc')
            ->take(5) // Limit to recent 5 voting history records
            ->get();

        return view('student-home', compact('ongoingElections', 'upcomingElections', 'votingHistory'));
    }
}
