<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class VotingHistoryController extends Controller
{
    public function index(Request $request)
    {
        $studentId = auth()->guard('student')->id();
        $limit = $request->input('limit', 10);
        $search = $request->input('search');
    
        $votingHistory = DB::table('votes')
            ->join('elections', 'votes.election_id', '=', 'elections.election_id')
            ->join('positions', 'votes.position_id', '=', 'positions.position_id')
            ->join('candidates', 'votes.candidate_id', '=', 'candidates.candidate_id')
            ->select(
                'elections.election_name',
                'positions.position_name',
                'candidates.student_name as candidate_name',
                'votes.vote_date'
            )
            ->where('votes.student_id', $studentId)
            ->when($search, function ($query, $search) {
                $query->where('elections.election_name', 'like', "%$search%")
                      ->orWhere('positions.position_name', 'like', "%$search%")
                      ->orWhere('candidates.student_name', 'like', "%$search%");
            })
            ->orderBy('votes.vote_date', 'desc')
            ->paginate($limit);
    
        return view('voting-history.index', compact('votingHistory'));
    }
    
}
