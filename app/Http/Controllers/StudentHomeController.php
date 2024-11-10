<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Election;

class StudentHomeController extends Controller
{
    public function index()
    {
        // Get the first 4 ongoing elections
        $ongoingElections = Election::where('election_status', 'Ongoing')
            ->orderBy('start_date', 'asc')
            ->take(4)
            ->get();

        return view('student-home', compact('ongoingElections'));
    }
}