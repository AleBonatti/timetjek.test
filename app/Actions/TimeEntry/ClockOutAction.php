<?php

namespace App\Actions\TimeEntry;

use App\Models\TimeEntry;
use App\Models\User;

class ClockOutAction
{
    public function execute(User $user, ?float $latitude, ?float $longitude): ?TimeEntry
    {
        $timeEntry = TimeEntry::where('user_id', $user->id)
            ->whereDate('clock_in', today())
            ->whereNull('clock_out')
            ->orderBy('clock_in', 'desc')
            ->first();

        if (!$timeEntry) {
            return null;
        }

        $timeEntry->update([
            'clock_out' => now(),
            'clock_out_latitude' => $latitude,
            'clock_out_longitude' => $longitude,
        ]);

        return $timeEntry;
    }
}
