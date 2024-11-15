<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Election;
use App\Models\Vote;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpFoundation\StreamedResponse;

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

    public function downloadResults($electionId)
    {
        $election = Election::findOrFail($electionId);
        $filename = $election->election_name . "_Results.csv";
    
        // Fetch election data with total votes per candidate
        $results = DB::table('candidates')
            ->join('positions', 'candidates.position_id', '=', 'positions.position_id')
            ->leftJoin('votes', 'candidates.candidate_id', '=', 'votes.candidate_id')
            ->select(
                'candidates.student_name',
                'positions.position_name',
                'candidates.partylist',
                DB::raw('COUNT(votes.vote_id) as total_votes'),
                'positions.position_id'
            )
            ->where('candidates.election_id', $electionId)
            ->groupBy(
                'candidates.student_name',
                'positions.position_name',
                'candidates.partylist',
                'positions.position_id'
            )
            ->orderBy('positions.position_id')
            ->orderBy('total_votes', 'desc')
            ->get();
    
        // Check if there are any results to download
        if ($results->isEmpty()) {
            // Redirect back with an error message if there are no results
            return redirect()->back()->with('error', 'No results available for this election.');
        }
    
        // Prepare the CSV headers
        $headers = [
            "Content-type"        => "text/csv",
            "Content-Disposition" => "attachment; filename=$filename",
            "Pragma"              => "no-cache",
            "Cache-Control"       => "must-revalidate, post-check=0, pre-check=0",
            "Expires"             => "0",
        ];
    
        // Define the callback for streamed response
        $callback = function() use ($results) {
            $file = fopen('php://output', 'w');
            // Add the header of columns
            fputcsv($file, ['Candidate Name', 'Position', 'Partylist', 'Total Votes']);
    
            // Add data rows
            foreach ($results as $result) {
                fputcsv($file, [
                    $result->student_name,
                    $result->position_name,
                    $result->partylist,
                    $result->total_votes,
                ]);
            }
            fclose($file);
        };
    
        // Return the CSV as a streamed response
        return new StreamedResponse($callback, 200, $headers);
    }
    
}
