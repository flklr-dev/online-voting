<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class VotingHistoryController extends Controller
{
    public function index(Request $request)
    {
        $user = auth('student')->user();
    
        // Get all elections the user has voted in
        $electionNames = DB::table('votes')
            ->join('elections', 'votes.election_id', '=', 'elections.election_id')
            ->where('votes.student_id', $user->student_id)
            ->distinct()
            ->pluck('elections.election_name');
    
        // Handle filtering and distinct results
        $filter = $request->input('filter');
        $query = DB::table('votes')
            ->join('elections', 'votes.election_id', '=', 'elections.election_id')
            ->join('candidates', 'votes.candidate_id', '=', 'candidates.candidate_id')
            ->join('positions', 'candidates.position_id', '=', 'positions.position_id')
            ->join('partylists', 'candidates.partylist_id', '=', 'partylists.partylist_id')
            ->where('votes.student_id', $user->student_id)
            ->select(
                'elections.election_name',
                'votes.vote_date',
                'candidates.student_name as candidate_name',
                'partylists.name as partylist',
                'positions.position_name'
            )
            ->distinct('elections.election_name'); // Ensure only one entry per election name
    
        if ($filter) {
            $query->where('elections.election_name', $filter);
        }
    
        $votingHistory = $query->paginate(10);
    
        return view('voting-history.index', compact('votingHistory', 'electionNames'));
    }
    
}
