<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Election;
use Illuminate\Support\Facades\DB;

class StudentHomeController extends Controller
{
    public function index()
    {
        $student = auth('student')->user();

        // Get ongoing elections that the student is eligible for and hasn't voted in yet
        $ongoingElections = Election::where('election_status', 'Ongoing')
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
            ->whereDoesntHave('votes', function ($query) use ($student) {
                // Exclude elections where the student has already voted
                $query->where('student_id', $student->student_id);
            })
            ->orderBy('end_date', 'asc')
            ->get();

        // Get upcoming elections that the student will be eligible for
        $upcomingElections = Election::where('election_status', 'Upcoming')
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
            ->orderBy('start_date', 'asc')
            ->get();

        // Get voting history with limit 10
        $votingHistory = DB::table('votes')
            ->join('elections', 'votes.election_id', '=', 'elections.election_id')
            ->join('candidates', 'votes.candidate_id', '=', 'candidates.candidate_id')
            ->join('positions', 'candidates.position_id', '=', 'positions.position_id')
            ->join('partylists', 'candidates.partylist_id', '=', 'partylists.partylist_id')
            ->where('votes.student_id', $student->student_id)
            ->select(
                'elections.election_name',
                'votes.vote_date',
                'candidates.student_name as candidate_name',
                'partylists.name as partylist',
                'positions.position_name'
            )
            ->orderBy('votes.vote_date', 'desc')
            ->limit(10)
            ->get();

        return view('student-home', compact('ongoingElections', 'upcomingElections', 'votingHistory'));
    }

}
