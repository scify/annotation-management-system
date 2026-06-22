<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="color-scheme" content="light">
    <title>{{ __('emails.co_manager_invitation.subject', ['app' => $appName]) }}</title>
</head>
<body style="margin: 0; padding: 0; width: 100%; background-color: #f2f5fd; font-family: Arial, Helvetica, sans-serif; -webkit-text-size-adjust: 100%;">
    <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="background-color: #f2f5fd;">
        <tr>
            <td align="center" style="padding: 32px 16px;">
                <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="max-width: 560px; background-color: #ffffff; border-radius: 12px; overflow: hidden; border: 1px solid #d9e1f8;">
                    <!-- Header -->
                    <tr>
                        <td align="center" style="background-color: #4d6fd1; padding: 28px 24px;">
                            <img src="{{ $logoUrl }}" alt="{{ $appName }}" width="auto" height="50" style="display: block; border: 0; outline: none; text-decoration: none; height: 48px; width: auto;">
                        </td>
                    </tr>

                    <!-- Body -->
                    <tr>
                        <td style="padding: 32px 32px 24px 32px; color: #1e293b;">
                            <h1 style="margin: 0 0 16px 0; font-size: 22px; line-height: 1.3; color: #27396b;">
                                {{ __('emails.co_manager_invitation.greeting', ['name' => $name]) }}
                            </h1>
                            <p style="margin: 0 0 16px 0; font-size: 16px; line-height: 1.6; color: #334155;">
                                {{ __('emails.co_manager_invitation.intro', ['inviter' => $inviterName, 'project' => $projectName]) }}
                            </p>
                            <p style="margin: 0 0 28px 0; font-size: 16px; line-height: 1.6; color: #334155;">
                                {{ __('emails.co_manager_invitation.outro') }}
                            </p>

                            <!-- CTA -->
                            <table role="presentation" cellpadding="0" cellspacing="0">
                                <tr>
                                    <td align="center" style="border-radius: 8px; background-color: #4d6fd1;">
                                        <a href="{{ $actionUrl }}" target="_blank" style="display: inline-block; padding: 14px 28px; font-size: 16px; font-weight: bold; color: #ffffff; text-decoration: none; border-radius: 8px; min-height: 20px; line-height: 20px;">
                                            {{ __('emails.co_manager_invitation.cta', ['app' => $appName]) }}
                                        </a>
                                    </td>
                                </tr>
                            </table>
                        </td>
                    </tr>

                    <!-- Footer -->
                    <tr>
                        <td style="padding: 20px 32px 28px 32px; border-top: 1px solid #e8eef9;">
                            <p style="margin: 0; font-size: 13px; line-height: 1.5; color: #94a3b8;">
                                &copy; {{ $appName }}
                            </p>
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>
</body>
</html>
