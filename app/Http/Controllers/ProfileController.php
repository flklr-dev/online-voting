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
            'current_password' => 'required',
            'new_password' => 'required|min:8|confirmed'
        ]);

        // Handle password change
        if (!Hash::check($request->current_password, $student->password)) {
            return back()->withErrors(['current_password' => 'The current password is incorrect.']);
        }
        
        $student->password = Hash::make($request->new_password);
        $student->save();

        return redirect()->route('profile.index')->with('success', 'Password updated successfully!');
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
