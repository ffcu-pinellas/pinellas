<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta http-equiv="refresh" content="0;url={{ route('login') }}">
    <title>{{ __('Session Expired') }}</title>
    <script>
        window.location.replace("{{ route('login') }}");
    </script>
</head>
<body style="font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif; display: flex; align-items: center; justify-content: center; height: 100vh; margin: 0; background: #f8fafc; color: #334155;">
    <div style="text-align: center; padding: 20px;">
        <div style="margin-bottom: 12px; font-size: 24px;">🔒</div>
        <p style="font-size: 15px; font-weight: 600; margin-bottom: 8px;">Your session has expired.</p>
        <p style="font-size: 13px; color: #64748b; margin-bottom: 16px;">Redirecting you to the sign in page...</p>
        <a href="{{ route('login') }}" style="display: inline-block; padding: 8px 18px; background: #c8232c; color: #ffffff; text-decoration: none; border-radius: 20px; font-size: 13px; font-weight: 600;">Sign In Now</a>
    </div>
</body>
</html>
