<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Invoice {{ $invoice->invoice_number }}</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            line-height: 1.6;
            color: #333;
            max-width: 600px;
            margin: 0 auto;
            padding: 20px;
        }
        .header {
            text-align: center;
            margin-bottom: 30px;
            padding: 20px;
            background-color: #f8f9fa;
            border-radius: 8px;
        }
        .company-name {
            font-size: 24px;
            font-weight: bold;
            color: #2563eb;
            margin-bottom: 5px;
        }
        .company-tagline {
            color: #6b7280;
            font-size: 14px;
        }
        .content {
            margin-bottom: 30px;
        }
        .details-box {
            background-color: #f8f9fa;
            padding: 20px;
            border-radius: 8px;
            margin: 20px 0;
        }
        .details-row {
            display: flex;
            justify-content: space-between;
            margin-bottom: 10px;
        }
        .details-label {
            font-weight: bold;
            color: #374151;
        }
        .details-value {
            color: #6b7280;
        }
        .custom-message {
            background-color: #eff6ff;
            padding: 15px;
            border-left: 4px solid #2563eb;
            margin: 20px 0;
            border-radius: 4px;
        }
        .footer {
            text-align: center;
            margin-top: 40px;
            padding-top: 20px;
            border-top: 1px solid #e5e7eb;
            color: #6b7280;
            font-size: 12px;
        }
        .amount {
            font-size: 18px;
            font-weight: bold;
            color: #059669;
        }
        .due-date {
            background-color: #fef3c7;
            padding: 15px;
            border-left: 4px solid #f59e0b;
            margin: 20px 0;
            border-radius: 4px;
        }
        .status {
            display: inline-block;
            padding: 4px 12px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: bold;
            text-transform: uppercase;
        }
        .status.paid {
            background-color: #d1fae5;
            color: #065f46;
        }
        .status.pending {
            background-color: #fef3c7;
            color: #92400e;
        }
        .status.overdue {
            background-color: #fee2e2;
            color: #991b1b;
        }
    </style>
</head>
<body>
    <div class="header">
        <div class="company-name">Noor Al-Fath</div>
        <div class="company-tagline">Business Management System</div>
    </div>

    <div class="content">
        <h2>Invoice {{ $invoice->invoice_number }}</h2>
        
        <p>Dear {{ $invoice->client->name }},</p>
        
        @if($customMessage)
            <div class="custom-message">
                {!! nl2br(e($customMessage)) !!}
            </div>
        @else
            <p>I hope this email finds you well.</p>
            <p>Please find attached invoice {{ $invoice->invoice_number }} dated {{ \Carbon\Carbon::parse($invoice->issue_date)->format('M d, Y') }}.</p>
        @endif

        <div class="details-box">
            <h3 style="margin-top: 0;">Invoice Details</h3>
            
            <div class="details-row">
                <span class="details-label">Invoice Number:</span>
                <span class="details-value">{{ $invoice->invoice_number }}</span>
            </div>
            
            <div class="details-row">
                <span class="details-label">Issue Date:</span>
                <span class="details-value">{{ \Carbon\Carbon::parse($invoice->issue_date)->format('M d, Y') }}</span>
            </div>
            
            <div class="details-row">
                <span class="details-label">Due Date:</span>
                <span class="details-value">{{ \Carbon\Carbon::parse($invoice->due_date)->format('M d, Y') }}</span>
            </div>
            
            <div class="details-row">
                <span class="details-label">Status:</span>
                <span class="status {{ $invoice->status }}">{{ ucfirst($invoice->status) }}</span>
            </div>
            
            <div class="details-row">
                <span class="details-label">Total Amount:</span>
                <span class="amount">${{ number_format($invoice->total_amount, 2) }}</span>
            </div>
        </div>

        @if($invoice->status !== 'paid')
            <div class="due-date">
                <strong>Payment Due:</strong> {{ \Carbon\Carbon::parse($invoice->due_date)->format('M d, Y') }}
                <br>
                Please process payment at your earliest convenience to avoid any late fees.
            </div>
        @endif

        @if($invoice->status === 'paid')
            <div style="background-color: #d1fae5; padding: 15px; border-left: 4px solid #10b981; margin: 20px 0; border-radius: 4px;">
                <strong>Thank you!</strong> This invoice has been paid in full.
            </div>
        @endif

        <p>If you have any questions about this invoice or need to discuss payment terms, please don't hesitate to contact us.</p>

        <p>Thank you for your business.</p>

        <p>Best regards,<br>
        <strong>Noor Al-Fath Team</strong></p>
    </div>

    <div class="footer">
        <p>This is an automated email. Please do not reply directly to this email address.</p>
        <p>If you have any questions, please contact us through our official channels.</p>
    </div>
</body>
</html>
