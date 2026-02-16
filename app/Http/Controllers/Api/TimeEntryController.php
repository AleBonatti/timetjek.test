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
        $validated = $request->validate([
            'latitude' => 'nullable|numeric|between:-90,90',
            'longitude' => 'nullable|numeric|between:-180,180',
        ]);

        $timeEntry = TimeEntry::create([
            'user_id' => $request->user()->id,
            'clock_in' => now(),
            'clock_in_latitude' => $validated['latitude'] ?? null,
            'clock_in_longitude' => $validated['longitude'] ?? null,
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
        $validated = $request->validate([
            'latitude' => 'nullable|numeric|between:-90,90',
            'longitude' => 'nullable|numeric|between:-180,180',
        ]);

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
            'clock_out' => now(),
            'clock_out_latitude' => $validated['latitude'] ?? null,
            'clock_out_longitude' => $validated['longitude'] ?? null,
        ]);

        return response()->json([
            'time_entry' => $timeEntry,
            'message' => 'Clocked out successfully',
        ]);
    }

    /**
     * Get time entries for current week (Monday to Sunday).
     */
    public function currentWeek(Request $request)
    {
        $startOfWeek = now()->startOfWeek();
        $endOfWeek = now()->endOfWeek();

        $timeEntries = TimeEntry::where('user_id', $request->user()->id)
            ->whereBetween('clock_in', [$startOfWeek, $endOfWeek])
            ->orderBy('clock_in', 'desc')
            ->get();

        return response()->json([
            'time_entries' => $timeEntries,
        ]);
    }

    /**
     * Get time entries for current month.
     */
    public function currentMonth(Request $request)
    {
        $startOfMonth = now()->startOfMonth();
        $endOfMonth = now()->endOfMonth();

        $timeEntries = TimeEntry::where('user_id', $request->user()->id)
            ->whereBetween('clock_in', [$startOfMonth, $endOfMonth])
            ->orderBy('clock_in', 'desc')
            ->get();

        return response()->json([
            'time_entries' => $timeEntries,
        ]);
    }

    /**
     * Get time entries for a custom date range.
     */
    public function dateRange(Request $request)
    {
        $validated = $request->validate([
            'from' => 'required|date',
            'to' => 'required|date|after_or_equal:from',
        ]);

        $from = \Carbon\Carbon::parse($validated['from'])->startOfDay();
        $to = \Carbon\Carbon::parse($validated['to'])->endOfDay();

        $timeEntries = TimeEntry::where('user_id', $request->user()->id)
            ->whereBetween('clock_in', [$from, $to])
            ->orderBy('clock_in', 'desc')
            ->get();

        return response()->json([
            'time_entries' => $timeEntries,
        ]);
    }

    /**
     * Update a time entry.
     */
    public function update(Request $request, TimeEntry $timeEntry)
    {
        // Ensure the time entry belongs to the authenticated user
        if ($timeEntry->user_id !== $request->user()->id) {
            return response()->json([
                'message' => 'Unauthorized',
            ], 403);
        }

        $validated = $request->validate([
            'clock_in' => 'required|date|before_or_equal:now',
            'clock_out' => 'nullable|date|after:clock_in|before_or_equal:now',
            'notes' => 'nullable|string|max:1000',
        ]);

        // Parse the times
        $clockIn = \Carbon\Carbon::parse($validated['clock_in']);
        $clockOut = isset($validated['clock_out']) ? \Carbon\Carbon::parse($validated['clock_out']) : null;

        // Validate time between 6am and 11pm (23:00 is the cutoff, so hour must be < 23)
        if ($clockIn->hour < 6 || ($clockIn->hour == 23 && $clockIn->minute > 0) || $clockIn->hour > 23) {
            return response()->json([
                'message' => 'Clock in time must be between 6:00 AM and 11:00 PM',
                'errors' => [
                    'clock_in' => ['Clock in time must be between 6:00 AM and 11:00 PM'],
                ],
            ], 422);
        }

        if ($clockOut && ($clockOut->hour < 6 || ($clockOut->hour == 23 && $clockOut->minute > 0) || $clockOut->hour > 23)) {
            return response()->json([
                'message' => 'Clock out time must be between 6:00 AM and 11:00 PM',
                'errors' => [
                    'clock_out' => ['Clock out time must be between 6:00 AM and 11:00 PM'],
                ],
            ], 422);
        }

        // If clock_out is empty, check if this is the last entry for that day
        if (!$clockOut) {
            $latestEntryForDay = TimeEntry::where('user_id', $request->user()->id)
                ->whereDate('clock_in', $clockIn->toDateString())
                ->where('id', '!=', $timeEntry->id)
                ->orderBy('clock_in', 'desc')
                ->first();

            if ($latestEntryForDay && $latestEntryForDay->clock_in > $clockIn) {
                return response()->json([
                    'message' => 'Cannot have an open entry that is not the latest for the day',
                    'errors' => [
                        'clock_out' => ['Only the latest entry for a day can be left open'],
                    ],
                ], 422);
            }
        }

        // Check that entries do not overlap for the same day
        if ($clockOut) {
            $overlappingEntry = TimeEntry::where('user_id', $request->user()->id)
                ->whereDate('clock_in', $clockIn->toDateString())
                ->where('id', '!=', $timeEntry->id)
                ->whereNotNull('clock_out')
                ->where(function ($query) use ($clockIn, $clockOut) {
                    $query->where(function ($q) use ($clockIn, $clockOut) {
                        // New entry starts during existing entry
                        $q->where('clock_in', '<=', $clockIn)
                          ->where('clock_out', '>', $clockIn);
                    })->orWhere(function ($q) use ($clockIn, $clockOut) {
                        // New entry ends during existing entry
                        $q->where('clock_in', '<', $clockOut)
                          ->where('clock_out', '>=', $clockOut);
                    })->orWhere(function ($q) use ($clockIn, $clockOut) {
                        // New entry completely contains existing entry
                        $q->where('clock_in', '>=', $clockIn)
                          ->where('clock_out', '<=', $clockOut);
                    });
                })
                ->first();

            if ($overlappingEntry) {
                return response()->json([
                    'message' => 'This time entry overlaps with another entry',
                    'errors' => [
                        'clock_out' => ['This time entry overlaps with another entry for the same day'],
                    ],
                ], 422);
            }
        }

        // Update the time entry
        $timeEntry->update([
            'clock_in' => $clockIn,
            'clock_out' => $clockOut,
            'notes' => $validated['notes'] ?? null,
        ]);

        return response()->json([
            'time_entry' => $timeEntry->fresh(),
            'message' => 'Time entry updated successfully',
        ]);
    }

    /**
     * Delete a time entry.
     */
    public function destroy(Request $request, TimeEntry $timeEntry)
    {
        // Ensure the time entry belongs to the authenticated user
        if ($timeEntry->user_id !== $request->user()->id) {
            return response()->json([
                'message' => 'Unauthorized',
            ], 403);
        }

        $timeEntry->delete();

        return response()->json([
            'message' => 'Time entry deleted successfully',
        ]);
    }
}
