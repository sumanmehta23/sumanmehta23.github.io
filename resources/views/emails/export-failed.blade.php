{{-- Use the new modern layout instead of extending defaultTemplate --}}
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Export Failed</title>
</head>
<body style="margin:0;padding:0;background:#f5f5f5;font-family:'Segoe UI',Arial,sans-serif;">
<div style="max-width:600px;margin:0 auto;background:#fff;border-radius:12px;box-shadow:0 4px 24px rgba(0,0,0,0.07);font-family:'Segoe UI',Arial,sans-serif;overflow:hidden;">
    <div style="background:linear-gradient(90deg,#dc3545 0,#c82333 100%);padding:32px 0;text-align:center;">
        <div style="font-size:48px;">❌</div>
        <h1 style="color:#fff;font-size:2rem;margin:0 0 8px 0;letter-spacing:1px;">Export Failed</h1>
        <div style="color:#ffe0e1;font-size:1rem;">There was an issue processing your IB Users export</div>
    </div>
    <div style="padding:32px 24px 16px 24px;">
        <div style="font-size:1.1rem;color:#333;margin-bottom:18px;">Hello <strong>{{ $userName }}</strong>,</div>
        <div style="background:linear-gradient(135deg,#f8d7da 0%,#f5c6cb 100%);border:1px solid #f5c6cb;color:#721c24;padding:18px 20px;margin-bottom:24px;border-radius:8px;text-align:center;font-size:1rem;">
            <h3 style="margin:0;font-size:1.2rem;">⚠️ Export Unsuccessful</h3>
            <div>Unfortunately, your <span style="color:#dc3545;font-weight:600;">{{ $exportType }}</span> export has failed to complete.</div>
        </div>
        
        {{-- Error Details --}}
        <div style="background:#f8f9fa;padding:18px 20px;border-radius:8px;margin-bottom:24px;border-left:4px solid #dc3545;">
            <h3 style="margin:0 0 12px 0;color:#495057;font-size:1.1rem;">🔍 Error Details</h3>
            <div style="margin:12px 0;">
                <div style="display:flex;justify-content:space-between;align-items:center;margin:8px 0;">
                    <span><strong>Failed at:</strong></span>
                    <span>{{ $failedAt }}</span>
                </div>
                @if(isset($requestedRecords))
                <div style="display:flex;justify-content:space-between;align-items:center;margin:8px 0;">
                    <span><strong>Records requested:</strong></span>
                    <span>{{ number_format($requestedRecords) }}</span>
                </div>
                @endif
                @if(isset($errorCode))
                <div style="display:flex;justify-content:space-between;align-items:center;margin:8px 0;">
                    <span><strong>Error Code:</strong></span>
                    <span style="font-family:monospace;background:#e9ecef;padding:4px 8px;border-radius:4px;font-size:0.9rem;">{{ $errorCode }}</span>
                </div>
                @endif
            </div>
            <div style="margin:15px 0;">
                <strong>Error message:</strong>
                <div style="font-family:'Courier New',monospace;background:#e9ecef;padding:12px;border-radius:6px;word-break:break-all;font-size:0.9rem;margin-top:8px;color:#495057;">{{ $errorMessage }}</div>
            </div>
        </div>
        
        {{-- What to do next --}}
        <div style="background:#e3f2fd;padding:18px 20px;border-radius:8px;margin-bottom:24px;">
            <h3 style="margin:0 0 15px 0;color:#1976d2;font-size:1.1rem;">🛠️ What you can do:</h3>
            <ol style="margin:0;padding-left:20px;color:#424242;line-height:1.7;">
                <li style="margin:8px 0;">Try the export again in a few minutes</li>
                <li style="margin:8px 0;">Check if you have applied any complex filters</li>
                <li style="margin:8px 0;">Try exporting a smaller date range</li>
                <li style="margin:8px 0;">Contact support if the problem persists</li>
            </ol>
        </div>
        
        {{-- Retry Button --}}
        <div style="text-align:center;margin:32px 0;">
            <a href="{{ $retryUrl ?? url('/admin/iblist_active') }}" 
               style="background:linear-gradient(135deg,#007bff,#0056b3);
                      color:#fff;
                      text-decoration:none;
                      padding:14px 32px;
                      border-radius:8px;
                      font-size:1.1rem;
                      font-weight:600;
                      box-shadow:0 2px 8px rgba(0,123,255,0.08);
                      transition:background 0.2s;
                      display:inline-block;">
                <span style="margin-right:8px;">🔄</span>Try Export Again
            </a>
        </div>
        
        {{-- Common causes --}}
        <div style="color:#4a5568;line-height:1.6;font-size:0.98rem;margin:24px 0 12px 0;">
            <strong>Common causes:</strong><br>
            • High server load during peak hours<br>
            • Very large datasets (try filtering by date)<br>
            • Temporary database connectivity issues<br>
            • System maintenance activities
        </div>
        
        {{-- Contact support section --}}
        <div style="background:#fff3cd;border:1px solid #ffeaa7;border-radius:8px;padding:15px;margin:20px 0;color:#856404;">
            <div style="font-size:1.05rem;font-weight:600;margin-bottom:8px;">📞 Need Help?</div>
            <div style="font-size:0.95rem;">
                If this issue continues, please contact our support team at 
                <strong style="color:#00b98e;">{{ $supportEmail ?? 'support@lqhmarkets.com' }}</strong> 
                and include the error code above for faster resolution.
            </div>
        </div>
    </div>
    
    {{-- Footer --}}
    <div style="background:#f8f9fa;padding:18px 0;text-align:center;color:#6c757d;font-size:0.98rem;border-top:1px solid #e9ecef;">
        <div style="margin-bottom:6px;font-weight:600;">We apologize for the inconvenience.</div>
        <div>Our technical team has been notified of this issue.</div>
    </div>
</div>
</body>
</html>
            padding: 40px;
        }
        
        .greeting {
            font-size: 18px;
            margin-bottom: 20px;
            color: #4a5568;
        }
        
        .error-message {
            background: linear-gradient(135deg, #f8d7da 0%, #f5c6cb 100%);
            border: 1px solid #f5c6cb;
            color: #721c24;
            padding: 20px;
            margin: 20px 0;
            border-radius: 10px;
        }
        
        .retry-button {
            display: inline-block;
            background: linear-gradient(135deg, #007bff 0%, #0056b3 100%);
            color: white;
            padding: 15px 30px;
            text-decoration: none;
            border-radius: 25px;
            font-weight: bold;
            margin: 20px 0;
            transition: all 0.3s ease;
            box-shadow: 0 4px 15px rgba(0, 123, 255, 0.3);
        }
        
        .retry-button:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(0, 123, 255, 0.4);
        }
        
        .error-details {
            background: #f8f9fa;
            padding: 20px;
            border-radius: 10px;
            margin: 20px 0;
            border-left: 4px solid #dc3545;
        }
        
        .error-details h3 {
            margin: 0 0 10px 0;
            color: #495057;
        }
        
        .error-code {
            font-family: 'Courier New', monospace;
            background: #e9ecef;
            padding: 10px;
            border-radius: 5px;
            word-break: break-all;
            font-size: 12px;
        }
        
        .footer {
            background: #f8f9fa;
            padding: 20px;
            text-align: center;
            color: #6c757d;
            font-size: 14px;
        }
        
        .steps {
            background: #e3f2fd;
            padding: 20px;
            border-radius: 10px;
            margin: 20px 0;
        }
        
        .steps h3 {
            margin: 0 0 15px 0;
            color: #1976d2;
        }
        
        .steps ol {
            margin: 0;
            padding-left: 20px;
        }
        
        .steps li {
            margin: 8px 0;
            color: #424242;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <div class="icon">❌</div>
            <h1>Export Failed</h1>
        </div>
        
        <div class="content">
            <div class="greeting">Hello {{ $userName }},</div>
            
            <div class="error-message">
                <h3 style="margin: 0 0 10px 0;">⚠️ Export Unsuccessful</h3>
                <p style="margin: 0;">Unfortunately, your {{ $exportType }} export has failed to complete.</p>
            </div>
            
            <div class="error-details">
                <h3>🔍 Error Details</h3>
                <p><strong>Failed at:</strong> {{ $failedAt }}</p>
                <p><strong>Error message:</strong></p>
                <div class="error-code">{{ $errorMessage }}</div>
            </div>
            
            <div class="steps">
                <h3>🛠️ What you can do:</h3>
                <ol>
                    <li>Try the export again in a few minutes</li>
                    <li>Check if you have applied any complex filters</li>
                    <li>Try exporting a smaller date range</li>
                    <li>Contact support if the problem persists</li>
                </ol>
            </div>
            
            <div style="text-align: center;">
                <a href="{{ url('/admin/iblist_active') }}" class="retry-button">
                    🔄 Try Export Again
                </a>
            </div>
            
            <p style="color: #4a5568; line-height: 1.6;">
                <strong>Common causes:</strong><br>
                • High server load during peak hours<br>
                • Very large datasets (try filtering by date)<br>
                • Temporary database connectivity issues<br>
                • System maintenance activities
            </p>
        </div>
        
        <div class="footer">
            <p>We apologize for the inconvenience.</p>
            <p>If this issue continues, please contact our support team with the error details above.</p>
        </div>
    </div>
</body>
</html>
