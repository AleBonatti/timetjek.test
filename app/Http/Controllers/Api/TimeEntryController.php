<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\TimeEntry;
use Illuminate\Http\Request;

class TimeEntryController extends Controller
{
    /**
     * Get today's time entries for the authenticated user.
     */
    public function today(Request $request)
    {
        $timeEntries = TimeEntry::where('user_id', $request->user()->id)
            ->whereDate('clock_in', today())
            ->orderBy('clock_in', 'desc')
            ->get();

        return response()->json([
            'time_entries' => $timeEntries,
        ]);
    }

    /**
     * Clock in.
     */
    public function clockIn(Request $request)
    {
        $timeEntry = TimeEntry::create([
            'user_id' => $request->user()->id,
            'clock_in' => now()->startOfMinute(),
        ]);

        return response()->json([
            'time_entry' => $timeEntry,
            'message' => 'Clocked in successfully',
        ], 201);
    }

    /**
     * Clock out.
     */
    public function clockOut(Request $request)
    {
        // Find the latest open time entry for today
        $timeEntry = TimeEntry::where('user_id', $request->user()->id)
            ->whereDate('clock_in', today())
            ->whereNull('clock_out')
            ->orderBy('clock_in', 'desc')
            ->first();

        if (!$timeEntry) {
            return response()->json([
                'message' => 'No open time entry found',
            ], 404);
        }

        $timeEntry->update([
            'clock_out' => now()->startOfMinute(),
        ]);

        return response()->json([
            'time_entry' => $timeEntry,
            'message' => 'Clocked out successfully',
        ]);
    }
}
