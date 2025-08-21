{{-- Use the new modern layout instead of extending defaultTemplate --}}
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Export Started</title>
</head>
<body style="margin:0;padding:0;background:#f5f5f5;font-family:'Segoe UI',Arial,sans-serif;">
<div style="max-width:600px;margin:0 auto;background:#fff;border-radius:12px;box-shadow:0 4px 24px rgba(0,0,0,0.07);font-family:'Segoe UI',Arial,sans-serif;overflow:hidden;">
    <div style="background:linear-gradient(90deg,#007bff 0,#00f2fe 100%);padding:32px 0;text-align:center;">
        <div style="font-size:48px;">📊</div>
        <h1 style="color:#fff;font-size:2rem;margin:0 0 8px 0;letter-spacing:1px;">Export Started</h1>
        <div style="color:#e0f7fa;font-size:1rem;">Your IB Users export is now being processed</div>
    </div>
    <div style="padding:32px 24px 16px 24px;">
        <div style="font-size:1.1rem;color:#333;margin-bottom:18px;">Hello <strong>{{ $userName }}</strong>,</div>
        <div style="background:#f7fafc;border-left:4px solid #4facfe;padding:18px 20px;margin-bottom:24px;border-radius:6px;font-size:1rem;color:#444;">
            <strong>Great news!</strong> Your <span style="color:#007bff;font-weight:600;">{{ $exportType }}</span> export has been started and is now processing in the background.
        </div>
        
        {{-- Processing Information --}}
        <div style="display:flex;gap:16px;justify-content:space-between;margin-bottom:24px;">
            <div style="flex:1;background:#f8f9fa;padding:18px;border-radius:8px;text-align:center;">
                <div style="font-size:1.5rem;font-weight:700;color:#4facfe;">{{ number_format($estimatedRecords) }}</div>
                <div style="font-size:0.85rem;color:#6c757d;text-transform:uppercase;letter-spacing:1px;">Estimated Records</div>
            </div>
            <div style="flex:1;background:#f8f9fa;padding:18px;border-radius:8px;text-align:center;">
                <div style="font-size:1.5rem;font-weight:700;color:#4facfe;">{{ $processingTime ?? '2-5' }} min</div>
                <div style="font-size:0.85rem;color:#6c757d;text-transform:uppercase;letter-spacing:1px;">Est. Time</div>
            </div>
        </div>
        
        {{-- Export Details --}}
        <div style="background:#e8f4fd;border-left:4px solid #007bff;padding:18px 20px;border-radius:6px;margin-bottom:24px;">
            <h3 style="margin:0 0 12px 0;color:#1565c0;font-size:1.1rem;">📋 Export Details</h3>
            <div style="color:#1565c0;font-size:0.95rem;line-height:1.6;">
                <div><strong>Started:</strong> {{ $startedAt }}</div>
                @if(isset($estimatedCompletion))
                <div><strong>Expected completion:</strong> {{ $estimatedCompletion }}</div>
                @endif
                @if(isset($filters) && $filters !== 'No filters applied')
                <div><strong>Filters:</strong> {{ $filters }}</div>
                @endif
            </div>
        </div>
        
        {{-- What happens next section --}}
        <div style="background:#e3f2fd;border-radius:8px;padding:18px 20px;margin-bottom:24px;color:#1565c0;">
            <div style="font-size:1.1rem;font-weight:600;margin-bottom:12px;">🔄 What happens next?</div>
            <ul style="margin:0;padding-left:20px;line-height:1.7;font-size:0.98rem;">
                <li>Your export is being processed in the background</li>
                <li>You'll receive another email when it's complete with download link</li>
                <li>The download link will be valid for 24 hours</li>
                <li>You can continue using the system normally</li>
            </ul>
        </div>
        
        {{-- Progress indicator --}}
        <div style="background:#f8f9fa;padding:20px;border-radius:8px;margin-bottom:24px;text-align:center;">
            <div style="color:#6c757d;font-size:0.9rem;margin-bottom:12px;">Export Progress</div>
            <div style="background:#e9ecef;height:8px;border-radius:4px;overflow:hidden;">
                <div style="background:linear-gradient(90deg,#007bff,#00f2fe);height:100%;width:30%;border-radius:4px;animation:progress 3s ease-in-out infinite;">
                </div>
            </div>
            <div style="color:#6c757d;font-size:0.85rem;margin-top:8px;">Processing {{ number_format($estimatedRecords) }} records...</div>
        </div>
        
        <div style="text-align:center;margin:32px 0 0 0;">
            <a href="{{ $adminPanelUrl ?? url('/admin/iblist_active') }}" style="background:linear-gradient(135deg,#007bff,#00f2fe);color:#fff;text-decoration:none;padding:14px 32px;border-radius:8px;font-size:1.1rem;font-weight:600;box-shadow:0 2px 8px rgba(0,0,0,0.08);transition:background 0.2s;display:inline-block;">
                <span style="margin-right:8px;">←</span>Back to IB List
            </a>
        </div>
    </div>
    <div style="background:#f8f9fa;padding:18px 0;text-align:center;color:#6c757d;font-size:0.98rem;border-top:1px solid #e9ecef;">
        <div style="margin-bottom:6px;font-weight:600;">LQH Markets Admin Panel</div>
        <div>You will receive a follow-up email once your export is ready for download.</div>
    </div>
</div>

<style>
@keyframes progress {
    0% { width: 30%; }
    50% { width: 70%; }
    100% { width: 30%; }
}
</style>
</body>
</html>