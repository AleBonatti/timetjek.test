<?php

namespace App\Actions\TimeEntry;

use App\Models\TimeEntry;
use App\Models\User;
use Symfony\Component\HttpKernel\Exception\HttpException;

class ClockInAction
{
    public function execute(User $user, ?float $latitude, ?float $longitude): TimeEntry
    {
        $hasOpenEntry = TimeEntry::where('user_id', $user->id)
            ->whereNull('clock_out')
            ->exists();

        if ($hasOpenEntry) {
            throw new HttpException(409, 'You already have an open time entry.');
        }

        return TimeEntry::create([
            'user_id' => $user->id,
            'clock_in' => now(),
            'clock_in_latitude' => $latitude,
            'clock_in_longitude' => $longitude,
        ]);
    }
}
