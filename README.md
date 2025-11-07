crontab -e

* * * * * cd /path-to-your-laravel-project && php artisan schedule:run >> /dev/null 2>&1


# Test daily quotes manually
php artisan quotes:send-daily

# Test scheduled announcements
php artisan announcements:send-scheduled
