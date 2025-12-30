<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>GDPR Data Export - {{ $customer->full_name ?? $customer->phone_number }}</title>
    <style>
        body { font-family: Arial, sans-serif; max-width: 900px; margin: 0 auto; padding: 20px; color: #333; }
        h1 { color: #1e40af; border-bottom: 2px solid #1e40af; padding-bottom: 10px; }
        h2 { color: #3b82f6; margin-top: 30px; }
        h3 { color: #64748b; }
        .section { background: #f8fafc; padding: 20px; border-radius: 8px; margin: 20px 0; border: 1px solid #e2e8f0; }
        .info-table { width: 100%; border-collapse: collapse; margin: 10px 0; }
        .info-table td { padding: 8px 12px; border-bottom: 1px solid #e2e8f0; }
        .info-table td:first-child { font-weight: bold; width: 200px; background: #f1f5f9; }
        .status-badge { display: inline-block; padding: 4px 12px; border-radius: 20px; font-size: 12px; font-weight: bold; }
        .status-success { background: #dcfce7; color: #166534; }
        .status-danger { background: #fee2e2; color: #991b1b; }
        .status-warning { background: #fef3c7; color: #92400e; }
        .purchase-item { background: white; padding: 15px; border-radius: 6px; margin: 10px 0; border: 1px solid #e2e8f0; }
        .footer { margin-top: 40px; padding-top: 20px; border-top: 1px solid #e2e8f0; font-size: 12px; color: #64748b; }
    </style>
</head>
<body>
    <h1>GDPR Data Export</h1>
    <p><strong>Generated:</strong> {{ $data['export_info']['generated_at'] }}</p>
    <p><strong>Customer:</strong> {{ $customer->full_name ?? 'N/A' }} ({{ $customer->phone_number }})</p>

    <h2>1. Personal Information</h2>
    <div class="section">
        <table class="info-table">
            <tr><td>Customer ID</td><td>{{ $data['personal_information']['id'] }}</td></tr>
            <tr><td>Full Name</td><td>{{ $data['personal_information']['full_name'] ?? 'N/A' }}</td></tr>
            <tr><td>Email</td><td>{{ $data['personal_information']['email'] ?? 'N/A' }}</td></tr>
            <tr><td>Phone Number</td><td>{{ $data['personal_information']['phone_number'] }}</td></tr>
            <tr><td>Gender</td><td>{{ $data['personal_information']['gender'] ?? 'N/A' }}</td></tr>
            <tr><td>Date of Birth</td><td>{{ $data['personal_information']['date_of_birth'] ?? 'N/A' }}</td></tr>
            <tr><td>Address</td><td>{{ $data['personal_information']['address'] ?? 'N/A' }}</td></tr>
            <tr><td>Account Created</td><td>{{ $data['personal_information']['created_at'] }}</td></tr>
            <tr><td>Last Updated</td><td>{{ $data['personal_information']['updated_at'] }}</td></tr>
        </table>
    </div>

    <h2>2. Account Status</h2>
    <div class="section">
        <p>
            <span class="status-badge {{ $data['account_status']['is_blocked'] ? 'status-danger' : 'status-success' }}">
                {{ $data['account_status']['is_blocked'] ? 'BLOCKED' : 'ACTIVE' }}
            </span>
        </p>
        @if($data['account_status']['is_blocked'])
        <table class="info-table">
            <tr><td>Blocked At</td><td>{{ $data['account_status']['blocked_at'] }}</td></tr>
            <tr><td>Block Reason</td><td>{{ $data['account_status']['block_reason'] }}</td></tr>
        </table>
        @endif
    </div>

    <h2>3. Purchase History ({{ count($data['purchases']) }} records)</h2>
    <div class="section">
        @forelse($data['purchases'] as $purchase)
        <div class="purchase-item">
            <table class="info-table">
                <tr><td>Order Number</td><td>{{ $purchase['order_number'] }}</td></tr>
                <tr><td>Ticket Number</td><td>{{ $purchase['ticket_number'] ?? 'N/A' }}</td></tr>
                <tr><td>Quantity</td><td>{{ $purchase['quantity'] }}</td></tr>
                <tr><td>Total Price</td><td>฿{{ number_format($purchase['total_price'], 2) }}</td></tr>
                <tr><td>Status</td><td>
                    <span class="status-badge {{ $purchase['status'] === 'won' ? 'status-success' : ($purchase['status'] === 'not_won' ? 'status-danger' : 'status-warning') }}">
                        {{ strtoupper($purchase['status']) }}
                    </span>
                </td></tr>
                @if($purchase['prize_won'])
                <tr><td>Prize Won</td><td class="status-success" style="color: #166534; font-weight: bold;">฿{{ number_format($purchase['prize_won'], 2) }}</td></tr>
                @endif
                <tr><td>Created At</td><td>{{ $purchase['created_at'] }}</td></tr>
            </table>
        </div>
        @empty
        <p>No purchase history found.</p>
        @endforelse
    </div>

    <h2>4. Device Push Tokens ({{ count($data['push_tokens']) }} devices)</h2>
    <div class="section">
        @forelse($data['push_tokens'] as $token)
        <div class="purchase-item">
            <table class="info-table">
                <tr><td>Platform</td><td>{{ $token['platform'] }}</td></tr>
                <tr><td>Device</td><td>{{ $token['device_name'] }}</td></tr>
                <tr><td>Status</td><td>
                    <span class="status-badge {{ $token['is_active'] ? 'status-success' : 'status-danger' }}">
                        {{ $token['is_active'] ? 'ACTIVE' : 'INACTIVE' }}
                    </span>
                </td></tr>
                <tr><td>Last Seen</td><td>{{ $token['last_seen_at'] ?? 'Never' }}</td></tr>
            </table>
        </div>
        @empty
        <p>No push tokens found.</p>
        @endforelse
    </div>

    <h2>5. Login Activity ({{ count($data['login_activities']) }} recent records)</h2>
    <div class="section">
        @forelse($data['login_activities'] as $activity)
        <div class="purchase-item">
            <table class="info-table">
                <tr><td>IP Address</td><td>{{ $activity['ip_address'] }}</td></tr>
                <tr><td>Location</td><td>{{ $activity['location'] }}</td></tr>
                <tr><td>Device</td><td>{{ $activity['device'] }}</td></tr>
                <tr><td>Browser</td><td>{{ $activity['browser'] }}</td></tr>
                <tr><td>Status</td><td>
                    <span class="status-badge {{ $activity['status'] === 'success' ? 'status-success' : 'status-danger' }}">
                        {{ strtoupper($activity['status']) }}
                    </span>
                </td></tr>
                <tr><td>Login Time</td><td>{{ $activity['login_at'] }}</td></tr>
            </table>
        </div>
        @empty
        <p>No login activity found.</p>
        @endforelse
    </div>

    <h2>6. Activity Logs ({{ count($data['activity_logs']) }} recent records)</h2>
    <div class="section">
        @forelse($data['activity_logs'] as $log)
        <div class="purchase-item">
            <table class="info-table">
                <tr><td>Action</td><td>{{ $log['action'] }}</td></tr>
                <tr><td>Description</td><td>{{ $log['description'] }}</td></tr>
                <tr><td>Context</td><td>{{ $log['context'] }}</td></tr>
                <tr><td>IP Address</td><td>{{ $log['ip_address'] }}</td></tr>
                <tr><td>Created At</td><td>{{ $log['created_at'] }}</td></tr>
            </table>
        </div>
        @empty
        <p>No activity logs found.</p>
        @endforelse
    </div>

    <div class="footer">
        <p>This data export was generated in accordance with GDPR Article 20 (Right to data portability).</p>
        <p>Generated by Kan-San Admin System on {{ $data['export_info']['generated_at'] }}</p>
    </div>
</body>
</html>
