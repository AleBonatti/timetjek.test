<?php

namespace App\Http\Requests\TimeEntry;

use App\Models\TimeEntry;
use Illuminate\Foundation\Http\FormRequest;

class UpdateTimeEntryRequest extends FormRequest
{
    public function authorize(): bool
    {
        $timeEntry = $this->route('timeEntry');

        return $timeEntry instanceof TimeEntry
            && $this->user()->can('update', $timeEntry);
    }

    public function rules(): array
    {
        return [
            'clock_in' => 'required|date|before_or_equal:now',
            'clock_out' => 'nullable|date|after:clock_in|before_or_equal:now',
            'notes' => 'nullable|string|max:1000',
        ];
    }
}
