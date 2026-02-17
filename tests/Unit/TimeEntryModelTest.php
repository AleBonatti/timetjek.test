<?php

namespace Tests\Unit;

use App\Models\TimeEntry;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TimeEntryModelTest extends TestCase
{
    use RefreshDatabase;

    // -------------------------------------------------------------------------
    // isActive()
    // -------------------------------------------------------------------------

    public function test_is_active_returns_true_when_no_clock_out(): void
    {
        $entry = TimeEntry::factory()->open()->create();

        $this->assertTrue($entry->isActive());
    }

    public function test_is_active_returns_false_when_clock_out_is_set(): void
    {
        $entry = TimeEntry::factory()->create();

        $this->assertFalse($entry->isActive());
    }

    // -------------------------------------------------------------------------
    // getDurationInMinutes()
    // -------------------------------------------------------------------------

    public function test_get_duration_in_minutes_returns_null_for_open_entry(): void
    {
        $entry = TimeEntry::factory()->open()->create();

        $this->assertNull($entry->getDurationInMinutes());
    }

    public function test_get_duration_in_minutes_returns_correct_value(): void
    {
        $entry = TimeEntry::factory()->create([
            'clock_in' => Carbon::today()->setTime(9, 0),
            'clock_out' => Carbon::today()->setTime(10, 30),
        ]);

        $this->assertEquals(90, $entry->getDurationInMinutes());
    }

    // -------------------------------------------------------------------------
    // getFormattedDuration()
    // -------------------------------------------------------------------------

    public function test_get_formatted_duration_returns_null_for_open_entry(): void
    {
        $entry = TimeEntry::factory()->open()->create();

        $this->assertNull($entry->getFormattedDuration());
    }

    public function test_get_formatted_duration_formats_correctly(): void
    {
        $entry = TimeEntry::factory()->create([
            'clock_in' => Carbon::today()->setTime(9, 0),
            'clock_out' => Carbon::today()->setTime(10, 30),
        ]);

        $this->assertEquals('1h 30m', $entry->getFormattedDuration());
    }

    public function test_get_formatted_duration_handles_whole_hours(): void
    {
        $entry = TimeEntry::factory()->create([
            'clock_in' => Carbon::today()->setTime(9, 0),
            'clock_out' => Carbon::today()->setTime(11, 0),
        ]);

        $this->assertEquals('2h 0m', $entry->getFormattedDuration());
    }

    // -------------------------------------------------------------------------
    // overlaps()
    // -------------------------------------------------------------------------

    public function test_overlaps_returns_false_when_this_entry_is_open(): void
    {
        $open = TimeEntry::factory()->open()->create([
            'clock_in' => Carbon::today()->setTime(9, 0),
        ]);
        $other = TimeEntry::factory()->create([
            'clock_in' => Carbon::today()->setTime(9, 0),
            'clock_out' => Carbon::today()->setTime(10, 0),
        ]);

        $this->assertFalse($open->overlaps($other));
    }

    public function test_overlaps_returns_false_when_other_entry_is_open(): void
    {
        $entry = TimeEntry::factory()->create([
            'clock_in' => Carbon::today()->setTime(9, 0),
            'clock_out' => Carbon::today()->setTime(10, 0),
        ]);
        $open = TimeEntry::factory()->open()->create([
            'clock_in' => Carbon::today()->setTime(9, 30),
        ]);

        $this->assertFalse($entry->overlaps($open));
    }

    public function test_overlaps_returns_true_when_ranges_overlap(): void
    {
        $entry = TimeEntry::factory()->create([
            'clock_in' => Carbon::today()->setTime(9, 0),
            'clock_out' => Carbon::today()->setTime(11, 0),
        ]);
        $other = TimeEntry::factory()->create([
            'clock_in' => Carbon::today()->setTime(10, 0),
            'clock_out' => Carbon::today()->setTime(12, 0),
        ]);

        $this->assertTrue($entry->overlaps($other));
    }

    public function test_overlaps_returns_false_when_ranges_do_not_overlap(): void
    {
        $entry = TimeEntry::factory()->create([
            'clock_in' => Carbon::today()->setTime(9, 0),
            'clock_out' => Carbon::today()->setTime(10, 0),
        ]);
        $other = TimeEntry::factory()->create([
            'clock_in' => Carbon::today()->setTime(11, 0),
            'clock_out' => Carbon::today()->setTime(12, 0),
        ]);

        $this->assertFalse($entry->overlaps($other));
    }

    public function test_overlaps_returns_false_for_adjacent_entries(): void
    {
        $entry = TimeEntry::factory()->create([
            'clock_in' => Carbon::today()->setTime(9, 0),
            'clock_out' => Carbon::today()->setTime(10, 0),
        ]);
        $other = TimeEntry::factory()->create([
            'clock_in' => Carbon::today()->setTime(10, 0),
            'clock_out' => Carbon::today()->setTime(11, 0),
        ]);

        $this->assertFalse($entry->overlaps($other));
    }
}
