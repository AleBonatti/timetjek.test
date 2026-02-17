<?php

namespace App\Actions\TimeEntry;

use App\Models\TimeEntry;
use Carbon\Carbon;
use Illuminate\Validation\ValidationException;

class UpdateTimeEntryAction
{
    public const EARLIEST_HOUR = 6;
    public const LATEST_HOUR = 23;

    public function execute(TimeEntry $timeEntry, array $validated): TimeEntry
    {
        $clockIn = Carbon::parse($validated['clock_in']);
        $clockOut = isset($validated['clock_out']) ? Carbon::parse($validated['clock_out']) : null;

        $this->validateWorkingHours($clockIn, $clockOut);
        $this->validateOpenEntryIsLatest($timeEntry, $clockIn, $clockOut);
        $this->validateNoOverlap($timeEntry, $clockIn, $clockOut);

        $timeEntry->update([
            'clock_in' => $clockIn,
            'clock_out' => $clockOut,
            'notes' => $validated['notes'] ?? null,
        ]);

        return $timeEntry->fresh();
    }

    private function validateWorkingHours(Carbon $clockIn, ?Carbon $clockOut): void
    {
        if ($this->isOutsideWorkingHours($clockIn)) {
            throw ValidationException::withMessages([
                'clock_in' => ['Clock in time must be between 6:00 AM and 11:00 PM'],
            ]);
        }

        if ($clockOut && $this->isOutsideWorkingHours($clockOut)) {
            throw ValidationException::withMessages([
                'clock_out' => ['Clock out time must be between 6:00 AM and 11:00 PM'],
            ]);
        }
    }

    private function isOutsideWorkingHours(Carbon $time): bool
    {
        return $time->hour < self::EARLIEST_HOUR
            || ($time->hour === self::LATEST_HOUR && $time->minute > 0)
            || $time->hour > self::LATEST_HOUR;
    }

    private function validateOpenEntryIsLatest(TimeEntry $timeEntry, Carbon $clockIn, ?Carbon $clockOut): void
    {
        if ($clockOut) {
            return;
        }

        $latestEntryForDay = TimeEntry::where('user_id', $timeEntry->user_id)
            ->whereDate('clock_in', $clockIn->toDateString())
            ->where('id', '!=', $timeEntry->id)
            ->orderBy('clock_in', 'desc')
            ->first();

        if ($latestEntryForDay && $latestEntryForDay->clock_in > $clockIn) {
            throw ValidationException::withMessages([
                'clock_out' => ['Only the latest entry for a day can be left open'],
            ]);
        }
    }

    private function validateNoOverlap(TimeEntry $timeEntry, Carbon $clockIn, ?Carbon $clockOut): void
    {
        if (!$clockOut) {
            return;
        }

        // Use a temporary unsaved instance to delegate to the model's overlaps() method,
        // keeping overlap logic in a single place.
        $candidate = new TimeEntry(['clock_in' => $clockIn, 'clock_out' => $clockOut]);

        $existingEntries = TimeEntry::where('user_id', $timeEntry->user_id)
            ->whereDate('clock_in', $clockIn->toDateString())
            ->where('id', '!=', $timeEntry->id)
            ->whereNotNull('clock_out')
            ->get();

        $hasOverlap = $existingEntries->contains(fn (TimeEntry $existing) => $candidate->overlaps($existing));

        if ($hasOverlap) {
            throw ValidationException::withMessages([
                'clock_out' => ['This time entry overlaps with another entry for the same day'],
            ]);
        }
    }
}
