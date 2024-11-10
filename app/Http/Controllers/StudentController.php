<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;
use App\Models\Student;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash; 
use Illuminate\Support\Facades\DB;


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
        $validatedData = $request->validate([
            'fullname' => 'required|string|max:255',
            'school_email' => 'required|email|unique:students',
            'student_id' => 'required|string|max:10|unique:students',
            'faculty' => 'required|string',
            'program' => 'required|string',
        ]);

        // Set status to 'active' automatically and hash student_id as password
        $student = Student::create([
            'student_id' => $validatedData['student_id'],
            'fullname' => $validatedData['fullname'],
            'school_email' => $validatedData['school_email'],
            'username' => $validatedData['school_email'], // Use school_email as username
            'password' => Hash::make($validatedData['student_id']), // Hash student_id as password
            'faculty' => $validatedData['faculty'],
            'program' => $validatedData['program'],
            'status' => 'active', // Automatically set status to 'active'
        ]);

        return response()->json(['success' => true, 'message' => 'Student added successfully!', 'student' => $student]);
    }

    public function edit($student_id)
    {
        $student = Student::findOrFail($student_id);
        return view('students.edit', compact('student'));
    }


    public function update(Request $request, $student_id)
    {
        $student = Student::findOrFail($student_id);

        $validatedData = $request->validate([
            'fullname' => 'required|string|max:255',
            'school_email' => 'required|email|unique:students,school_email,' . $student_id . ',student_id',
            'student_id' => 'required|string|unique:students,student_id,' . $student_id . ',student_id', // New ID
            'faculty' => 'required|string',
            'program' => 'required|string',
            'status' => 'required|in:active,inactive',
        ]);

        DB::beginTransaction(); // Start a transaction

        try {
            // Step 1: Delete the existing record with the current primary key
            $student->delete();

            // Step 2: Create a new record with the updated `student_id`
            $newStudent = Student::create([
                'student_id' => $validatedData['student_id'],
                'fullname' => $validatedData['fullname'],
                'school_email' => $validatedData['school_email'],
                'username' => $validatedData['school_email'],
                'password' => Hash::make($validatedData['student_id']), // Ensure password is hashed
                'faculty' => $validatedData['faculty'],
                'program' => $validatedData['program'],
                'status' => $validatedData['status'],
            ]);

            DB::commit(); // Commit the transaction

            return response()->json(['success' => true, 'message' => 'Student updated successfully!', 'student' => $newStudent]);

        } catch (\Exception $e) {
            DB::rollBack(); // Rollback the transaction if an error occurs

            return response()->json(['success' => false, 'message' => 'Failed to update student.'], 500);
        }
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


