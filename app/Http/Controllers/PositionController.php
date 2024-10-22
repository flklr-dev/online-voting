<?php

namespace App\Http\Controllers;


use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;
use App\Models\Position;
use Illuminate\Http\Request;
class PositionController extends Controller
{
    public function index(Request $request)
    {
        $limit = $request->input('limit', 10);
        $search = $request->input('search');

        $positions = Position::when($search, function ($query, $search){
            return $query->where('position_id', 'like', "%$search%")
                        ->orWhere('position_name', 'like', "%$search%");
        })->paginate($limit);

        return view('positions.index', compact('positions'));
        
    }

    public function store(Request $request)
    {
        $validatedData = $request->validate([
            'position_name' => 'required|string|max:255',
            'max_vote' => 'required|string',
        ]);

        Position::create($validatedData);

        // Return JSON response for AJAX requests
        return response()->json(['success' => true, 'message' => 'Position added successfully!']);
    }

    public function edit($position_id)
    {
        $position = Position::findOrFail($position_id);
        return view('positions.edit', compact('position'));
    }

    public function update(Request $request, $id)
    {
        $position = Position::findOrFail($id);
        $validatedData = $request->validate([
            'position_name' => 'required|string|max:255',
            'max_vote' => 'required|integer|min:1',
        ]);
    
        $position->update($validatedData);
        return response()->json(['success' => true, 'message' => 'Position updated successfully!']);
    }
    
    public function destroy($id)
    {
        $position = Position::findOrFail($id);
        $position->delete();
        return response()->json(['success' => true, 'message' => 'Position deleted successfully!']);
    }
}
