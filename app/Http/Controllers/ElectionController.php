<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Election;
use App\Models\Candidate;
use App\Models\Vote;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use Exception;
use Illuminate\Validation\ValidationException;
use App\Services\ActivityLogService;

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
                'election_status' => 'required|string|in:Upcoming,Ongoing,Completed',
            ]);

            if ($validatedData['election_type'] === 'General') {
                $validatedData['restriction'] = 'None';
            }

            $election = Election::create($validatedData);

            // Log the activity
            ActivityLogService::log(
                'create',
                'Elections',
                "Created new election: {$election->election_name} (Type: {$election->election_type})"
            );

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
            $oldData = $election->toArray(); // Store old data for logging

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

            // Log the activity with changes
            $changes = [];
            foreach ($validatedData as $key => $value) {
                if ($oldData[$key] !== $value) {
                    $changes[] = "$key: {$oldData[$key]} → $value";
                }
            }
            
            if (!empty($changes)) {
                ActivityLogService::log(
                    'update',
                    'Elections',
                    "Updated election: {$election->election_name}. Changes: " . implode(', ', $changes)
                );
            }

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
            $election = Election::where('election_id', $id)->firstOrFail();
            $electionName = $election->election_name; // Store name before deletion
            
            $election->delete();

            // Log the activity
            ActivityLogService::log(
                'delete',
                'Elections',
                "Deleted election: {$electionName}"
            );
    
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
        $student = auth('student')->user();

        if (!$student) {
            return redirect()->route('ongoing-elections.index')
                ->with('error', 'Student not authenticated.');
        }

        if (strtolower($student->status) !== 'active') {
            return redirect()->route('ongoing-elections.index')
                ->with('error', 'You are not eligible to vote due to inactive status.');
        }

        $election = Election::with(['candidates.position', 'candidates.partylist'])
            ->findOrFail($election_id);

        // Sort candidates by position priority
        $sortedCandidates = $election->candidates->sort(function ($a, $b) {
            $priorityA = $a->position->getPositionPriority();
            $priorityB = $b->position->getPositionPriority();
            
            if ($priorityA === $priorityB) {
                return $a->position->position_name <=> $b->position->position_name;
            }
            
            return $priorityA <=> $priorityB;
        })->groupBy('position_id');

        // Replace the original candidates with sorted ones
        $election->setRelation('candidates', collect($sortedCandidates)->flatten());

        return view('vote.index', compact('election'));
    }
    
    
    public function storeVote(Request $request)
    {
        try {
            DB::beginTransaction();
            
            $student = auth()->guard('student')->user();
            
            if (!$student) {
                return response()->json([
                    'success' => false,
                    'message' => 'Student not authenticated.'
                ], 401);
            }

            // Validate request
            $validatedData = $request->validate([
                'election_id' => 'required|exists:elections,election_id',
                'candidates' => 'required|array',
                'candidates.*.candidate_id' => 'required|exists:candidates,candidate_id',
                'candidates.*.position_id' => 'required|exists:positions,position_id',
            ]);

            // Check if student has already voted
            $alreadyVoted = Vote::where('student_id', $student->student_id)
                ->where('election_id', $request->election_id)
                ->exists();

            if ($alreadyVoted) {
                DB::rollBack();
                return response()->json([
                    'success' => false,
                    'message' => 'You have already voted in this election.'
                ], 400);
            }

            // Store each vote
            foreach ($request->candidates as $vote) {
                $newVote = new Vote();
                $newVote->vote_id = uniqid('vote_', true);
                $newVote->student_id = $student->student_id;
                $newVote->election_id = $request->election_id;
                $newVote->position_id = $vote['position_id'];
                $newVote->candidate_id = $vote['candidate_id'];
                $newVote->vote_date = now();
                $newVote->save();
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Vote cast successfully!'
            ]);

        } catch (ValidationException $e) {
            DB::rollBack();
            Log::error('Validation error: ' . json_encode($e->errors()));
            return response()->json([
                'success' => false,
                'message' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Vote casting error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'An error occurred while casting your vote. Please try again.',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function updateStatus(Request $request, $id)
    {
        try {
            $election = Election::findOrFail($id);
            $oldStatus = $election->election_status; // Store old status
    
            $validatedData = $request->validate([
                'election_status' => 'required|string|in:Upcoming,Ongoing,Completed',
            ]);
    
            $election->election_status = $validatedData['election_status'];
            $election->save();

            // Log the status change
            ActivityLogService::log(
                'update',
                'Elections',
                "Updated election status: {$election->election_name} ({$oldStatus} → {$election->election_status})"
            );
    
            return response()->json(['success' => true, 'message' => 'Election status updated successfully']);
        } catch (Exception $e) {
            return response()->json(['success' => false, 'message' => 'Failed to update election status.'], 500);
        }
    }

    
    
}
