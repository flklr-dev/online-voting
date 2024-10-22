<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Admin;
use Illuminate\Support\Facades\Crypt; // For encryption and decryption

class AdminController extends Controller
{
    public function index(Request $request)
    {
        $search = $request->input('search');
        $admins = Admin::when($search, function ($query, $search) {
            return $query->where('admin_id', 'like', "%$search%")
                         ->orWhere('username', 'like', "%$search%");
        })->get();

        return view('admin.index', compact('admins'));
    }

    public function store(Request $request)
    {
        $validatedData = $request->validate([
            'username' => 'required|string|max:255|unique:admins',
            'password' => 'required|string|min:6',
        ]);

        Admin::create([
            'username' => $validatedData['username'],
            'password' => Crypt::encrypt($validatedData['password']),
        ]);

        return response()->json(['success' => true, 'message' => 'Admin added successfully'], 200);
    }

    public function edit($id)
    {
        $admin = Admin::findOrFail($id);
        $admin->password = Crypt::decrypt($admin->password); // Decrypt password for the edit modal

        return response()->json($admin);
    }

    public function update(Request $request, $id)
    {
        $admin = Admin::findOrFail($id);

        $validatedData = $request->validate([
            'username' => 'required|string|max:255|unique:admins,username,'.$admin->admin_id.',admin_id',
            'password' => 'required|string|min:6',
        ]);

        $admin->update([
            'username' => $validatedData['username'],
            'password' => Crypt::encrypt($validatedData['password']),
        ]);

        return response()->json(['success' => true, 'message' => 'Admin updated successfully']);
    }

    public function destroy($id)
    {
        $admin = Admin::findOrFail($id);
        $admin->delete();

        return response()->json(['success' => true]);
    }
}
