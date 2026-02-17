<?php

namespace App\Actions\TimeEntry;

use App\Models\TimeEntry;

class DeleteTimeEntryAction
{
    public function execute(TimeEntry $timeEntry): void
    {
        $timeEntry->delete();
    }
}
