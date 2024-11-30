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

            Partylist::create($validatedData);

            return response()->json([
                'success' => true,
                'message' => 'Partylist added successfully!'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }
} 