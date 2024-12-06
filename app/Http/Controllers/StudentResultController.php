<?php

namespace App\Http\Controllers;

use App\Models\Election;
use Illuminate\Support\Facades\DB;

class StudentResultController extends Controller
{
    public function index()
    {
        $student = auth('student')->user();
        
        // Get eligible elections (Ongoing and Completed)
        $elections = Election::whereIn('election_status', ['Ongoing', 'Completed'])
            ->where(function ($query) use ($student) {
                // General elections
                $query->where('election_type', 'General')
                    ->orWhere(function ($query) use ($student) {
                        // Faculty elections matching student's faculty
                        $query->where('election_type', 'Faculty')
                            ->where('restriction', $student->faculty);
                    })
                    ->orWhere(function ($query) use ($student) {
                        // Program elections matching student's program
                        $query->where('election_type', 'Program')
                            ->where('restriction', $student->program);
                    });
            })
            ->orderBy('election_status', 'asc')
            ->orderBy('end_date', 'desc')
            ->get();

        return view('student-results.index', compact('elections'));
    }

    public function show(Election $election)
    {
        $student = auth('student')->user();
        
        // Check eligibility
        if ($election->election_type !== 'General' && 
            (($election->election_type === 'Faculty' && $election->restriction !== $student->faculty) ||
             ($election->election_type === 'Program' && $election->restriction !== $student->program))) {
            return redirect()->route('student-results.index')
                           ->with('error', 'You are not eligible to view these results.');
        }

        // Get positions with candidates and their vote counts
        $positions = DB::table('candidates')
            ->join('positions', 'candidates.position_id', '=', 'positions.position_id')
            ->join('partylists', 'candidates.partylist_id', '=', 'partylists.partylist_id')
            ->leftJoin('votes', 'candidates.candidate_id', '=', 'votes.candidate_id')
            ->where('candidates.election_id', $election->election_id)
            ->select(
                'positions.position_id',
                'positions.position_name',
                'candidates.candidate_id',
                'candidates.student_name',
                'candidates.picture',
                'candidates.campaign_statement',
                'partylists.name as partylist_name',
                DB::raw('COUNT(votes.vote_id) as total_votes')
            )
            ->groupBy(
                'positions.position_id',
                'positions.position_name',
                'candidates.candidate_id',
                'candidates.student_name',
                'candidates.picture',
                'candidates.campaign_statement',
                'partylists.name'
            )
            ->orderBy('positions.position_id', 'asc')
            ->orderBy('total_votes', 'desc')
            ->get()
            ->groupBy('position_name');

        return view('student-results.show', compact('election', 'positions'));
    }
}