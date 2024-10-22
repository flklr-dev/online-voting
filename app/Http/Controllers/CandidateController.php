<?php

namespace App\Http\Controllers;

use App\Models\Candidate;
use App\Models\Election;
use App\Models\Position;
use App\Models\Student;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class CandidateController extends Controller
{
    public function index(Request $request)
    {
        $limit = $request->get('limit', 10); // Pagination limit
        $search = $request->input('search');
    
        // Modify the search to include election_name and position_name
        $candidates = Candidate::with(['election', 'position', 'student'])
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
    
        $elections = Election::all();
        $positions = Position::all();
    
        return view('candidates.index', compact('candidates', 'elections', 'positions'));
    }
    

    
    public function store(Request $request)
    {
        $validatedData = $request->validate([
            'student_id' => 'required|exists:students,student_id',
            'election_id' => 'required|exists:elections,election_id',
            'position_id' => 'required|exists:positions,position_id',
            'student_name' => 'required|string|max:255',
            'campaign_statement' => 'nullable|string',
            'partylist' => 'nullable|string',
            'picture' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048'
        ]);
    
        if ($request->hasFile('picture')) {
            $fileName = time() . '.' . $request->file('picture')->extension();
            $request->file('picture')->move(public_path('images/candidates'), $fileName);
            $validatedData['picture'] = $fileName;
        }
    
        Candidate::create($validatedData);
    
        return response()->json(['success' => true, 'message' => 'Candidate added successfully!']);
    }
    
    
    public function update(Request $request, $id)
    {
        try {
            $candidate = Candidate::findOrFail($id); // Find the candidate by ID
    
            $validated = $request->validate([
                'election_id' => 'required|integer|exists:elections,election_id',
                'position_id' => 'required|integer|exists:positions,position_id',
                'student_id' => 'required|string|exists:students,student_id',
                'student_name' => 'required|string|max:100',
                'campaign_statement' => 'nullable|string',
                'partylist' => 'nullable|string|max:100',
                'picture' => 'nullable|image|max:2048',
            ]);
    
            // Handle picture update or keep the old picture
            if ($request->hasFile('picture')) {
                if ($candidate->picture && Storage::exists('public/' . $candidate->picture)) {
                    Storage::delete('public/' . $candidate->picture); // Delete old picture
                }
                $path = $request->file('picture')->store('images/candidates', 'public');
                $validated['picture'] = $path;
            } else {
                $validated['picture'] = $candidate->picture; // Keep existing picture if not updated
            }
    
            // Update candidate details
            $candidate->update($validated);
    
            return response()->json(['success' => true, 'message' => 'Candidate updated successfully!']);
        } catch (\Exception $e) {
            Log::error('Error updating candidate: ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'Failed to update candidate.'], 500);
        }
    }
    
    
    public function destroy($id)
    {
        $candidate = Candidate::find($id);

        if (!$candidate) {
            return response()->json(['success' => false, 'message' => 'Candidate not found.']);
        }

        $candidate->delete();

        return response()->json(['success' => true, 'message' => 'Candidate deleted successfully!']);
    }

    public function searchStudents(Request $request)
    {
        $query = $request->get('search', '');

        $students = Student::where('student_id', 'LIKE', "%{$query}%")->get();

        return response()->json($students);
    }
}
