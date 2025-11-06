@php
    $customer = $purchase->customer;
    $ticket = $purchase->lotteryTicket;
@endphp

<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>New Ticket Purchase</title>
</head>
<body style="font-family: sans-serif; background: #f9f9f9;">
    <div style="background: #fff; max-width: 600px; margin: 30px auto; border-radius: 5px; padding: 30px; border: 1px solid #eee;">
        <h2 style="margin-bottom: 20px; color: #111">🎟️ New Lottery Ticket Purchase Pending Approval</h2>

        <p>
            A new ticket purchase has been submitted and is pending your approval.
        </p>
        <hr>
        <h4>Customer Information</h4>
        <table style="width: 100%; margin-bottom:10px;">
            <tr>
                <td><b>Name:</b></td>
                <td>{{ $customer->full_name ?? 'N/A' }}</td>
            </tr>
            <tr>
                <td><b>Email:</b></td>
                <td>{{ $customer->email ?? 'N/A' }}</td>
            </tr>
            <tr>
                <td><b>Phone:</b></td>
                <td>{{ $customer->phone_number ?? 'N/A' }}</td>
            </tr>
        </table>

        <h4>Ticket Details</h4>
        <table style="width: 100%; margin-bottom:10px;">
            <tr>
                <td><b>Ticket Name:</b></td>
                <td>{{ $ticket->ticket_name ?? 'N/A' }}</td>
            </tr>
            <tr>
                <td><b>Period:</b></td>
                <td>{{ $ticket->period ?? 'N/A' }}</td>
            </tr>
            <tr>
                <td><b>Type:</b></td>
                <td>{{ $ticket->ticket_type ?? 'N/A' }}</td>
            </tr>
            <tr>
                <td><b>Bar Code:</b></td>
                <td>{{ $ticket->bar_code ?? 'N/A' }}</td>
            </tr>
            <tr>
                <td><b>Numbers:</b></td>
                <td>
                    {{ is_array($ticket->numbers) ? implode('-', $ticket->numbers) : ($ticket->numbers ?? 'N/A') }}
                </td>
            </tr>
        </table>

        <h4>Purchase Information</h4>
        <table style="width: 100%; margin-bottom:10px;">
            <tr>
                <td><b>Order Number:</b></td>
                <td>{{ $purchase->order_number }}</td>
            </tr>
            <tr>
                <td><b>Quantity:</b></td>
                <td>{{ $purchase->quantity }}</td>
            </tr>
            <tr>
                <td><b>Total Price:</b></td>
                <td>{{ number_format($purchase->total_price, 2) }} THB</td>
            </tr>
            <tr>
                <td><b>Status:</b></td>
                <td><span style="color:orange">{{ ucfirst($purchase->status) }}</span></td>
            </tr>
            <tr>
                <td><b>Submitted At:</b></td>
                <td>{{ $purchase->created_at->format('Y-m-d H:i') }}</td>
            </tr>
        </table>

        <hr>
        <h4>Payment Screenshot</h4>
        @if($purchase->payment_screenshot)
            <p>
                <a href="{{ asset('storage/' . $purchase->payment_screenshot) }}" target="_blank">
                    <img src="{{ asset('storage/' . $purchase->payment_screenshot) }}" alt="proof" style="max-width: 260px; border-radius:6px;" />
                </a>
            </p>
            <p>Click the image to view full size.</p>
        @else
            <p><i>No payment screenshot uploaded.</i></p>
        @endif

        <hr>
        <p>
            <b>Next action needed:</b><br>
            Log in to the admin panel to review and approve/reject the purchase.
        </p>
        <p>
            <a href="{{ url('/purchases/'.$purchase->id) }}" style="background: #0b72ee; color: #fff; border-radius: 4px; padding: 8px 16px; text-decoration: none;">Review Purchase</a>
        </p>
    </div>
</body>
</html>
