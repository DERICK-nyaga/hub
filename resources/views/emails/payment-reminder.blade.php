<!DOCTYPE html>
<html>
<head>
    <title>Payment Reminder</title>
    <style>
        body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
        .container { max-width: 600px; margin: 0 auto; padding: 20px; }
        .header { background: #4CAF50; color: white; padding: 10px; text-align: center; }
        .content { padding: 20px; background: #f9f9f9; }
        .footer { text-align: center; padding: 10px; font-size: 12px; color: #777; }
        .button { display: inline-block; padding: 10px 20px; background: #4CAF50; color: white; text-decoration: none; border-radius: 5px; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h2>Payment Reminder</h2>
        </div>
        <div class="content">
            <pre style="white-space: pre-wrap; font-family: inherit;">{{ $emailMessage }}</pre>
        </div>
        <div class="footer">
            <p>This is an automated reminder. Please do not reply to this email.</p>
            <p>&copy; {{ date('Y') }} Payment System. All rights reserved.</p>
        </div>
    </div>
</body>
</html>