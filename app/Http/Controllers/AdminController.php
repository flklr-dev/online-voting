<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Admin;
use Illuminate\Support\Facades\Crypt; // For encryption and decryption

class AdminController extends Controller
{
    public function index(Request $request)
    {
        $limit = $request->input('limit', 10);
        $search = $request->input('search');
        $admins = Admin::when($search, function ($query, $search) {
            return $query->where('username', 'like', "%$search%")
                            ->orWhere('admin_id', 'like', "%$search%");
        })->paginate($limit);
    
        return view('admin.index', compact('admins'));
    }
    
    public function store(Request $request)
    {
        $validatedData = $request->validate([
            'username' => 'required|string|max:255|unique:admins',
            'password' => [
                'required',
                'string',
                'min:8',
                'regex:/[a-z]/',      // at least one lowercase letter
                'regex:/[A-Z]/',      // at least one uppercase letter
                'regex:/[0-9]/',      // at least one number
                'regex:/[@$!%*#?&]/', // at least one special character
            ],
        ], [
            'password.regex' => 'Password must contain at least one uppercase letter, one lowercase letter, one number, and one special character.',
        ]);

        $validatedData['password'] = bcrypt($validatedData['password']);
        $admin = Admin::create($validatedData);

        return response()->json([
            'success' => true,
            'message' => 'Admin added successfully!'
        ]);
    }

    public function edit($id)
    {
        $admin = Admin::find($id);
    
        if ($admin) {
            return response()->json([
                'success' => true,
                'admin' => $admin,
            ]);
        } else {
            return response()->json([
                'success' => false,
                'message' => 'Admin not found.',
            ], 404);
        }
    }

    public function update(Request $request, $id)
    {
        $admin = Admin::findOrFail($id);
    
        // Validate the request data
        $validatedData = $request->validate([
            'username' => 'required|string|max:255',
            'password' => 'nullable|string|min:8', // Password is optional, only validate if provided
        ]);
    
        // Update the username
        $admin->username = $validatedData['username'];
    
        // Check if the password was provided and update it
        if (!empty($validatedData['password'])) {
            $admin->password = bcrypt($validatedData['password']); // Hash the new password
        }
    
        $admin->save();
    
        return response()->json([
            'success' => true,
            'message' => 'Admin updated successfully!',
        ]);
    }
    
    
    public function destroy($id)
    {
        $admin = Admin::findOrFail($id);
        $admin->delete();

        return response()->json(['success' => true]);
    }
}
