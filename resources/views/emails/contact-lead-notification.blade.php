<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>New Contact Lead</title>
</head>
<body style="font-family: Arial, sans-serif; line-height: 1.6; color: #333; background-color: #f9f9f9; padding: 20px;">
    <div style="max-width: 600px; margin: 0 auto; background-color: #ffffff; padding: 20px; border-radius: 8px; box-shadow: 0 4px 6px rgba(0,0,0,0.05); border: 1px solid #e2e8f0;">
        <h2 style="color: #ff9800; border-bottom: 2px solid #ff9800; padding-bottom: 10px; margin-top: 0;">New Contact Form Submission</h2>
        
        <table style="width: 100%; border-collapse: collapse; margin-top: 20px;">
            <tr>
                <td style="padding: 8px 0; font-weight: bold; width: 120px; border-bottom: 1px solid #f1f5f9;">Name:</td>
                <td style="padding: 8px 0; border-bottom: 1px solid #f1f5f9;">{{ $lead->name }}</td>
            </tr>
            <tr>
                <td style="padding: 8px 0; font-weight: bold; border-bottom: 1px solid #f1f5f9;">Email:</td>
                <td style="padding: 8px 0; border-bottom: 1px solid #f1f5f9;"><a href="mailto:{{ $lead->email }}">{{ $lead->email }}</a></td>
            </tr>
            @if($lead->phone)
            <tr>
                <td style="padding: 8px 0; font-weight: bold; border-bottom: 1px solid #f1f5f9;">Phone:</td>
                <td style="padding: 8px 0; border-bottom: 1px solid #f1f5f9;">{{ $lead->phone }}</td>
            </tr>
            @endif
            @if($lead->subject)
            <tr>
                <td style="padding: 8px 0; font-weight: bold; border-bottom: 1px solid #f1f5f9;">Subject:</td>
                <td style="padding: 8px 0; border-bottom: 1px solid #f1f5f9;">{{ $lead->subject }}</td>
            </tr>
            @endif
            @if($lead->source)
            <tr>
                <td style="padding: 8px 0; font-weight: bold; border-bottom: 1px solid #f1f5f9;">Source:</td>
                <td style="padding: 8px 0; border-bottom: 1px solid #f1f5f9;">{{ $lead->source }}</td>
            </tr>
            @endif
            <tr>
                <td style="padding: 8px 0; font-weight: bold; border-bottom: 1px solid #f1f5f9;">Submitted:</td>
                <td style="padding: 8px 0; border-bottom: 1px solid #f1f5f9;">{{ $lead->created_at ? $lead->created_at->format('Y-m-d H:i:s') : now()->format('Y-m-d H:i:s') }}</td>
            </tr>
        </table>
        
        <div style="margin-top: 20px; padding: 15px; background-color: #f8fafc; border-radius: 6px; border: 1px solid #e2e8f0;">
            <h3 style="margin-top: 0; margin-bottom: 10px; color: #475569; font-size: 16px;">Message Content:</h3>
            <p style="margin: 0; white-space: pre-wrap; font-size: 14px; color: #334155;">{{ $lead->message }}</p>
        </div>
        
        <div style="margin-top: 25px; text-align: center; border-top: 1px solid #f1f5f9; padding-top: 15px; font-size: 12px; color: #94a3b8;">
            This email was sent automatically from your website backend contact form.
        </div>
    </div>
</body>
</html>
