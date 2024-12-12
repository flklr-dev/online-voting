<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;
use App\Models\Position;
use Illuminate\Http\Request;
use App\Services\ActivityLogService;

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
        try {
            $validatedData = $request->validate([
                'position_name' => 'required|string|max:255',
                'max_vote' => 'required|integer|min:1',
            ]);
        
            $position = Position::create($validatedData);

            // Log the activity
            ActivityLogService::log(
                'create',
                'Positions',
                "Created new position: {$position->position_name} (Max votes: {$position->max_vote})"
            );
        
            return response()->json(['success' => true, 'message' => 'Position added successfully!']);
        } catch (\Exception $e) {
            Log::error('Error creating position: ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'Failed to create position.'], 500);
        }
    }

    public function edit($position_id)
    {
        $position = Position::findOrFail($position_id);
        return view('positions.edit', compact('position'));
    }

    public function update(Request $request, $id)
    {
        try {
            $position = Position::findOrFail($id);
            $oldData = $position->toArray(); // Store old data for logging

            $validatedData = $request->validate([
                'position_name' => 'required|string|max:255',
                'max_vote' => 'required|integer|min:1',
            ]);
        
            $position->update($validatedData);

            // Log the activity with changes
            $changes = [];
            foreach ($validatedData as $key => $value) {
                if ($oldData[$key] != $value) {
                    $changes[] = "$key: {$oldData[$key]} → $value";
                }
            }
            
            if (!empty($changes)) {
                ActivityLogService::log(
                    'update',
                    'Positions',
                    "Updated position: {$position->position_name}. Changes: " . implode(', ', $changes)
                );
            }
        
            return response()->json(['success' => true, 'message' => 'Position updated successfully!']);
        } catch (\Exception $e) {
            Log::error('Error updating position: ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'Failed to update position.'], 500);
        }
    }
    
    public function destroy($id)
    {
        try {
            $position = Position::findOrFail($id);
            $positionName = $position->position_name; // Store name before deletion
            
            $position->delete();

            // Log the activity
            ActivityLogService::log(
                'delete',
                'Positions',
                "Deleted position: {$positionName}"
            );

            return response()->json(['success' => true, 'message' => 'Position deleted successfully!']);
        } catch (\Exception $e) {
            Log::error('Error deleting position: ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'Failed to delete position.'], 500);
        }
    }
}
