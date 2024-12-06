<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Election;
use App\Models\Candidate;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use Exception;
use Illuminate\Validation\ValidationException;

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
        })
        ->orderByRaw("
        FIELD(election_status, 'Ongoing', 'Upcoming', 'Completed'),
        start_date ASC
        ")
        
        ->paginate($limit);

        $electionTypes = ['General','Faculty','Program'];
        $electionStatuses = ['Upcoming', 'Ongoing', 'Completed'];

        return view('elections.index', compact('elections', 'electionTypes', 'electionStatuses'));
    }

    public function create()
    {
        $electionTypes = ['General','Faculty','Program']; // List of faculties

        return view('elections.create', compact('electionTypes'));
    }
    
    public function create2()
    {
        $electionStatuses = ['Upcoming', 'Ongoing', 'Completed'];

        return view('elections.create2', compact('electionStatuses')); 
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

            if ($validatedData['election_type'] === 'General') {
                $validatedData['restriction'] = 'None';
            }

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
        try {
            $election = Election::findOrFail($id);

            $validatedData = $request->validate([
                'election_name' => 'required|string',
                'description' => 'required|string',
                'election_type' => 'required|in:General,Faculty,Program',
                'restriction' => 'required_if:election_type,Faculty,Program',
                'start_date' => 'required|date',
                'end_date' => 'required|date|after:start_date',
                'election_status' => 'required|string'
            ]);

            $election->update($validatedData);

            return response()->json([
                'success' => true,
                'message' => 'Election updated successfully!',
                'election' => $election
            ]);

        } catch (ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to update election.'
            ], 500);
        }
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
        $student = auth('student')->user(); // Get the authenticated student
        
        // Retrieve ongoing elections
        $ongoingElections = Election::where('election_status', 'Ongoing')
            ->where(function ($query) use ($student) {
                // Include General elections
                $query->where('election_type', 'General')
                    ->orWhere(function ($query) use ($student) {
                        // Include Faculty elections that match the student's faculty
                        $query->where('election_type', 'Faculty')
                            ->where('restriction', $student->faculty);
                    })
                    ->orWhere(function ($query) use ($student) {
                        // Include Program elections that match the student's program
                        $query->where('election_type', 'Program')
                            ->where('restriction', $student->program);
                    });
            })
            ->whereDoesntHave('votes', function ($query) use ($student) {
                // Exclude elections the student has already voted in
                $query->where('student_id', $student->student_id);
            })
            ->get();
    
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

    public function updateStatus(Request $request, $id)
    {
        try {
            $election = Election::findOrFail($id);
    
            $validatedData = $request->validate([
                'election_status' => 'required|string|in:Upcoming,Ongoing,Completed',
            ]);
    
            $election->election_status = $validatedData['election_status'];
            $election->save();
    
            return response()->json(['success' => true, 'message' => 'Election status updated successfully']);
        } catch (Exception $e) {
            return response()->json(['success' => false, 'message' => 'Failed to update election status.'], 500);
        }
    }

    
    
}
