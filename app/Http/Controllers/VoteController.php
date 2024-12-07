<?php

namespace App\Http\Controllers;

use App\Models\Vote;
use Illuminate\Http\Request;

class VoteController extends Controller
{
    public function adminVotingHistory(Request $request)
    {
        $query = Vote::with(['student', 'election', 'position', 'candidate.partylist'])
            ->orderBy('vote_date', 'desc');

        // Search functionality
        if ($request->has('search')) {
            $search = $request->search;
            $query->whereHas('student', function($q) use ($search) {
                $q->where('fullname', 'like', "%{$search}%");
            })->orWhereHas('election', function($q) use ($search) {
                $q->where('election_name', 'like', "%{$search}%");
            });
        }

        // Pagination
        $perPage = $request->input('limit', 10);
        $votingHistory = $query->paginate($perPage);

        return view('admin.voting-history', compact('votingHistory'));
    }
}
