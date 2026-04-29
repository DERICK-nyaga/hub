<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="color-scheme" content="light dark">
    <title>@yield('title', 'Link Manager - Industrial CRUD')</title>
    
    <!-- EU Cookie Compliance -->
    <script>
        // GDPR Cookie Consent (ePrivacy Directive)
        if (!localStorage.getItem('cookie_consent')) {
            document.cookie = "cookie_consent_pending=1; path=/; max-age=3600";
        }
    </script>
    
    <style>
        /* Industrial Design System - EU Compliant (WCAG 2.1 AA) */
        :root {
            --industrial-blue: #1e3a8a;
            --industrial-gray: #4b5563;
            --industrial-steel: #6b7280;
            --industrial-warning: #dc2626;
            --industrial-success: #059669;
            --industrial-bg: #f3f4f6;
            --contrast-ratio: 4.5; /* WCAG AA compliant */
        }
        
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, 'Helvetica Neue', Arial, sans-serif;
            background: var(--industrial-bg);
            color: #111827;
            line-height: 1.5;
            font-size: 16px;
        }
        
        /* Industrial Container */
        .container {
            max-width: 1280px;
            margin: 0 auto;
            padding: 2rem 1rem;
        }
        
        /* Industrial Header */
        .header {
            background: linear-gradient(135deg, var(--industrial-blue) 0%, #0f2b5c 100%);
            color: white;
            padding: 2rem 0;
            margin-bottom: 2rem;
            box-shadow: 0 4px 6px -1px rgba(0,0,0,0.1);
            border-bottom: 4px solid #f59e0b;
        }
        
        .header h1 {
            font-size: 1.875rem;
            font-weight: 600;
            letter-spacing: -0.025em;
        }
        
        /* Industrial Cards */
        .card {
            background: white;
            border-radius: 0.5rem;
            box-shadow: 0 1px 3px 0 rgba(0,0,0,0.1);
            margin-bottom: 1.5rem;
            border: 1px solid #e5e7eb;
        }
        
        .card-header {
            padding: 1.25rem 1.5rem;
            border-bottom: 1px solid #e5e7eb;
            background: #f9fafb;
            font-weight: 600;
            font-size: 1.125rem;
        }
        
        .card-body {
            padding: 1.5rem;
        }
        
        /* Industrial Table */
        .industrial-table {
            width: 100%;
            background: white;
            border-collapse: collapse;
        }
        
        .industrial-table thead {
            background: #f9fafb;
            border-bottom: 2px solid #e5e7eb;
        }
        
        .industrial-table th {
            padding: 0.75rem 1rem;
            text-align: left;
            font-weight: 600;
            color: #374151;
        }
        
        .industrial-table td {
            padding: 0.75rem 1rem;
            border-bottom: 1px solid #e5e7eb;
        }
        
        /* Industrial Buttons - High Contrast */
        .btn {
            display: inline-flex;
            align-items: center;
            padding: 0.5rem 1rem;
            border-radius: 0.375rem;
            font-weight: 500;
            text-decoration: none;
            transition: all 150ms;
            border: none;
            cursor: pointer;
            font-size: 0.875rem;
        }
        
        .btn-primary {
            background: var(--industrial-blue);
            color: white;
        }
        
        .btn-primary:hover {
            background: #14346c;
        }
        
        .btn-danger {
            background: var(--industrial-warning);
            color: white;
        }
        
        .btn-success {
            background: var(--industrial-success);
            color: white;
        }
        
        .btn-secondary {
            background: var(--industrial-steel);
            color: white;
        }
        
        /* Industrial Forms */
        .form-group {
            margin-bottom: 1rem;
        }
        
        .form-label {
            display: block;
            font-weight: 500;
            margin-bottom: 0.5rem;
            color: #374151;
        }
        
        .form-control {
            width: 100%;
            padding: 0.5rem 0.75rem;
            border: 1px solid #d1d5db;
            border-radius: 0.375rem;
            font-size: 0.875rem;
        }
        
        .form-control:focus {
            outline: none;
            border-color: var(--industrial-blue);
            ring: 2px solid var(--industrial-blue);
        }
        
        /* GDPR Notice Banner */
        .gdpr-notice {
            background: #fef3c7;
            border-left: 4px solid #f59e0b;
            padding: 1rem;
            margin-bottom: 1.5rem;
            font-size: 0.875rem;
        }
        
        /* Accessibility Skip Link */
        .skip-link {
            position: absolute;
            top: -40px;
            left: 0;
            background: var(--industrial-blue);
            color: white;
            padding: 8px;
            z-index: 100;
        }
        
        .skip-link:focus {
            top: 0;
        }
        
        /* Responsive */
        @media (max-width: 768px) {
            .industrial-table {
                display: block;
                overflow-x: auto;
            }
            
            .container {
                padding: 1rem;
            }
        }
    </style>
</head>
<body>
    <a href="#main-content" class="skip-link">Skip to main content</a>
    
    <div class="header">
        <div class="container">
            <h1>🔗 Industrial Link Manager</h1>
            <p>EU-GDPR Compliant | WCAG 2.1 AA | Industrial Grade</p>
        </div>
    </div>
    
    <div class="container" id="main-content">
        @if(session('success'))
            <div class="gdpr-notice" role="alert">
                ✅ {{ session('success') }}
            </div>
        @endif
        
        @if($errors->any())
            <div class="gdpr-notice" style="background: #fee2e2; border-left-color: #dc2626;">
                <strong>⚠️ Validation Error:</strong>
                <ul class="mt-2">
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif
        
        @yield('content')
    </div>
    
    <!-- GDPR Cookie Consent Banner -->
    <script>
        if (!localStorage.getItem('cookie_consent')) {
            const banner = document.createElement('div');
            banner.innerHTML = `
                <div style="position: fixed; bottom: 0; left: 0; right: 0; background: #1f2937; color: white; padding: 1rem; text-align: center; z-index: 1000;">
                    <p>We use essential cookies for functionality. <a href="/cookie-consent" style="color: #fbbf24;">Learn more</a>
                    <button onclick="acceptCookies()" style="margin-left: 1rem; padding: 0.5rem 1rem; background: #059669; color: white; border: none; border-radius: 0.25rem;">Accept</button>
                    <button onclick="rejectCookies()" style="margin-left: 0.5rem; padding: 0.5rem 1rem; background: #6b7280; color: white; border: none; border-radius: 0.25rem;">Reject</button>
                    </p>
                </div>
            `;
            document.body.appendChild(banner);
            window.acceptCookies = function() {
                localStorage.setItem('cookie_consent', 'accepted');
                document.querySelector('div[style*="position: fixed"]').remove();
            };
            window.rejectCookies = function() {
                localStorage.setItem('cookie_consent', 'rejected');
                document.querySelector('div[style*="position: fixed"]').remove();
            };
        }
    </script>
</body>
</html>