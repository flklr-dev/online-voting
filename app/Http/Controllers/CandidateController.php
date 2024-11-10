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
        try {
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
        } catch (\Illuminate\Validation\ValidationException $e) {
            // Log validation errors
            return response()->json(['success' => false, 'message' => $e->errors()], 400);
        } catch (\Exception $e) {
            // Log any other errors
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }
    
    public function update(Request $request, $id)
    {
        try {
            $candidate = Candidate::findOrFail($id);

            $validatedData = $request->validate([
                'student_id' => 'required|exists:students,student_id',
                'election_id' => 'required|exists:elections,election_id',
                'position_id' => 'required|exists:positions,position_id',
                'student_name' => 'required|string|max:255',
                'campaign_statement' => 'nullable|string',
                'partylist' => 'nullable|string|max:100',
                'picture' => 'nullable|image|max:2048',
            ]);

            if ($request->hasFile('picture')) {
                $fileName = time() . '.' . $request->file('picture')->extension();
                $request->file('picture')->move(public_path('images/candidates'), $fileName);
                $validatedData['picture'] = $fileName;
            }

            $candidate->update($validatedData);

            return response()->json(['success' => true, 'message' => 'Candidate updated successfully!']);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
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
}
