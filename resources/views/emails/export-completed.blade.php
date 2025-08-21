{{-- Use the new modern layout instead of extending defaultTemplate --}}
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Export Completed</title>
</head>
<body style="margin:0;padding:0;background:#f5f5f5;font-family:'Segoe UI',Arial,sans-serif;">
<div style="max-width:600px;margin:0 auto;background:#fff;border-radius:12px;box-shadow:0 4px 24px rgba(0,0,0,0.07);font-family:'Segoe UI',Arial,sans-serif;overflow:hidden;">
    <div style="background:linear-gradient(90deg,#28a745 0,#20c997 100%);padding:32px 0;text-align:center;">
        <div style="font-size:48px;">✅</div>
        <h1 style="color:#fff;font-size:2rem;margin:0 0 8px 0;letter-spacing:1px;">Export Completed!</h1>
        <div style="color:#e0f7fa;font-size:1rem;">Your IB Users export is ready for download</div>
    </div>
    <div style="padding:32px 24px 16px 24px;">
        <div style="font-size:1.1rem;color:#333;margin-bottom:18px;">Hello <strong>{{ $userName }}</strong>,</div>
        <div style="background:linear-gradient(135deg,#d4edda 0%,#c3e6cb 100%);border:1px solid #c3e6cb;color:#155724;padding:18px 20px;margin-bottom:24px;border-radius:8px;text-align:center;font-size:1rem;">
            <h3 style="margin:0;font-size:1.2rem;">🎉 Export Successful!</h3>
            <div>Your <span style="color:#28a745;font-weight:600;">{{ $exportType }}</span> export has been completed successfully.</div>
        </div>
        <div style="display:flex;gap:16px;justify-content:space-between;margin-bottom:24px;">
            <div style="flex:1;background:#f8f9fa;padding:18px;border-radius:8px;text-align:center;">
                <div style="font-size:1.5rem;font-weight:700;color:#28a745;">{{ number_format($recordCount) }}</div>
                <div style="font-size:0.85rem;color:#6c757d;text-transform:uppercase;letter-spacing:1px;">Total Records</div>
            </div>
            <div style="flex:1;background:#f8f9fa;padding:18px;border-radius:8px;text-align:center;">
                <div style="font-size:1.5rem;font-weight:700;color:#28a745;">24 hrs</div>
                <div style="font-size:0.85rem;color:#6c757d;text-transform:uppercase;letter-spacing:1px;">Download Expires</div>
            </div>
        </div>
        <div style="background:#f8f9fa;padding:18px 20px;border-radius:8px;margin-bottom:24px;">
            <h3 style="margin:0 0 10px 0;color:#495057;font-size:1.1rem;">📁 File Information</h3>
            <div style="display:flex;justify-content:space-between;align-items:center;margin:10px 0;">
                <span><strong>Filename:</strong></span>
                <span>{{ $fileName }}</span>
            </div>
            <div style="display:flex;justify-content:space-between;align-items:center;margin:10px 0;">
                <span><strong>Format:</strong></span>
                <span>Excel (.xlsx)</span>
            </div>
            <div style="display:flex;justify-content:space-between;align-items:center;margin:10px 0;">
                <span><strong>Expires:</strong></span>
                <span>{{ $expiresAt }}</span>
            </div>
            <div style="display:flex;justify-content:space-between;align-items:center;margin:10px 0;">
                <span><strong>File Size:</strong></span>
                <span>{{ $fileSizeEstimate ?? 'Calculating...' }}</span>
            </div>
        </div>
        
        {{-- Enhanced Download Button --}}
        <div style="text-align:center;margin:32px 0;">
            <a href="{{ $downloadUrl }}" 
               style="background:linear-gradient(135deg,#28a745,#20c997);
                      color:#fff;
                      text-decoration:none;
                      padding:16px 40px;
                      border-radius:12px;
                      font-size:1.2rem;
                      font-weight:700;
                      box-shadow:0 4px 20px rgba(40,167,69,0.3);
                      transition:all 0.3s ease;
                      display:inline-block;
                      border:2px solid transparent;
                      letter-spacing:0.5px;
                      text-transform:uppercase;">
                <span style="margin-right:12px;font-size:1.3rem;">📥</span>Download Your Export
            </a>
        </div>
        
        <div style="background:#fff3cd;border:1px solid #ffeaa7;border-radius:8px;padding:15px;margin:24px 0;color:#856404;">
            <div style="font-size:1.05rem;font-weight:600;margin-bottom:8px;">⚠️ Important:</div>
            <div>This download link will expire in <strong>24 hours</strong> for security reasons. Please download your file as soon as possible.</div>
        </div>
        
        <div style="color:#4a5568;line-height:1.6;font-size:0.98rem;margin-bottom:12px;">
            <strong>What's included:</strong><br>
            • Complete IB user data<br>
            • Commission details<br>
            • Withdrawal information<br>
            • Registration dates and times<br>
            • Contact information
        </div>
        
        {{-- Additional file details --}}
        <div style="background:#e8f4fd;border-left:4px solid #007bff;padding:15px;margin:20px 0;border-radius:4px;">
            <div style="color:#004085;font-weight:600;margin-bottom:8px;">📊 Export Details</div>
            <div style="color:#004085;font-size:0.9rem;">
                <div>• Exported on: {{ $exportDate }}</div>
                <div>• Total records: {{ number_format($recordCount) }}</div>
                @if(isset($filters))
                <div>• Filters applied: {{ $filters }}</div>
                @endif
            </div>
        </div>
    </div>
    
    {{-- Professional Footer --}}
    <div style="background:#f8f9fa;padding:20px;text-align:center;color:#6c757d;font-size:0.98rem;border-top:1px solid #e9ecef;">
        <div style="margin-bottom:8px;font-weight:600;">Thank you for using LQH Markets Export System!</div>
        <div style="margin-bottom:8px;">If you have any issues downloading the file, please contact our support team.</div>
        <div style="color:#00b98e;font-weight:600;">support@lqhmarkets.com</div>
    </div>
</div>
</body>
</html>
