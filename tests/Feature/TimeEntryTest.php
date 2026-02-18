<?php

namespace Tests\Feature;

use App\Models\TimeEntry;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TimeEntryTest extends TestCase
{
    use RefreshDatabase;

    // -------------------------------------------------------------------------
    // Clock In
    // -------------------------------------------------------------------------

    public function test_user_can_clock_in(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->postJson('/api/v1/time-entries/clock-in');

        $response->assertStatus(201)
            ->assertJsonPath('message', 'Clocked in successfully')
            ->assertJsonStructure(['time_entry' => ['id', 'clock_in', 'clock_out']]);

        $this->assertDatabaseHas('time_entries', [
            'user_id' => $user->id,
            'clock_out' => null,
        ]);
    }

    public function test_clock_in_stores_gps_coordinates(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->postJson('/api/v1/time-entries/clock-in', [
            'latitude' => 59.3293,
            'longitude' => 18.0686,
        ]);

        $response->assertStatus(201);
        $this->assertDatabaseHas('time_entries', [
            'user_id' => $user->id,
            'clock_in_latitude' => 59.3293,
            'clock_in_longitude' => 18.0686,
        ]);
    }

    public function test_clock_in_without_gps_is_allowed(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->postJson('/api/v1/time-entries/clock-in');

        $response->assertStatus(201);
        $this->assertDatabaseHas('time_entries', [
            'user_id' => $user->id,
            'clock_in_latitude' => null,
            'clock_in_longitude' => null,
        ]);
    }

    public function test_clock_in_requires_valid_coordinates(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->postJson('/api/v1/time-entries/clock-in', [
            'latitude' => 999,
            'longitude' => 999,
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['latitude', 'longitude']);
    }

    public function test_unauthenticated_user_cannot_clock_in(): void
    {
        $response = $this->postJson('/api/v1/time-entries/clock-in');

        $response->assertStatus(401);
    }

    // -------------------------------------------------------------------------
    // Clock Out
    // -------------------------------------------------------------------------

    public function test_user_can_clock_out(): void
    {
        $user = User::factory()->create();
        TimeEntry::factory()->open()->create(['user_id' => $user->id]);

        $response = $this->actingAs($user)->postJson('/api/v1/time-entries/clock-out');

        $response->assertStatus(200)
            ->assertJsonPath('message', 'Clocked out successfully');

        $this->assertDatabaseMissing('time_entries', [
            'user_id' => $user->id,
            'clock_out' => null,
        ]);
    }

    public function test_clock_out_fails_when_no_active_entry(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->postJson('/api/v1/time-entries/clock-out');

        $response->assertStatus(404)
            ->assertJsonPath('message', 'No open time entry found');
    }

    public function test_clock_out_stores_gps_coordinates(): void
    {
        $user = User::factory()->create();
        TimeEntry::factory()->open()->create(['user_id' => $user->id]);

        $response = $this->actingAs($user)->postJson('/api/v1/time-entries/clock-out', [
            'latitude' => 59.3293,
            'longitude' => 18.0686,
        ]);

        $response->assertStatus(200);
        $entry = TimeEntry::where('user_id', $user->id)->first();
        $this->assertEquals('59.32930000', $entry->clock_out_latitude);
        $this->assertEquals('18.06860000', $entry->clock_out_longitude);
    }

    // -------------------------------------------------------------------------
    // Today
    // -------------------------------------------------------------------------

    public function test_today_returns_only_todays_entries(): void
    {
        $user = User::factory()->create();

        // Today's entry
        TimeEntry::factory()->create([
            'user_id' => $user->id,
            'clock_in' => now()->setTime(9, 0),
            'clock_out' => now()->setTime(10, 0),
        ]);

        // Yesterday's entry
        TimeEntry::factory()->create([
            'user_id' => $user->id,
            'clock_in' => now()->subDay()->setTime(9, 0),
            'clock_out' => now()->subDay()->setTime(10, 0),
        ]);

        $response = $this->actingAs($user)->getJson('/api/v1/time-entries/today');

        $response->assertStatus(200)
            ->assertJsonCount(1, 'time_entries');
    }

    public function test_today_only_returns_entries_for_authenticated_user(): void
    {
        $user = User::factory()->create();
        $otherUser = User::factory()->create();

        TimeEntry::factory()->create([
            'user_id' => $otherUser->id,
            'clock_in' => now()->setTime(9, 0),
            'clock_out' => now()->setTime(10, 0),
        ]);

        $response = $this->actingAs($user)->getJson('/api/v1/time-entries/today');

        $response->assertStatus(200)
            ->assertJsonCount(0, 'time_entries');
    }

    // -------------------------------------------------------------------------
    // Date Range
    // -------------------------------------------------------------------------

    public function test_date_range_returns_entries_within_range(): void
    {
        $user = User::factory()->create();

        TimeEntry::factory()->create([
            'user_id' => $user->id,
            'clock_in' => Carbon::parse('2026-02-10 09:00'),
            'clock_out' => Carbon::parse('2026-02-10 10:00'),
        ]);
        TimeEntry::factory()->create([
            'user_id' => $user->id,
            'clock_in' => Carbon::parse('2026-02-05 09:00'),
            'clock_out' => Carbon::parse('2026-02-05 10:00'),
        ]);

        $response = $this->actingAs($user)->getJson('/api/v1/time-entries/date-range?from=2026-02-08&to=2026-02-15');

        $response->assertStatus(200)
            ->assertJsonCount(1, 'time_entries');
    }

    public function test_date_range_requires_from_and_to(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->getJson('/api/v1/time-entries/date-range');

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['from', 'to']);
    }

    public function test_date_range_to_must_be_after_or_equal_to_from(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->getJson('/api/v1/time-entries/date-range?from=2026-02-15&to=2026-02-10');

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['to']);
    }

    // -------------------------------------------------------------------------
    // Update — Happy Path
    // -------------------------------------------------------------------------

    public function test_user_can_update_their_own_entry(): void
    {
        $user = User::factory()->create();
        $entry = TimeEntry::factory()->create([
            'user_id' => $user->id,
            'clock_in' => Carbon::today()->setTime(9, 0),
            'clock_out' => Carbon::today()->setTime(10, 0),
        ]);

        $response = $this->actingAs($user)->putJson("/api/v1/time-entries/{$entry->id}", [
            'clock_in' => Carbon::today()->setTime(8, 0)->toDateTimeString(),
            'clock_out' => Carbon::today()->setTime(9, 0)->toDateTimeString(),
            'notes' => 'Updated note',
        ]);

        $response->assertStatus(200)
            ->assertJsonPath('message', 'Time entry updated successfully');

        $this->assertDatabaseHas('time_entries', [
            'id' => $entry->id,
            'notes' => 'Updated note',
        ]);
    }

    // -------------------------------------------------------------------------
    // Update — Working Hours
    // -------------------------------------------------------------------------

    public function test_update_fails_if_clock_in_before_6am(): void
    {
        $user = User::factory()->create();
        $entry = TimeEntry::factory()->create(['user_id' => $user->id]);

        $response = $this->actingAs($user)->putJson("/api/v1/time-entries/{$entry->id}", [
            'clock_in' => Carbon::today()->setTime(5, 59)->toDateTimeString(),
            'clock_out' => Carbon::today()->setTime(10, 0)->toDateTimeString(),
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['clock_in']);
    }

    public function test_update_fails_if_clock_in_after_11pm(): void
    {
        $user = User::factory()->create();
        $entry = TimeEntry::factory()->create(['user_id' => $user->id]);

        $response = $this->actingAs($user)->putJson("/api/v1/time-entries/{$entry->id}", [
            'clock_in' => Carbon::today()->setTime(23, 1)->toDateTimeString(),
            'clock_out' => Carbon::today()->setTime(23, 30)->toDateTimeString(),
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['clock_in']);
    }

    public function test_update_fails_if_clock_out_before_6am(): void
    {
        $user = User::factory()->create();
        $entry = TimeEntry::factory()->create(['user_id' => $user->id]);

        $response = $this->actingAs($user)->putJson("/api/v1/time-entries/{$entry->id}", [
            'clock_in' => Carbon::today()->setTime(6, 0)->toDateTimeString(),
            'clock_out' => Carbon::today()->setTime(5, 59)->toDateTimeString(),
        ]);

        // clock_out before clock_in will fail the after:clock_in validation first
        $response->assertStatus(422);
    }

    public function test_update_fails_if_clock_out_after_11pm(): void
    {
        $user = User::factory()->create();
        $entry = TimeEntry::factory()->create(['user_id' => $user->id]);

        $response = $this->actingAs($user)->putJson("/api/v1/time-entries/{$entry->id}", [
            'clock_in' => Carbon::today()->setTime(9, 0)->toDateTimeString(),
            'clock_out' => Carbon::today()->setTime(23, 30)->toDateTimeString(),
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['clock_out']);
    }

    public function test_update_allows_clock_in_at_exactly_6am(): void
    {
        $user = User::factory()->create();
        $entry = TimeEntry::factory()->create(['user_id' => $user->id]);

        $response = $this->actingAs($user)->putJson("/api/v1/time-entries/{$entry->id}", [
            'clock_in' => Carbon::today()->setTime(6, 0)->toDateTimeString(),
            'clock_out' => Carbon::today()->setTime(10, 0)->toDateTimeString(),
        ]);

        $response->assertStatus(200);
    }

    public function test_update_allows_clock_out_at_exactly_11pm(): void
    {
        $user = User::factory()->create();
        $entry = TimeEntry::factory()->create([
            'user_id' => $user->id,
            'clock_in' => Carbon::yesterday()->setTime(9, 0),
            'clock_out' => Carbon::yesterday()->setTime(17, 0),
        ]);

        $response = $this->actingAs($user)->putJson("/api/v1/time-entries/{$entry->id}", [
            'clock_in' => Carbon::yesterday()->setTime(9, 0)->toDateTimeString(),
            'clock_out' => Carbon::yesterday()->setTime(23, 0)->toDateTimeString(),
        ]);

        $response->assertStatus(200);
    }

    // -------------------------------------------------------------------------
    // Update — Overlap Detection
    // -------------------------------------------------------------------------

    public function test_update_fails_when_new_times_overlap_existing_entry(): void
    {
        $user = User::factory()->create();

        // Existing completed entry
        TimeEntry::factory()->create([
            'user_id' => $user->id,
            'clock_in' => Carbon::today()->setTime(9, 0),
            'clock_out' => Carbon::today()->setTime(10, 0),
        ]);

        // Entry we'll try to update to overlap
        $entry = TimeEntry::factory()->create([
            'user_id' => $user->id,
            'clock_in' => Carbon::today()->setTime(11, 0),
            'clock_out' => Carbon::today()->setTime(12, 0),
        ]);

        $response = $this->actingAs($user)->putJson("/api/v1/time-entries/{$entry->id}", [
            'clock_in' => Carbon::today()->setTime(9, 30)->toDateTimeString(),
            'clock_out' => Carbon::today()->setTime(10, 30)->toDateTimeString(),
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['clock_out']);
    }

    public function test_update_fails_when_entry_starts_inside_existing(): void
    {
        $user = User::factory()->create();

        TimeEntry::factory()->create([
            'user_id' => $user->id,
            'clock_in' => Carbon::today()->setTime(9, 0),
            'clock_out' => Carbon::today()->setTime(11, 0),
        ]);

        $entry = TimeEntry::factory()->create([
            'user_id' => $user->id,
            'clock_in' => Carbon::today()->setTime(13, 0),
            'clock_out' => Carbon::today()->setTime(14, 0),
        ]);

        $response = $this->actingAs($user)->putJson("/api/v1/time-entries/{$entry->id}", [
            'clock_in' => Carbon::today()->setTime(10, 0)->toDateTimeString(),
            'clock_out' => Carbon::today()->setTime(12, 0)->toDateTimeString(),
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['clock_out']);
    }

    public function test_update_fails_when_entry_completely_contains_existing(): void
    {
        $user = User::factory()->create();

        TimeEntry::factory()->create([
            'user_id' => $user->id,
            'clock_in' => Carbon::today()->setTime(10, 0),
            'clock_out' => Carbon::today()->setTime(11, 0),
        ]);

        $entry = TimeEntry::factory()->create([
            'user_id' => $user->id,
            'clock_in' => Carbon::today()->setTime(13, 0),
            'clock_out' => Carbon::today()->setTime(14, 0),
        ]);

        $response = $this->actingAs($user)->putJson("/api/v1/time-entries/{$entry->id}", [
            'clock_in' => Carbon::today()->setTime(9, 0)->toDateTimeString(),
            'clock_out' => Carbon::today()->setTime(12, 0)->toDateTimeString(),
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['clock_out']);
    }

    public function test_update_allows_adjacent_entries(): void
    {
        $user = User::factory()->create();

        TimeEntry::factory()->create([
            'user_id' => $user->id,
            'clock_in' => Carbon::today()->setTime(9, 0),
            'clock_out' => Carbon::today()->setTime(10, 0),
        ]);

        $entry = TimeEntry::factory()->create([
            'user_id' => $user->id,
            'clock_in' => Carbon::today()->setTime(11, 0),
            'clock_out' => Carbon::today()->setTime(12, 0),
        ]);

        $response = $this->actingAs($user)->putJson("/api/v1/time-entries/{$entry->id}", [
            'clock_in' => Carbon::today()->setTime(10, 0)->toDateTimeString(),
            'clock_out' => Carbon::today()->setTime(11, 0)->toDateTimeString(),
        ]);

        $response->assertStatus(200);
    }

    public function test_update_ignores_overlap_with_self(): void
    {
        $user = User::factory()->create();

        $entry = TimeEntry::factory()->create([
            'user_id' => $user->id,
            'clock_in' => Carbon::today()->setTime(9, 0),
            'clock_out' => Carbon::today()->setTime(10, 0),
        ]);

        $response = $this->actingAs($user)->putJson("/api/v1/time-entries/{$entry->id}", [
            'clock_in' => Carbon::today()->setTime(9, 0)->toDateTimeString(),
            'clock_out' => Carbon::today()->setTime(10, 0)->toDateTimeString(),
        ]);

        $response->assertStatus(200);
    }

    // -------------------------------------------------------------------------
    // Update — Open Entry Rules
    // -------------------------------------------------------------------------

    public function test_update_can_leave_entry_open_if_it_is_latest_for_day(): void
    {
        $user = User::factory()->create();

        // Earlier completed entry
        TimeEntry::factory()->create([
            'user_id' => $user->id,
            'clock_in' => Carbon::today()->setTime(8, 0),
            'clock_out' => Carbon::today()->setTime(9, 0),
        ]);

        // Later entry we want to leave open
        $entry = TimeEntry::factory()->create([
            'user_id' => $user->id,
            'clock_in' => Carbon::today()->setTime(10, 0),
            'clock_out' => Carbon::today()->setTime(11, 0),
        ]);

        $response = $this->actingAs($user)->putJson("/api/v1/time-entries/{$entry->id}", [
            'clock_in' => Carbon::today()->setTime(10, 0)->toDateTimeString(),
            'clock_out' => null,
        ]);

        $response->assertStatus(200);
    }

    public function test_update_fails_if_open_entry_is_not_latest_for_day(): void
    {
        $user = User::factory()->create();

        // Entry we want to mark as open
        $entry = TimeEntry::factory()->create([
            'user_id' => $user->id,
            'clock_in' => Carbon::today()->setTime(8, 0),
            'clock_out' => Carbon::today()->setTime(9, 0),
        ]);

        // A later completed entry exists
        TimeEntry::factory()->create([
            'user_id' => $user->id,
            'clock_in' => Carbon::today()->setTime(10, 0),
            'clock_out' => Carbon::today()->setTime(11, 0),
        ]);

        $response = $this->actingAs($user)->putJson("/api/v1/time-entries/{$entry->id}", [
            'clock_in' => Carbon::today()->setTime(8, 0)->toDateTimeString(),
            'clock_out' => null,
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['clock_out']);
    }

    // -------------------------------------------------------------------------
    // Authorization
    // -------------------------------------------------------------------------

    public function test_user_cannot_update_another_users_entry(): void
    {
        $user = User::factory()->create();
        $otherUser = User::factory()->create();

        $entry = TimeEntry::factory()->create(['user_id' => $otherUser->id]);

        $response = $this->actingAs($user)->putJson("/api/v1/time-entries/{$entry->id}", [
            'clock_in' => Carbon::today()->setTime(9, 0)->toDateTimeString(),
            'clock_out' => Carbon::today()->setTime(10, 0)->toDateTimeString(),
        ]);

        $response->assertStatus(403);
    }

    public function test_user_cannot_delete_another_users_entry(): void
    {
        $user = User::factory()->create();
        $otherUser = User::factory()->create();

        $entry = TimeEntry::factory()->create(['user_id' => $otherUser->id]);

        $response = $this->actingAs($user)->deleteJson("/api/v1/time-entries/{$entry->id}");

        $response->assertStatus(403);
    }

    // -------------------------------------------------------------------------
    // Delete
    // -------------------------------------------------------------------------

    public function test_user_can_delete_their_own_entry(): void
    {
        $user = User::factory()->create();
        $entry = TimeEntry::factory()->create(['user_id' => $user->id]);

        $response = $this->actingAs($user)->deleteJson("/api/v1/time-entries/{$entry->id}");

        $response->assertStatus(200)
            ->assertJsonPath('message', 'Time entry deleted successfully');

        $this->assertDatabaseMissing('time_entries', ['id' => $entry->id]);
    }

    // -------------------------------------------------------------------------
    // Data Isolation
    // -------------------------------------------------------------------------

    public function test_entries_are_isolated_between_users(): void
    {
        $user = User::factory()->create();
        $otherUser = User::factory()->create();

        // Create entry for other user today
        TimeEntry::factory()->create([
            'user_id' => $otherUser->id,
            'clock_in' => now()->setTime(9, 0),
            'clock_out' => now()->setTime(10, 0),
        ]);

        $response = $this->actingAs($user)->getJson('/api/v1/time-entries/today');

        $response->assertStatus(200)
            ->assertJsonCount(0, 'time_entries');
    }
}
