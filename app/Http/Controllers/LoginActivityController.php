<?php

namespace App\Http\Controllers;

use App\Models\LoginActivity;
use App\Models\User;
use App\Models\Customer;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Stevebauman\Location\Facades\Location;

class LoginActivityController extends Controller
{
    public function index(Request $request)
    {
        $query = LoginActivity::query()->with(['user']);

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('email', 'like', "%{$search}%")
                  ->orWhere('ip_address', 'like', "%{$search}%")
                  ->orWhere('browser', 'like', "%{$search}%")
                  ->orWhere('city', 'like', "%{$search}%");
            });
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('user_type')) {
            $query->where('user_type', $request->user_type);
        }

        if ($request->filled('ip_address')) {
            $query->where('ip_address', $request->ip_address);
        }

        if ($request->filled('start_date') && $request->filled('end_date')) {
            $query->whereBetween('login_at', [
                $request->start_date . ' 00:00:00',
                $request->end_date . ' 23:59:59'
            ]);
        }

        $perPage = $request->get('per_page', 50);
        $activities = $query->orderBy('login_at', 'desc')->paginate($perPage);

        $stats = [
            'total_logins' => LoginActivity::count(),
            'success_logins' => LoginActivity::success()->count(),
            'failed_logins' => LoginActivity::failed()->count(),
            'unique_ips' => LoginActivity::distinct()->count('ip_address'),
            'unique_users' => LoginActivity::distinct()->count(DB::raw('CONCAT(user_type, "-", user_id)')),
        ];

        $suspiciousIps = LoginActivity::failed()
            ->select('ip_address', DB::raw('COUNT(*) as failed_count'))
            ->groupBy('ip_address')
            ->having('failed_count', '>=', 5)
            ->orderByDesc('failed_count')
            ->limit(10)
            ->get();

        $recentFailed = LoginActivity::failed()
            ->with(['user'])
            ->orderBy('login_at', 'desc')
            ->limit(10)
            ->get();

        return view('misc.admin.login-activities.index', compact(
            'activities', 'stats', 'suspiciousIps', 'recentFailed'
        ));
    }

    public function show(LoginActivity $loginActivity)
    {
        $loginActivity->load(['user']);

        return view('misc.admin.login-activities.show', compact('loginActivity'));
    }

    public function userActivity(Request $request, $userType, $userId)
    {
        $userClass = $userType === 'User' ? User::class : Customer::class;
        $user = $userClass::findOrFail($userId);

        $activities = LoginActivity::where('user_type', $userClass)
            ->where('user_id', $userId)
            ->orderBy('login_at', 'desc')
            ->paginate(20);

        $stats = [
            'total_logins' => LoginActivity::where('user_type', $userClass)->where('user_id', $userId)->count(),
            'success_logins' => LoginActivity::where('user_type', $userClass)->where('user_id', $userId)->success()->count(),
            'failed_logins' => LoginActivity::where('user_type', $userClass)->where('user_id', $userId)->failed()->count(),
            'unique_ips' => LoginActivity::where('user_type', $userClass)->where('user_id', $userId)->distinct()->count('ip_address'),
        ];

        return view('misc.admin.login-activities.user-activity', compact('user', 'activities', 'stats'));
    }

    public function export(Request $request)
    {
        $query = LoginActivity::query();

        if ($request->filled('start_date') && $request->filled('end_date')) {
            $query->whereBetween('login_at', [
                $request->start_date . ' 00:00:00',
                $request->end_date . ' 23:59:59'
            ]);
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $activities = $query->orderBy('login_at', 'desc')->get();

        $filename = 'login-activities-' . now()->format('Y-m-d-His') . '.csv';

        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
        ];

        $callback = function() use ($activities) {
            $file = fopen('php://output', 'w');
            fputcsv($file, ['ID', 'User Type', 'User', 'Email', 'IP Address', 'Location', 'Device', 'Browser', 'OS', 'Status', 'Login At', 'Failure Reason']);

            foreach ($activities as $activity) {
                fputcsv($file, [
                    $activity->id,
                    $activity->user_type ? class_basename($activity->user_type) : 'N/A',
                    $activity->user ? ($activity->user->name ?? $activity->user->full_name ?? $activity->user->email) : 'N/A',
                    $activity->email ?? 'N/A',
                    $activity->ip_address ?? 'N/A',
                    $activity->location,
                    $activity->device_type ?? 'N/A',
                    $activity->browser ?? 'N/A',
                    $activity->os ?? 'N/A',
                    $activity->status,
                    $activity->login_at->format('Y-m-d H:i:s'),
                    $activity->failure_reason ?? 'N/A',
                ]);
            }
            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    public static function logLogin($user, string $email, string $status, ?string $failureReason = null): LoginActivity
    {
        $userAgent = request()->userAgent();
        $ipAddress = request()->ip();

        $deviceInfo = self::parseUserAgent($userAgent);

        $location = null;
        try {
            $position = Location::get($ipAddress);
            if ($position) {
                $location = $position;
            }
        } catch (\Exception $e) {
            \Log::warning('Could not get location for IP: ' . $ipAddress);
        }

        return LoginActivity::create([
            'user_type' => get_class($user),
            'user_id' => $user->id,
            'email' => $email,
            'ip_address' => $ipAddress,
            'user_agent' => $userAgent,
            'country' => $location->countryName ?? null,
            'city' => $location->cityName ?? null,
            'device_type' => $deviceInfo['device'],
            'browser' => $deviceInfo['browser'],
            'os' => $deviceInfo['os'],
            'login_at' => now(),
            'status' => $status,
            'failure_reason' => $failureReason,
        ]);
    }

    private static function parseUserAgent(?string $userAgent): array
    {
        $device = 'Desktop';
        $browser = 'Unknown';
        $os = 'Unknown';

        if (!$userAgent) {
            return compact('device', 'browser', 'os');
        }

        if (preg_match('/Mobile|Android|iPhone|iPad|iPod|Windows Phone/i', $userAgent)) {
            $device = preg_match('/iPad|Tablet/i', $userAgent) ? 'Tablet' : 'Mobile';
        }

        if (preg_match('/Chrome/i', $userAgent)) {
            $browser = 'Chrome';
        } elseif (preg_match('/Firefox/i', $userAgent)) {
            $browser = 'Firefox';
        } elseif (preg_match('/Safari/i', $userAgent)) {
            $browser = 'Safari';
        } elseif (preg_match('/Edge/i', $userAgent)) {
            $browser = 'Edge';
        } elseif (preg_match('/MSIE|Trident/i', $userAgent)) {
            $browser = 'IE';
        }

        if (preg_match('/Windows/i', $userAgent)) {
            $os = 'Windows';
        } elseif (preg_match('/Macintosh|Mac OS/i', $userAgent)) {
            $os = 'macOS';
        } elseif (preg_match('/Linux/i', $userAgent)) {
            $os = 'Linux';
        } elseif (preg_match('/Android/i', $userAgent)) {
            $os = 'Android';
        } elseif (preg_match('/iOS|iPhone|iPad/i', $userAgent)) {
            $os = 'iOS';
        }

        return compact('device', 'browser', 'os');
    }
}
