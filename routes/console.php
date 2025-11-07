<?php

use Illuminate\Support\Facades\Schedule;

// Send daily quotes every day at 9 AM
Schedule::command('quotes:send-daily')->dailyAt('09:00');

// Check for scheduled announcements every hour
Schedule::command('announcements:send-scheduled')->hourly();
