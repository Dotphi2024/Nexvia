<?php

use Illuminate\Support\Facades\Schedule;

Schedule::command('openai:process-batches')->everyMinute();
// Schedule::command('openai:trigger-batch')->daily();