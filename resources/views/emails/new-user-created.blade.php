<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Welcome!</title>
    <style>
        /* Basic styles for email clients that do support them */
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif, 'Apple Color Emoji', 'Segoe UI Emoji', 'Segoe UI Symbol';
            background-color: #f4f4f7;
            color: #3d4852;
            line-height: 1.5;
        }
    </style>
</head>

<body style="margin: 0; padding: 0; background-color: #f4f4f7;">
    <table width="100%" border="0" cellspacing="0" cellpadding="0">
        <tr>
            <td align="center" style="padding: 20px 0;">
                <table width="600" border="0" cellspacing="0" cellpadding="0"
                    style="background-color: #ffffff; border-radius: 8px; box-shadow: 0 4px 8px rgba(0,0,0,0.1);">
                    <!-- Header -->
                    <tr>
                        <td align="center" style="padding: 20px; border-bottom: 1px solid #e8e5ef;">
                            <h1 style="font-size: 24px; color: #2d3748; margin: 0;">{{ config('app.name') }}</h1>
                        </td>
                    </tr>
                    <!-- Content -->
                    <tr>
                        <td style="padding: 30px 40px;">
                            <h2 style="font-size: 20px; color: #2d3748; margin-top: 0;">Welcome Aboard!</h2>
                            <p style="color: #3d4852;">Hi {{ $user->name }},</p>
                            <p style="color: #3d4852;">An administrator has created an account for you. You can log in
                                using your email and the temporary password below.</p>

                            <!-- Password Panel -->
                            <table width="100%" border="0" cellspacing="0" cellpadding="0"
                                style="background-color: #edf2f7; border-radius: 4px; margin: 20px 0;">
                                <tr>
                                    <td style="padding: 15px 20px;">
                                        Your temporary password is: <strong
                                            style="color: #2d3748;">{{ $temporaryPassword }}</strong>
                                    </td>
                                </tr>
                            </table>

                            <p style="color: #3d4852;">For your security, please log in and change this password from
                                your account settings page as soon as possible.</p>

                            <!-- Button -->
                            <table width="100%" border="0" cellspacing="0" cellpadding="0" style="margin-top: 30px;">
                                <tr>
                                    <td align="center">
                                        <a href="{{ route('login') }}"
                                            style="display: inline-block; background-color: #3490dc; color: #ffffff; padding: 12px 25px; border-radius: 4px; text-decoration: none; font-weight: bold;">
                                            Log In to Your Account
                                        </a>
                                    </td>
                                </tr>
                            </table>

                            <p style="margin-top: 30px; color: #3d4852;">Thanks,<br>The {{ config('app.name') }} Team
                            </p>
                        </td>
                    </tr>
                    <!-- Footer -->
                    <tr>
                        <td align="center"
                            style="padding: 20px; font-size: 12px; color: #a0aec0; border-top: 1px solid #e8e5ef;">
                            &copy; {{ date('Y') }} {{ config('app.name') }}. All rights reserved.
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>
</body>

</html>