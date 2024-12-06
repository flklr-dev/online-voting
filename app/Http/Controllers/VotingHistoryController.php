<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class VotingHistoryController extends Controller
{
    public function index(Request $request)
    {
        $user = auth('student')->user();
        $limit = $request->input('limit', 10); // Get limit from request, default to 10
        $search = $request->input('search');
        $filter = $request->input('filter');

        // Get all elections the user has voted in
        $electionNames = DB::table('votes')
            ->join('elections', 'votes.election_id', '=', 'elections.election_id')
            ->where('votes.student_id', $user->student_id)
            ->distinct()
            ->pluck('elections.election_name');

        // Build the query
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
            );

        // Apply search if provided
        if ($search) {
            $query->where(function($q) use ($search) {
                $q->where('elections.election_name', 'like', "%$search%")
                  ->orWhere('positions.position_name', 'like', "%$search%")
                  ->orWhere('candidates.student_name', 'like', "%$search%");
            });
        }

        // Apply election filter if provided
        if ($filter) {
            $query->where('elections.election_name', $filter);
        }

        $votingHistory = $query->orderBy('votes.vote_date', 'desc')
                              ->paginate($limit)
                              ->appends(['limit' => $limit, 'search' => $search, 'filter' => $filter]);

        return view('voting-history.index', compact('votingHistory', 'electionNames'));
    }
    
}
