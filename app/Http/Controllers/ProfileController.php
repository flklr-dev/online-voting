<?php
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Student;
use Illuminate\Support\Facades\Hash;

class ProfileController extends Controller
{
    public function index()
    {
        $student = auth('student')->user();
        $faculties = ['FaCET', 'FALS', 'FBM', 'FNAHS', 'FTED', 'FCGE', 'FHUSOCOM'];
        $programs = $this->getProgramsByFaculty($student->faculty);
        
        return view('profile.index', compact('student', 'faculties', 'programs'));
    }

    public function update(Request $request)
    {
        $student = auth('student')->user();

        $request->validate([
            'faculty' => 'required|string',
            'program' => 'required|string',
            'current_password' => 'required_with:new_password',
            'new_password' => 'nullable|min:8|confirmed'
        ]);

        // Update faculty and program
        $student->faculty = $request->faculty;
        $student->program = $request->program;

        // Handle password change
        if ($request->filled('current_password')) {
            if (!Hash::check($request->current_password, $student->password)) {
                return back()->withErrors(['current_password' => 'The current password is incorrect.']);
            }
            $student->password = Hash::make($request->new_password);
        }

        $student->save();

        return redirect()->route('profile.index')->with('success', 'Profile updated successfully!');
    }

    public function getPrograms(Request $request)
    {
        $faculty = $request->input('faculty');
        $programs = $this->getProgramsByFaculty($faculty);
        return response()->json($programs);
    }

    private function getProgramsByFaculty($faculty)
    {
        $programsByFaculty = [
            'FaCET' => ['BSIT', 'BSCpE', 'BSEE', 'BSCE'],
            'FALS' => ['BSBio', 'BSMath', 'BAPolSci'],
            'FBM' => ['BSBA', 'BSA'],
            'FNAHS' => ['BSN'],
            'FTED' => ['BEED', 'BSED'],
            'FCGE' => ['BSPsych'],
            'FHUSOCOM' => ['BAComm', 'BAEnglish']
        ];

        return $programsByFaculty[$faculty] ?? [];
    }
}
