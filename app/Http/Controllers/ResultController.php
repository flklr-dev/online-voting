<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Election;
use Illuminate\Support\Facades\DB;

class ResultController extends Controller
{
    public function index()
    {
        // Fetch both ongoing and completed elections
        $elections = Election::whereIn('election_status', ['Ongoing', 'Completed'])->get();
        
        return view('results.index', compact('elections'));
    }
    

    public function show($id)
    {
        $election = Election::with(['candidates.position'])->findOrFail($id);
    
        // Retrieve vote totals for each candidate per position, ordered by position_id
        $positions = DB::table('candidates')
            ->join('positions', 'candidates.position_id', '=', 'positions.position_id')
            ->leftJoin('votes', 'candidates.candidate_id', '=', 'votes.candidate_id')
            ->select(
                'positions.position_id',        // Include position_id for ordering
                'positions.position_name', 
                'positions.max_vote', 
                'candidates.candidate_id', 
                'candidates.student_name', 
                'candidates.picture', 
                'candidates.campaign_statement', 
                'candidates.partylist', 
                DB::raw('COUNT(votes.vote_id) as total_votes')
            )
            ->where('candidates.election_id', $id)
            ->groupBy(
                'positions.position_id',         // Include position_id in groupBy for ordering
                'positions.position_name', 
                'positions.max_vote', 
                'candidates.candidate_id', 
                'candidates.student_name', 
                'candidates.picture', 
                'candidates.campaign_statement', 
                'candidates.partylist'
            )
            ->orderBy('positions.position_id') // Order by position_id to ensure positions appear in the correct order
            ->orderBy('total_votes', 'desc')   // Order candidates within each position by total_votes
            ->get()
            ->groupBy('position_name');
    
        return view('results.show', compact('election', 'positions'));
    }
    
}
