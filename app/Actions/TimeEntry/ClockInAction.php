<?php

namespace App\Actions\TimeEntry;

use App\Models\TimeEntry;
use App\Models\User;

class ClockInAction
{
    public function execute(User $user, ?float $latitude, ?float $longitude): TimeEntry
    {
        return TimeEntry::create([
            'user_id' => $user->id,
            'clock_in' => now(),
            'clock_in_latitude' => $latitude,
            'clock_in_longitude' => $longitude,
        ]);
    }
}
