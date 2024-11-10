<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Election;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class ResultController extends Controller
{
    public function index()
    {
        $elections = Election::all();
        return view('results.index', compact('elections'));
    }

    public function fetchResults($election_id)
    {
        try {
            $election = Election::with('candidates.position')->findOrFail($election_id);

            $results = DB::table('votes')
                ->select(
                    'candidates.candidate_id',
                    'candidates.student_name',
                    'candidates.picture',
                    'candidates.campaign_statement',
                    'candidates.partylist',
                    DB::raw('COUNT(votes.candidate_id) as total_votes'),
                    'votes.position_id',
                    'positions.position_name',
                    'positions.max_vote'
                )
                ->join('candidates', 'candidates.candidate_id', '=', 'votes.candidate_id')
                ->join('positions', 'positions.position_id', '=', 'votes.position_id')
                ->where('votes.election_id', $election_id)
                ->groupBy('votes.position_id', 'votes.candidate_id')
                ->orderBy('votes.position_id')
                ->orderByDesc('total_votes')
                ->get()
                ->groupBy('position_id');

            return response()->json(['election' => $election, 'results' => $results]);
        } catch (\Exception $e) {
            Log::error('Error fetching results: ' . $e->getMessage());
            return response()->json(['message' => 'Error fetching results'], 500);
        }
    }
}
