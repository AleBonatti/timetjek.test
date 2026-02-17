<?php

namespace App\Http\Controllers\Api;

use App\Actions\TimeEntry\ClockInAction;
use App\Actions\TimeEntry\ClockOutAction;
use App\Actions\TimeEntry\DeleteTimeEntryAction;
use App\Actions\TimeEntry\UpdateTimeEntryAction;
use App\Http\Controllers\Controller;
use App\Http\Requests\TimeEntry\ClockInRequest;
use App\Http\Requests\TimeEntry\ClockOutRequest;
use App\Http\Requests\TimeEntry\DateRangeRequest;
use App\Http\Requests\TimeEntry\UpdateTimeEntryRequest;
use App\Models\TimeEntry;
use Carbon\Carbon;
use Illuminate\Http\Request;

class TimeEntryController extends Controller
{
    public function __construct(
        private ClockInAction $clockInAction,
        private ClockOutAction $clockOutAction,
        private UpdateTimeEntryAction $updateTimeEntryAction,
        private DeleteTimeEntryAction $deleteTimeEntryAction,
    ) {}

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
    public function clockIn(ClockInRequest $request)
    {
        $validated = $request->validated();

        $timeEntry = $this->clockInAction->execute(
            $request->user(),
            $validated['latitude'] ?? null,
            $validated['longitude'] ?? null,
        );

        return response()->json([
            'time_entry' => $timeEntry,
            'message' => 'Clocked in successfully',
        ], 201);
    }

    /**
     * Clock out.
     */
    public function clockOut(ClockOutRequest $request)
    {
        $validated = $request->validated();

        $timeEntry = $this->clockOutAction->execute(
            $request->user(),
            $validated['latitude'] ?? null,
            $validated['longitude'] ?? null,
        );

        if (!$timeEntry) {
            return response()->json([
                'message' => 'No open time entry found',
            ], 404);
        }

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
        $timeEntries = TimeEntry::where('user_id', $request->user()->id)
            ->whereBetween('clock_in', [now()->startOfWeek(), now()->endOfWeek()])
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
        $timeEntries = TimeEntry::where('user_id', $request->user()->id)
            ->whereBetween('clock_in', [now()->startOfMonth(), now()->endOfMonth()])
            ->orderBy('clock_in', 'desc')
            ->get();

        return response()->json([
            'time_entries' => $timeEntries,
        ]);
    }

    /**
     * Get time entries for a custom date range.
     */
    public function dateRange(DateRangeRequest $request)
    {
        $validated = $request->validated();

        $from = Carbon::parse($validated['from'])->startOfDay();
        $to = Carbon::parse($validated['to'])->endOfDay();

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
    public function update(UpdateTimeEntryRequest $request, TimeEntry $timeEntry)
    {
        $this->authorize('update', $timeEntry);

        $timeEntry = $this->updateTimeEntryAction->execute($timeEntry, $request->validated());

        return response()->json([
            'time_entry' => $timeEntry,
            'message' => 'Time entry updated successfully',
        ]);
    }

    /**
     * Delete a time entry.
     */
    public function destroy(Request $request, TimeEntry $timeEntry)
    {
        $this->authorize('delete', $timeEntry);

        $this->deleteTimeEntryAction->execute($timeEntry);

        return response()->json([
            'message' => 'Time entry deleted successfully',
        ]);
    }
}
