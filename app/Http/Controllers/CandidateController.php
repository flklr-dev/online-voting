<?php

namespace App\Http\Controllers;

use App\Models\Candidate;
use App\Models\Election;
use App\Models\Position;
use App\Models\Student;
use App\Models\Partylist;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;
use App\Services\ActivityLogService;

class CandidateController extends Controller
{
    public function index(Request $request)
    {
        $limit = $request->input('limit', 10); // Pagination limit
        $search = $request->input('search');
    
        // Modify the search to include election_name and position_name
        $candidates = Candidate::with(['election', 'position', 'student', 'partylist'])
            ->when($search, function ($query, $search) {
                return $query->whereHas('election', function($q) use ($search) {
                        $q->where('election_name', 'like', "%$search%");
                    })
                    ->orWhereHas('position', function($q) use ($search) {
                        $q->where('position_name', 'like', "%$search%");
                    })
                    ->orWhere('student_name', 'like', "%$search%")
                    ->orWhere('student_id', 'like', "%$search%");
            })
            ->paginate($limit);
    
        $elections = Election::whereIn('election_status', ['Upcoming', 'Ongoing'])
            ->orderBy('start_date', 'asc')
            ->get();
        $positions = Position::all();
        $partylists = Partylist::all();
    
        return view('candidates.index', compact('candidates', 'elections', 'positions', 'partylists'));
    }

    public function store(Request $request)
    {
        try {
            $validatedData = $request->validate([
                'student_id' => 'required|exists:students,student_id',
                'election_id' => 'required|exists:elections,election_id',
                'position_id' => 'required|exists:positions,position_id',
                'student_name' => 'required|string|max:255',
                'campaign_statement' => 'nullable|string',
                'partylist_id' => 'required|exists:partylists,partylist_id',
                'picture' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048'
            ]);
    
            if ($request->hasFile('picture')) {
                $fileName = time() . '.' . $request->file('picture')->extension();
                $request->file('picture')->move(public_path('images/candidates'), $fileName);
                $validatedData['picture'] = $fileName;
            }
    
            $candidate = Candidate::create($validatedData);

            // Get related data for detailed logging
            $election = Election::find($validatedData['election_id']);
            $position = Position::find($validatedData['position_id']);
            $partylist = Partylist::find($validatedData['partylist_id']);

            // Log the activity
            ActivityLogService::log(
                'create',
                'Candidates',
                "Added new candidate: {$candidate->student_name} for position {$position->position_name} " .
                "in election '{$election->election_name}' (Partylist: {$partylist->partylist_name})"
            );
    
            return response()->json(['success' => true, 'message' => 'Candidate added successfully!']);
        } catch (\Exception $e) {
            Log::error('Error adding candidate: ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }
    
    public function update(Request $request, $id)
    {
        try {
            $candidate = Candidate::findOrFail($id);
            $oldData = $candidate->toArray(); // Store old data for logging

            $validatedData = $request->validate([
                'student_id' => 'required|exists:students,student_id',
                'election_id' => 'required|exists:elections,election_id',
                'position_id' => 'required|exists:positions,position_id',
                'student_name' => 'required|string|max:255',
                'campaign_statement' => 'nullable|string',
                'partylist_id' => 'required|exists:partylists,partylist_id',
                'picture' => 'nullable|image|max:2048',
            ]);

            if ($request->hasFile('picture')) {
                $fileName = time() . '.' . $request->file('picture')->extension();
                $request->file('picture')->move(public_path('images/candidates'), $fileName);
                $validatedData['picture'] = $fileName;
            }

            $candidate->update($validatedData);

            // Get related data for logging changes
            $changes = [];
            if ($oldData['position_id'] != $validatedData['position_id']) {
                $oldPosition = Position::find($oldData['position_id'])->position_name;
                $newPosition = Position::find($validatedData['position_id'])->position_name;
                $changes[] = "position: {$oldPosition} → {$newPosition}";
            }
            if ($oldData['partylist_id'] != $validatedData['partylist_id']) {
                $oldPartylist = Partylist::find($oldData['partylist_id'])->partylist_name;
                $newPartylist = Partylist::find($validatedData['partylist_id'])->partylist_name;
                $changes[] = "partylist: {$oldPartylist} → {$newPartylist}";
            }
            if ($oldData['campaign_statement'] != $validatedData['campaign_statement']) {
                $changes[] = "campaign statement updated";
            }
            if ($request->hasFile('picture')) {
                $changes[] = "profile picture updated";
            }

            if (!empty($changes)) {
                ActivityLogService::log(
                    'update',
                    'Candidates',
                    "Updated candidate: {$candidate->student_name}. Changes: " . implode(', ', $changes)
                );
            }

            return response()->json(['success' => true, 'message' => 'Candidate updated successfully!']);
        } catch (\Exception $e) {
            Log::error('Error updating candidate: ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    public function destroy($id)
    {
        try {
            $candidate = Candidate::with(['election', 'position'])->find($id);

            if (!$candidate) {
                return response()->json(['success' => false, 'message' => 'Candidate not found.']);
            }

            $candidateName = $candidate->student_name;
            $electionName = $candidate->election->election_name;
            $positionName = $candidate->position->position_name;

            $candidate->delete();

            // Log the activity
            ActivityLogService::log(
                'delete',
                'Candidates',
                "Deleted candidate: {$candidateName} (Position: {$positionName}, Election: {$electionName})"
            );

            return response()->json(['success' => true, 'message' => 'Candidate deleted successfully!']);
        } catch (\Exception $e) {
            Log::error('Error deleting candidate: ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'Failed to delete candidate.'], 500);
        }
    }

    public function getEligibleStudents($election_id)
    {
        try {
            $election = Election::findOrFail($election_id);
            
            // Query to get eligible students based on election type and restriction
            $query = Student::where('status', 'Active')
                ->whereNotExists(function ($query) use ($election_id) {
                    $query->select(DB::raw(1))
                        ->from('candidates')
                        ->whereRaw('candidates.student_id = students.student_id')
                        ->where('election_id', $election_id);
                });

            // Add restrictions based on election type
            if ($election->election_type === 'Faculty') {
                $query->where('faculty', $election->restriction);
            } elseif ($election->election_type === 'Program') {
                $query->where('program', $election->restriction);
            }

            $students = $query->get(['student_id', 'fullname']);

            return response()->json(['success' => true, 'students' => $students]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }
}