<?php

// app/Http/Controllers/ActivityLogController.php

namespace App\Http\Controllers;

use App\Models\ActivityLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class ActivityLogController extends Controller
{
    public function index(Request $request)
    {
        try {
            $limit = $request->input('limit', 10); // Default to 10
            $search = $request->input('search');
            $date = $request->input('date');

            $query = ActivityLog::with('admin')->latest();
            
            // Search functionality
            if ($search) {
                $query->where(function($q) use ($search) {
                    $q->where('action', 'like', "%{$search}%")
                      ->orWhere('module', 'like', "%{$search}%")
                      ->orWhere('description', 'like', "%{$search}%");
                });
            }

            // Date filter
            if ($date) {
                $query->whereDate('created_at', $date);
            }

            $logs = $query->paginate($limit)->withQueryString();
            
            return view('admin.activity-logs.index', compact('logs'));
            
        } catch (\Exception $e) {
            Log::error('Activity Log Error: ' . $e->getMessage());
            return back()->with('error', 'An error occurred while fetching activity logs.');
        }
    }
}