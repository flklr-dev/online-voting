<?php

namespace App\Http\Controllers;

use App\Models\Partylist;
use Illuminate\Http\Request;

class PartylistController extends Controller
{
    public function store(Request $request)
    {
        try {
            $validatedData = $request->validate([
                'name' => 'required|string|max:100|unique:partylists'
            ]);

            $partylist = Partylist::create($validatedData);

            return response()->json([
                'success' => true,
                'message' => 'Partylist added successfully!',
                'partylist' => $partylist
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }

    public function destroy($id)
    {
        try {
            $partylist = Partylist::findOrFail($id);
            
            // Check if partylist is being used by any candidates
            if ($partylist->candidates()->count() > 0) {
                return response()->json([
                    'success' => false,
                    'message' => 'Cannot delete partylist as it is being used by candidates.'
                ], 400);
            }

            $partylist->delete();

            return response()->json([
                'success' => true,
                'message' => 'Partylist deleted successfully!'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }
} 