<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Election;
use App\Models\Position;
use App\Models\Student;
use App\Models\Vote;
use Illuminate\Support\Facades\DB;

class HomeController extends Controller
{
    public function index()
    {
        $totalElections = Election::count();
        $ongoingElections = Election::where('election_status', 'Ongoing')->count();
        $totalVoters = Student::where('status', 'Active')->count();
        $totalVotesCast = Vote::count();
        $recentElections = Election::with('positions')->orderBy('start_date', 'desc')->take(5)->get();
    
        return view('home', compact('totalElections', 'totalVoters', 'ongoingElections', 'totalVotesCast', 'recentElections'));
    }
    

    // Add this to HomeController.php

    public function getVotingResults(Request $request)
    {
        $electionId = $request->input('election_id');
        $positionId = $request->input('position_id');

        // Fetch candidates and their votes for the given election and position
        $candidates = Vote::join('candidates', 'votes.candidate_id', '=', 'candidates.candidate_id')
            ->select('candidates.student_name as candidate_name', DB::raw('COUNT(votes.vote_id) as total_votes'))
            ->where('votes.election_id', $electionId)
            ->where('votes.position_id', $positionId)
            ->groupBy('candidates.candidate_id', 'candidates.student_name')
            ->orderBy('total_votes', 'desc')
            ->get();

        return response()->json($candidates);
    }

    public function getPositionsByElection($electionId)
    {
        // Fetch positions linked to the specified election
        $positions = Position::whereHas('candidates', function ($query) use ($electionId) {
            $query->where('election_id', $electionId);
        })->get(['position_id', 'position_name']);

        return response()->json($positions);
    }


}
