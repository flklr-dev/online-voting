<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Election;
use App\Models\Vote;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ResultController extends Controller
{
    public function index(Request $request)
    {
        // Fetch distinct years from the start_date column
        $years = Election::selectRaw('YEAR(start_date) as year')->distinct()->pluck('year')->sortDesc();
    
        // Get the academic_year filter from the request
        $academicYear = $request->input('academic_year');
    
        // Filter elections based on the academic_year if provided
        $elections = Election::whereIn('election_status', ['Ongoing', 'Completed'])
            ->when($academicYear, function ($query, $academicYear) {
                $query->whereYear('start_date', $academicYear);
            })
            ->orderByDesc('start_date')
            ->get();
    
        return view('results.index', compact('elections', 'years'));
    }       

    public function show($id)
    {
        $election = Election::with(['candidates.position'])->findOrFail($id);
    
        // Updated query to use partylist_id and include partylist name
        $positions = DB::table('candidates')
            ->join('positions', 'candidates.position_id', '=', 'positions.position_id')
            ->join('partylists', 'candidates.partylist_id', '=', 'partylists.partylist_id')
            ->leftJoin('votes', 'candidates.candidate_id', '=', 'votes.candidate_id')
            ->select(
                'positions.position_id',
                'positions.position_name', 
                'positions.max_vote', 
                'candidates.candidate_id', 
                'candidates.student_name', 
                'candidates.picture', 
                'candidates.campaign_statement', 
                'partylists.name as partylist_name', // Get partylist name instead
                DB::raw('COUNT(votes.vote_id) as total_votes')
            )
            ->where('candidates.election_id', $id)
            ->groupBy(
                'positions.position_id',
                'positions.position_name', 
                'positions.max_vote', 
                'candidates.candidate_id', 
                'candidates.student_name', 
                'candidates.picture', 
                'candidates.campaign_statement', 
                'partylists.name' // Group by partylist name
            )
            ->orderBy('positions.position_id')
            ->orderBy('total_votes', 'desc')
            ->get()
            ->groupBy('position_name');
    
        return view('results.show', compact('election', 'positions'));
    }

    public function downloadResults($electionId)
    {
        $election = Election::findOrFail($electionId);
        $filename = $election->election_name . "_Results.csv";
    
        // Updated query to use partylist name
        $results = DB::table('candidates')
            ->join('positions', 'candidates.position_id', '=', 'positions.position_id')
            ->join('partylists', 'candidates.partylist_id', '=', 'partylists.partylist_id')
            ->leftJoin('votes', 'candidates.candidate_id', '=', 'votes.candidate_id')
            ->select(
                'candidates.student_name',
                'positions.position_name',
                'partylists.name as partylist_name',
                DB::raw('COUNT(votes.vote_id) as total_votes'),
                'positions.position_id'
            )
            ->where('candidates.election_id', $electionId)
            ->groupBy(
                'candidates.student_name',
                'positions.position_name',
                'partylists.name',
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
                    $result->partylist_name,
                    $result->total_votes,
                ]);
            }
            fclose($file);
        };
    
        // Return the CSV as a streamed response
        return new StreamedResponse($callback, 200, $headers);
    }
    
}
