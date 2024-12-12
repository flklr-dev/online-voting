<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Admin;
use Illuminate\Support\Facades\Crypt;
use App\Services\ActivityLogService;
use Illuminate\Support\Facades\Log;

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
        try {
            $validatedData = $request->validate([
                'username' => 'required|string|max:255|unique:admins',
                'password' => [
                    'required',
                    'string',
                    'min:8',
                    'regex:/[a-z]/',
                    'regex:/[A-Z]/',
                    'regex:/[0-9]/',
                    'regex:/[@$!%*#?&]/',
                ],
            ], [
                'password.regex' => 'Password must contain at least one uppercase letter, one lowercase letter, one number, and one special character.',
            ]);

            $validatedData['password'] = bcrypt($validatedData['password']);
            $admin = Admin::create($validatedData);

            // Log the activity
            ActivityLogService::log(
                'create',
                'Admins',
                "Created new admin account: {$admin->username}"
            );

            return response()->json([
                'success' => true,
                'message' => 'Admin added successfully!'
            ]);
        } catch (\Exception $e) {
            Log::error('Error creating admin: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to create admin account.'
            ], 500);
        }
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
        try {
            $admin = Admin::findOrFail($id);
            $oldUsername = $admin->username; // Store old username for logging
        
            $validatedData = $request->validate([
                'username' => 'required|string|max:255',
                'password' => 'nullable|string|min:8',
            ]);
        
            // Update the username
            $admin->username = $validatedData['username'];
        
            // Prepare changes log
            $changes = [];
            if ($oldUsername !== $validatedData['username']) {
                $changes[] = "username: {$oldUsername} → {$validatedData['username']}";
            }
            
            // Check if the password was provided and update it
            if (!empty($validatedData['password'])) {
                $admin->password = bcrypt($validatedData['password']);
                $changes[] = "password updated";
            }
        
            $admin->save();

            // Log the activity if there were changes
            if (!empty($changes)) {
                ActivityLogService::log(
                    'update',
                    'Admins',
                    "Updated admin account: {$admin->username}. Changes: " . implode(', ', $changes)
                );
            }
        
            return response()->json([
                'success' => true,
                'message' => 'Admin updated successfully!',
            ]);
        } catch (\Exception $e) {
            Log::error('Error updating admin: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to update admin account.'
            ], 500);
        }
    }
    
    public function destroy($id)
    {
        try {
            $admin = Admin::findOrFail($id);
            $username = $admin->username; // Store username before deletion
            
            $admin->delete();

            // Log the activity
            ActivityLogService::log(
                'delete',
                'Admins',
                "Deleted admin account: {$username}"
            );

            return response()->json(['success' => true]);
        } catch (\Exception $e) {
            Log::error('Error deleting admin: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to delete admin account.'
            ], 500);
        }
    }
}
