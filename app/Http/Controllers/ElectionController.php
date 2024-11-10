<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Election;
use App\Models\Candidate;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use Exception;

class ElectionController extends Controller
{
    public function index(Request $request)
    {
        // Get the limit for pagination
        $limit = $request->input('limit', 10);
        $search = $request->input('search');

        // Modify the query to filter by student_id, email, program, faculty, or status
        $elections = Election::when($search, function ($query, $search) {
            return $query->where('election_id', 'like', "%$search%")
                        ->orWhere('election_name', 'like', "%$search%")
                        ->orWhere('description', 'like', "%$search%")
                        ->orWhere('election_type', 'like', "%$search%")
                        ->orWhere('restriction', 'like', "%$search%")
                        ->orWhere('start_date', 'like', "%$search%")
                        ->orWhere('end_date', 'like', "%$search%")
                        ->orWhere('election_status', 'like', "%$search%");
        })->paginate($limit);

        $electionTypes = ['General','Faculty','Program'];
        $electionStatuses = ['Upcoming', 'Ongoing', 'Completed'];

        return view('elections.index', compact('elections', 'electionTypes', 'electionStatuses'));
    }

    public function create()
    {
        $electionTypes = ['General','Faculty','Program']; // List of faculties

        return view('elections.create', compact('electionTypes')); // Pass $faculties to the view
    }
    
    public function create2()
    {
        $electionStatuses = ['Upcoming', 'Ongoing', 'Completed'];

        return view('elections.create2', compact('electionStatuses')); // Pass $faculties to the view
    }


    public function store(Request $request)
    {
        try {
            $validatedData = $request->validate([
                'election_name' => 'required|string|max:255',
                'description' => 'required|string',
                'election_type' => 'required|string|in:Faculty,Program,General',
                'restriction' => 'nullable|string',
                'start_date' => 'required|date|after_or_equal:today',
                'end_date' => 'required|date|after:start_date',
                'election_status' => 'required|string|in:Upcoming,Ongoing,Completed', // Capitalized values
            ]);

            Election::create($validatedData);

            return response()->json(['success' => true, 'message' => 'Election added successfully'], 200);
        } catch (Exception $e) {
            Log::error('Error creating election: ' . $e->getMessage());

            return response()->json(['success' => false, 'message' => 'Error adding election: ' . $e->getMessage()], 500);
        }
    }


    public function edit($id)
    {
        // Retrieve the election by ID
        $election = Election::findOrFail($id);
        
        // Pass the election to the edit view
        return view('elections.edit', compact('election'));
    }
    public function update(Request $request, $id)
    {
        $election = Election::where('election_id', $id)->firstOrFail();
    
        $validatedData = $request->validate([
            'election_name' => 'required|string|max:255',
            'description' => 'required|string',
            'election_type' => 'required|string|in:Faculty,Program,General',
            'start_date' => 'required|date|after_or_equal:today',
            'end_date' => 'required|date|after:start_date',
            'restriction' => 'nullable|string',
            'election_status' => 'required|string|in:Upcoming,Ongoing,Completed',
        ]);
    
        // Preserve the election_id and apply other validated data
        $election->update($validatedData);
    
        return response()->json(['success' => true, 'message' => 'Election updated successfully!']);
    }
    

    public function destroy($id)
    {
        try {
            // Use the correct primary key column: 'election_id'
            $election = Election::where('election_id', $id)->firstOrFail();
            $election->delete();
    
            return response()->json(['success' => true]);
        } catch (Exception $e) {
            Log::error('Error deleting election: ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => $e->getMessage()]);
        }
    }   

    public function show($id)
    {
        $election = Election::findOrFail($id);
        return view('elections.show', compact('election'));
    }

    public function ongoingElections()
    {
        $ongoingElections = Election::where('election_status', 'Ongoing')->get();

        return view('ongoing-elections.index', compact('ongoingElections'));
    }

    // Display the voting interface for a specific election
    public function voteInterface($election_id)
    {
        // Ensure the student guard is correctly set and authenticated
        $student = auth('student')->user();
    
        // Debugging: Check if the student object and status are correctly loaded
        if (!$student) {
            return redirect()->route('ongoing-elections.index')
                ->with('error', 'Student not authenticated.');
        }
    
        // Debug: Check the student's status
        if (strtolower($student->status) !== 'active') {
            return redirect()->route('ongoing-elections.index')
                ->with('error', 'You are not eligible to vote due to inactive status.');
        }
    
        $election = Election::with('candidates.position')->findOrFail($election_id);
    
        // Check if the student has already voted in the election
        $alreadyVoted = DB::table('votes')
            ->where('student_id', $student->student_id)
            ->where('election_id', $election_id)
            ->exists();
    
        if ($alreadyVoted) {
            return redirect()->route('ongoing-elections.index')
                ->with('error', 'You have already voted in this election.');
        }
    
        // Eligibility check based on election type
        if (
            $election->election_type === 'Faculty' &&
            $election->restriction !== $student->faculty
        ) {
            return redirect()->route('ongoing-elections.index')
                ->with('error', 'You are not eligible to vote in this Faculty election.');
        }
    
        if (
            $election->election_type === 'Program' &&
            $election->restriction !== $student->program
        ) {
            return redirect()->route('ongoing-elections.index')
                ->with('error', 'You are not eligible to vote in this Program election.');
        }
    
        // If all conditions pass, render the voting interface
        return view('vote.index', compact('election'));
    }
    
    
    public function storeVote(Request $request)
    {
        $student = auth()->guard('student')->user();
    
        // Check if the student has already voted in this election
        $alreadyVoted = DB::table('votes')
            ->where('student_id', $student->student_id)
            ->where('election_id', $request->election_id)
            ->exists();
    
        if ($alreadyVoted) {
            return response()->json(['success' => false, 'message' => 'You have already voted in this election.']);
        }
    
        // Store votes in the database
        try {
            foreach ($request->candidates as $candidate) {
                DB::table('votes')->insert([
                    'vote_id' => uniqid(),
                    'student_id' => $student->student_id,
                    'election_id' => $request->election_id,
                    'position_id' => $candidate['position_id'], // Allow null position_id
                    'candidate_id' => $candidate['candidate_id'],
                    'vote_date' => now(),
                ]);
            }
    
            return response()->json(['success' => true, 'message' => 'Vote cast successfully!']);
        } catch (Exception $e) {
            Log::error('Error storing vote: ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'Failed to cast vote.'], 500);
        }
    }
    
    
}
