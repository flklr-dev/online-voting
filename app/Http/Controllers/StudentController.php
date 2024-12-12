<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;
use App\Models\Student;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash; 
use Illuminate\Support\Facades\DB;
use App\Services\ActivityLogService;


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

        $faculties = ['FaCET', 'FALS', 'FBM', 'FNAHS', 'FTED', 'FCGE', 'FHUSOCOM'];
        return view('students.index', compact('students', 'faculties'));
    }

    public function create()
    {
        $faculties = ['FaCET', 'FALS', 'FBM', 'FNAHS', 'FTED', 'FCGE', 'FHUSOCOM']; // List of faculties
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

        $student = Student::create([
            'student_id' => $validatedData['student_id'],
            'fullname' => $validatedData['fullname'],
            'school_email' => $validatedData['school_email'],
            'username' => $validatedData['school_email'],
            'password' => bcrypt($validatedData['student_id']),
            'faculty' => $validatedData['faculty'],
            'program' => $validatedData['program'],
            'status' => 'active',
        ]);

        // Log the activity
        ActivityLogService::log(
            'create',
            'Students',
            "Added new student: {$student->fullname} (ID: {$student->student_id})"
        );

        return response()->json(['success' => true, 'message' => 'Student added successfully!', 'student' => $student]);
    }

    public function edit($student_id)
    {
        $student = Student::findOrFail($student_id);
        return view('students.edit', compact('student'));
    }


    public function update(Request $request, $student_id)
    {
        try {
            $student = Student::findOrFail($student_id);
            $oldData = $student->toArray(); // Store old data for logging

            $validatedData = $request->validate([
                'fullname' => 'required|string|max:255',
                'school_email' => 'required|email|unique:students,school_email,' . $student_id . ',student_id',
                'student_id' => 'required|string|unique:students,student_id,' . $student_id . ',student_id',
                'faculty' => 'required|string',
                'program' => 'required|string',
                'status' => 'required|in:active,inactive',
            ]);

            $student->update([
                'student_id' => $validatedData['student_id'],
                'fullname' => $validatedData['fullname'],
                'school_email' => $validatedData['school_email'],
                'username' => $validatedData['school_email'],
                'faculty' => $validatedData['faculty'],
                'program' => $validatedData['program'],
                'status' => $validatedData['status'],
            ]);

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
                    'Students',
                    "Updated student: {$student->fullname} (ID: {$student->student_id}). Changes: " . implode(', ', $changes)
                );
            }

            return response()->json([
                'success' => true, 
                'message' => 'Student updated successfully!',
                'student' => $student
            ]);

        } catch (ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to update student.'
            ], 500);
        }
    }

    public function destroy($student_id)
    {
        try {
            $student = Student::findOrFail($student_id);
            $studentName = $student->fullname; // Store name before deletion
            $studentId = $student->student_id; // Store ID before deletion
            
            $student->delete();

            // Log the activity
            ActivityLogService::log(
                'delete',
                'Students',
                "Deleted student: {$studentName} (ID: {$studentId})"
            );

            return response()->json(['success' => true, 'message' => 'Student deleted successfully!']);
        } catch (\Exception $e) {
            Log::error('Error deleting student: ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'Failed to delete student.'], 500);
        }
    }
    
}


