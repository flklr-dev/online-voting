<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Election;
use Illuminate\Support\Facades\DB;

class StudentHomeController extends Controller
{
    public function index()
    {
        $student = auth('student')->user(); // Get the authenticated student
        
        // Fetch eligible ongoing elections
        $ongoingElections = Election::where('election_status', 'Ongoing')
            ->where(function ($query) use ($student) {
                // General elections
                $query->where('election_type', 'General')
                    ->orWhere(function ($query) use ($student) {
                        // Faculty elections
                        $query->where('election_type', 'Faculty')
                            ->where('restriction', $student->faculty);
                    })
                    ->orWhere(function ($query) use ($student) {
                        // Program elections
                        $query->where('election_type', 'Program')
                            ->where('restriction', $student->program);
                    });
            })
            ->whereDoesntHave('votes', function ($query) use ($student) {
                // Exclude elections already voted in
                $query->where('student_id', $student->student_id);
            })
            ->orderBy('start_date', 'asc')
            ->take(4) // Limit to the first 4
            ->get();
    
        $upcomingElections = Election::where('election_status', 'Upcoming')
        ->where(function ($query) use ($student) {
            // General elections
            $query->where('election_type', 'General')
                ->orWhere(function ($query) use ($student) {
                    // Faculty elections
                    $query->where('election_type', 'Faculty')
                        ->where('restriction', $student->faculty);
                })
                ->orWhere(function ($query) use ($student) {
                    // Program elections
                    $query->where('election_type', 'Program')
                        ->where('restriction', $student->program);
                });
        })
        ->orderBy('start_date', 'asc')
        ->take(4) // Limit to 3
        ->get();

        // Fetch recent voting history
        $votingHistory = DB::table('votes')
            ->join('elections', 'votes.election_id', '=', 'elections.election_id')
            ->select(
                'elections.election_id',
                'elections.election_name',
                DB::raw('MAX(votes.vote_date) as vote_date') // Most recent vote date per election
            )
            ->where('votes.student_id', $student->student_id)
            ->groupBy('elections.election_id', 'elections.election_name')
            ->orderBy('vote_date', 'desc')
            ->take(5) // Limit to the 5 most recent votes
            ->get();
    
        return view('student-home', compact('ongoingElections', 'upcomingElections', 'votingHistory'));
    }
    
}
