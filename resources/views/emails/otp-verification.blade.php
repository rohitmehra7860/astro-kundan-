<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Email Verification OTP</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif;
            background-color: #f4f4f5;
            padding: 40px 20px;
            color: #18181b;
        }
        .container {
            max-width: 560px;
            margin: 0 auto;
            background: #ffffff;
            border-radius: 12px;
            overflow: hidden;
            box-shadow: 0 4px 24px rgba(0,0,0,0.08);
        }
        .header {
            background: linear-gradient(135deg, #6366f1 0%, #8b5cf6 100%);
            padding: 36px 40px;
            text-align: center;
        }
        .header h1 {
            color: #ffffff;
            font-size: 22px;
            font-weight: 700;
            letter-spacing: -0.3px;
        }
        .header p {
            color: rgba(255,255,255,0.8);
            font-size: 14px;
            margin-top: 6px;
        }
        .body {
            padding: 40px;
        }
        .greeting {
            font-size: 16px;
            color: #3f3f46;
            margin-bottom: 16px;
        }
        .message {
            font-size: 15px;
            color: #52525b;
            line-height: 1.6;
            margin-bottom: 32px;
        }
        .otp-box {
            background: #f4f4f5;
            border: 2px dashed #6366f1;
            border-radius: 12px;
            padding: 28px;
            text-align: center;
            margin-bottom: 32px;
        }
        .otp-label {
            font-size: 12px;
            font-weight: 600;
            color: #6366f1;
            text-transform: uppercase;
            letter-spacing: 1.5px;
            margin-bottom: 12px;
        }
        .otp-code {
            font-size: 42px;
            font-weight: 800;
            letter-spacing: 10px;
            color: #18181b;
            font-variant-numeric: tabular-nums;
        }
        .otp-expiry {
            font-size: 13px;
            color: #71717a;
            margin-top: 12px;
        }
        .warning {
            background: #fef9c3;
            border-left: 4px solid #eab308;
            border-radius: 6px;
            padding: 14px 16px;
            font-size: 13px;
            color: #713f12;
            margin-bottom: 28px;
        }
        .footer {
            border-top: 1px solid #f4f4f5;
            padding: 24px 40px;
            text-align: center;
            font-size: 12px;
            color: #a1a1aa;
            line-height: 1.6;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>🔐 Verify Your Email</h1>
            <p>One-Time Password (OTP)</p>
        </div>
        <div class="body">
            <p class="greeting">Hi <strong>{{ $userName }}</strong>,</p>
            <p class="message">
                Thank you for registering! Please use the OTP below to verify your email address and activate your account.
            </p>

            <div class="otp-box">
                <div class="otp-label">Your Verification Code</div>
                <div class="otp-code">{{ $otp }}</div>
                <div class="otp-expiry">⏱ Expires in <strong>15 minutes</strong></div>
            </div>

            <div class="warning">
                ⚠️ Do not share this code with anyone. Our team will never ask for your OTP.
            </div>

            <p class="message" style="font-size: 14px;">
                If you did not create an account, you can safely ignore this email.
            </p>
        </div>
        <div class="footer">
            <p>This email was sent by <strong>{{ config('app.name') }}</strong></p>
            <p>Please do not reply to this email.</p>
        </div>
    </div>
</body>
</html>
