<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;
use App\Models\Student;
use Illuminate\Http\Request;

class StudentController extends Controller
{
    public function index(Request $request)
    {
        $limit = $request->input('limit', 10); // Default to 10
        $search = $request->input('search'); // Get the search query

        // Modify the query to filter by student_id, email, program, faculty, or status
        $students = Student::when($search, function ($query, $search) {
            return $query->where('student_id', 'like', "%$search%")
                        ->orWhere('fullname', 'like', "%$search%")
                        ->orWhere('school_email', 'like', "%$search%")
                        ->orWhere('faculty', 'like', "%$search%")
                        ->orWhere('program', 'like', "%$search%")
                        ->orWhere('status', 'like', "%$search%");
        })->paginate($limit);

        $faculties = ['FaCET', 'FALS', 'FBM', 'FNAHS', 'FTED', 'FCGE'];

        return view('students.index', compact('students', 'faculties'));
    }



    public function create()
    {
        $faculties = ['FaCET', 'FALS', 'FBM', 'FNAHS', 'FTED', 'FCGE']; // List of faculties
        return view('students.create', compact('faculties')); // Pass $faculties to the view
    }

    public function store(Request $request)
    {
        // Validate and create the student
        $validatedData = $request->validate([
            'student_id' => 'required|string|max:10',
            'fullname' => 'required|string|max:255',
            'school_email' => 'required|email|unique:students',
            'faculty' => 'required|string',
            'program' => 'required|string',
        ]);

        // Set the status to "active" by default
        $validatedData['status'] = 'active';

        // Create the student with the validated data
        Student::create($validatedData);

        // Return JSON response for AJAX requests
        return response()->json(['success' => true, 'message' => 'Student added successfully!']);
    }


    public function edit($student_id)
    {
        $student = Student::findOrFail($student_id);
        return view('students.edit', compact('student'));
    }

    public function update(Request $request, $student_id)
{
    $student = Student::findOrFail($student_id); // Fetch original student

    $validatedData = $request->validate([
        'student_id' => 'required|string|unique:students,student_id,' . $student_id . ',student_id',
        'fullname' => 'required|string|max:255',
        'school_email' => 'required|email|unique:students,school_email,' . $student_id . ',student_id',
        'faculty' => 'required|string',
        'program' => 'required|string',
        'status' => 'required|in:active,inactive',
    ]);

    // Handle student_id change
    if ($validatedData['student_id'] !== $student_id) {
        $student->student_id = $validatedData['student_id'];
    }

    // Update other fields
    $student->update($validatedData);

    return response()->json(['success' => true, 'message' => 'Student updated successfully!']);
}

    
    public function destroy($student_id)
    {
        try {
            $student = Student::findOrFail($student_id);
            $student->delete();

            // Return JSON response for AJAX requests
            return response()->json(['success' => true, 'message' => 'Student deleted successfully!']);
        } catch (\Exception $e) {
            Log::error('Error deleting student: ' . $e->getMessage());

            // Return JSON response for AJAX requests
            return response()->json(['success' => false, 'message' => 'Failed to delete student.'], 500);
        }
    }
    
}


