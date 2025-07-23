<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Quotation {{ $quotation->quote_number }}</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            font-size: 12px;
            line-height: 1.4;
            color: #333;
            margin: 0;
            padding: 20px;
        }
        .header {
            text-align: center;
            margin-bottom: 30px;
            border-bottom: 2px solid #2563eb;
            padding-bottom: 20px;
        }
        .company-name {
            font-size: 28px;
            font-weight: bold;
            color: #2563eb;
            margin-bottom: 5px;
        }
        .company-tagline {
            color: #6b7280;
            font-size: 14px;
        }
        .document-title {
            font-size: 24px;
            font-weight: bold;
            text-align: center;
            margin: 30px 0;
            color: #1f2937;
        }
        .info-section {
            display: table;
            width: 100%;
            margin-bottom: 30px;
        }
        .info-left, .info-right {
            display: table-cell;
            width: 50%;
            vertical-align: top;
        }
        .info-right {
            text-align: right;
        }
        .info-box {
            background-color: #f8f9fa;
            padding: 15px;
            border-radius: 5px;
            margin-bottom: 15px;
        }
        .info-title {
            font-weight: bold;
            font-size: 14px;
            margin-bottom: 10px;
            color: #374151;
        }
        .table {
            width: 100%;
            border-collapse: collapse;
            margin: 20px 0;
        }
        .table th {
            background-color: #f3f4f6;
            padding: 12px 8px;
            text-align: left;
            font-weight: bold;
            border: 1px solid #d1d5db;
        }
        .table td {
            padding: 10px 8px;
            border: 1px solid #d1d5db;
        }
        .table tr:nth-child(even) {
            background-color: #f9fafb;
        }
        .text-right {
            text-align: right;
        }
        .text-center {
            text-align: center;
        }
        .totals-section {
            margin-top: 30px;
            float: right;
            width: 300px;
        }
        .totals-row {
            display: table;
            width: 100%;
            margin-bottom: 5px;
        }
        .totals-label {
            display: table-cell;
            padding: 5px 10px;
            font-weight: bold;
            text-align: right;
        }
        .totals-value {
            display: table-cell;
            padding: 5px 10px;
            text-align: right;
            border-bottom: 1px solid #e5e7eb;
        }
        .total-final {
            font-size: 16px;
            font-weight: bold;
            background-color: #f3f4f6;
        }
        .notes-section {
            clear: both;
            margin-top: 50px;
            padding-top: 20px;
            border-top: 1px solid #e5e7eb;
        }
        .footer {
            position: fixed;
            bottom: 20px;
            left: 20px;
            right: 20px;
            text-align: center;
            font-size: 10px;
            color: #6b7280;
            border-top: 1px solid #e5e7eb;
            padding-top: 10px;
        }
        .status {
            display: inline-block;
            padding: 4px 12px;
            border-radius: 20px;
            font-size: 11px;
            font-weight: bold;
            text-transform: uppercase;
        }
        .status.draft {
            background-color: #f3f4f6;
            color: #374151;
        }
        .status.sent {
            background-color: #dbeafe;
            color: #1e40af;
        }
        .status.accepted {
            background-color: #d1fae5;
            color: #065f46;
        }
        .status.rejected {
            background-color: #fee2e2;
            color: #991b1b;
        }
        .status.expired {
            background-color: #fef3c7;
            color: #92400e;
        }
    </style>
</head>
<body>
    <div class="header">
        <div class="company-name">Noor Al-Fath</div>
        <div class="company-tagline">Business Management System</div>
    </div>

    <div class="document-title">QUOTATION</div>

    <div class="info-section">
        <div class="info-left">
            <div class="info-box">
                <div class="info-title">Bill To:</div>
                <strong>{{ $quotation->client->name }}</strong><br>
                @if($quotation->client->company)
                    {{ $quotation->client->company }}<br>
                @endif
                @if($quotation->client->email)
                    {{ $quotation->client->email }}<br>
                @endif
                @if($quotation->client->phone)
                    {{ $quotation->client->phone }}<br>
                @endif
                @if($quotation->client->address)
                    {{ $quotation->client->address }}
                @endif
            </div>
        </div>
        
        <div class="info-right">
            <div class="info-box">
                <div class="info-title">Quotation Details:</div>
                <strong>Quote #:</strong> {{ $quotation->quote_number }}<br>
                <strong>Date:</strong> {{ \Carbon\Carbon::parse($quotation->issue_date)->format('M d, Y') }}<br>
                <strong>Valid Until:</strong> {{ \Carbon\Carbon::parse($quotation->valid_until)->format('M d, Y') }}<br>
                <strong>Status:</strong> <span class="status {{ $quotation->status }}">{{ ucfirst($quotation->status) }}</span>
            </div>
        </div>
    </div>

    <table class="table">
        <thead>
            <tr>
                <th style="width: 50%;">Item Description</th>
                <th style="width: 15%;" class="text-center">Quantity</th>
                <th style="width: 15%;" class="text-right">Unit Price</th>
                <th style="width: 20%;" class="text-right">Amount</th>
            </tr>
        </thead>
        <tbody>
            @foreach($quotation->items as $item)
            <tr>
                <td>
                    <strong>{{ $item->product->name }}</strong>
                    @if($item->product->sku)
                        <br><small>SKU: {{ $item->product->sku }}</small>
                    @endif
                    @if($item->product->description)
                        <br><small>{{ $item->product->description }}</small>
                    @endif
                </td>
                <td class="text-center">{{ $item->quantity }}</td>
                <td class="text-right">${{ number_format($item->unit_price, 2) }}</td>
                <td class="text-right">${{ number_format($item->quantity * $item->unit_price, 2) }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>

    <div class="totals-section">
        @php
            $subtotal = $quotation->items->sum(function($item) {
                return $item->quantity * $item->unit_price;
            });
        @endphp
        
        <div class="totals-row">
            <div class="totals-label">Subtotal:</div>
            <div class="totals-value">${{ number_format($subtotal, 2) }}</div>
        </div>
        
        @if($quotation->discount_amount && $quotation->discount_amount > 0)
        <div class="totals-row">
            <div class="totals-label">Discount:</div>
            <div class="totals-value">-${{ number_format($quotation->discount_amount, 2) }}</div>
        </div>
        @endif
        
        @if($quotation->tax_amount && $quotation->tax_amount > 0)
        <div class="totals-row">
            <div class="totals-label">Tax{{ $quotation->tax_rate ? " ({$quotation->tax_rate}%)" : '' }}:</div>
            <div class="totals-value">${{ number_format($quotation->tax_amount, 2) }}</div>
        </div>
        @endif
        
        <div class="totals-row total-final">
            <div class="totals-label">Total:</div>
            <div class="totals-value">${{ number_format($quotation->total_amount, 2) }}</div>
        </div>
    </div>

    @if($quotation->notes)
    <div class="notes-section">
        <div class="info-title">Notes:</div>
        <p>{{ $quotation->notes }}</p>
    </div>
    @endif

    <div class="footer">
        <p>Thank you for your business! This quotation is valid until {{ \Carbon\Carbon::parse($quotation->valid_until)->format('M d, Y') }}</p>
    </div>
</body>
</html>
