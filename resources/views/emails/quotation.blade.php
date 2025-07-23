<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Quotation {{ $quotation->quote_number }}</title>
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
        .button {
            display: inline-block;
            padding: 12px 24px;
            background-color: #2563eb;
            color: white;
            text-decoration: none;
            border-radius: 6px;
            font-weight: bold;
            margin: 20px 0;
        }
        .amount {
            font-size: 18px;
            font-weight: bold;
            color: #059669;
        }
    </style>
</head>
<body>
    <div class="header">
        <div class="company-name">Noor Al-Fath</div>
        <div class="company-tagline">Business Management System</div>
    </div>

    <div class="content">
        <h2>Quotation {{ $quotation->quote_number }}</h2>
        
        <p>Dear {{ $quotation->client->name }},</p>
        
        @if($customMessage)
            <div class="custom-message">
                {!! nl2br(e($customMessage)) !!}
            </div>
        @else
            <p>I hope this email finds you well.</p>
            <p>Please find attached quotation {{ $quotation->quote_number }} for your review.</p>
        @endif

        <div class="details-box">
            <h3 style="margin-top: 0;">Quotation Details</h3>
            
            <div class="details-row">
                <span class="details-label">Quote Number:</span>
                <span class="details-value">{{ $quotation->quote_number }}</span>
            </div>
            
            <div class="details-row">
                <span class="details-label">Issue Date:</span>
                <span class="details-value">{{ \Carbon\Carbon::parse($quotation->issue_date)->format('M d, Y') }}</span>
            </div>
            
            <div class="details-row">
                <span class="details-label">Valid Until:</span>
                <span class="details-value">{{ \Carbon\Carbon::parse($quotation->valid_until)->format('M d, Y') }}</span>
            </div>
            
            <div class="details-row">
                <span class="details-label">Status:</span>
                <span class="details-value">{{ ucfirst($quotation->status) }}</span>
            </div>
            
            <div class="details-row">
                <span class="details-label">Total Amount:</span>
                <span class="amount">${{ number_format($quotation->total_amount, 2) }}</span>
            </div>
        </div>

        <p>This quotation is valid until {{ \Carbon\Carbon::parse($quotation->valid_until)->format('M d, Y') }}. Please review the attached document and let us know if you have any questions or would like to proceed with the order.</p>

        <p>We look forward to hearing from you soon.</p>

        <p>Best regards,<br>
        <strong>Noor Al-Fath Team</strong></p>
    </div>

    <div class="footer">
        <p>This is an automated email. Please do not reply directly to this email address.</p>
        <p>If you have any questions, please contact us through our official channels.</p>
    </div>
</body>
</html>
