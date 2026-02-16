<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TimeEntry extends Model
{
    protected $fillable = [
        'user_id',
        'clock_in',
        'clock_out',
        'latitude',
        'longitude',
        'notes',
    ];

    protected $casts = [
        'clock_in' => 'datetime',
        'clock_out' => 'datetime',
        'latitude' => 'decimal:8',
        'longitude' => 'decimal:8',
    ];

    /**
     * Get the user that owns the time entry.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Check if the time entry is still active (not clocked out).
     */
    public function isActive(): bool
    {
        return is_null($this->clock_out);
    }

    /**
     * Get the duration of the time entry in minutes.
     */
    public function getDurationInMinutes(): ?int
    {
        if (is_null($this->clock_out)) {
            return null;
        }

        return $this->clock_in->diffInMinutes($this->clock_out);
    }

    /**
     * Get the duration of the time entry as a formatted string.
     */
    public function getFormattedDuration(): ?string
    {
        $minutes = $this->getDurationInMinutes();

        if (is_null($minutes)) {
            return null;
        }

        $hours = floor($minutes / 60);
        $mins = $minutes % 60;

        return sprintf('%dh %dm', $hours, $mins);
    }

    /**
     * Check if this time entry overlaps with another time entry.
     */
    public function overlaps(TimeEntry $other): bool
    {
        // If either entry doesn't have a clock_out, we can't determine overlap
        if (is_null($this->clock_out) || is_null($other->clock_out)) {
            return false;
        }

        // Check if the time ranges overlap
        return $this->clock_in < $other->clock_out && $this->clock_out > $other->clock_in;
    }
}
