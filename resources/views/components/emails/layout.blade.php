@props(['title' => 'BerRuang', 'preheader' => ''])

<!DOCTYPE html>
<html lang="en" xmlns="http://www.w3.org/1999/xhtml">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="x-apple-disable-message-reformatting">
    <meta name="color-scheme" content="dark light">
    <meta name="format-detection" content="telephone=no">
    <title>{{ $title }}</title>
    <style>
        body {
            margin: 0;
            padding: 0;
            -webkit-text-size-adjust: 100%;
            -ms-text-size-adjust: 100%;
        }
        table {
            border-collapse: collapse;
            mso-table-lspace: 0pt;
            mso-table-rspace: 0pt;
        }
        img {
            border: 0;
            line-height: 100%;
        }
        @media only screen and (max-width: 600px) {
            .container { width: 100% !important; }
            .padding { padding: 28px 20px !important; }
            .code { font-size: 28px !important; letter-spacing: 6px !important; }
        }
    </style>
</head>
<body style="margin:0;padding:0;background-color:#0A0A0A;">
    <div style="display:none;font-size:1px;line-height:1px;max-height:0;max-width:0;opacity:0;overflow:hidden;mso-hide:all;">
        {{ $preheader ?: $title }}&nbsp;&zwnj;&nbsp;&zwnj;&nbsp;&zwnj;&nbsp;&zwnj;&nbsp;
    </div>

    <table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0" bgcolor="#0A0A0A" style="background-color:#0A0A0A;">
        <tr>
            <td align="center" style="padding:48px 16px;">
                <table role="presentation" class="container" width="560" cellpadding="0" cellspacing="0" border="0" style="width:100%;max-width:560px;">
                    <tr>
                        <td align="center" style="padding:0 0 28px;font-family:Arial,Helvetica,sans-serif;font-size:22px;font-weight:700;color:#FFFFFF;letter-spacing:1px;">
                            Ber<span style="color:#E091A9;">Ruang</span>
                        </td>
                    </tr>
                    <tr>
                        <td bgcolor="#141414" style="background-color:#141414;border:1px solid #262626;">
                            <table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0">
                                <tr>
                                    <td class="padding" style="padding:32px 36px;font-family:Arial,Helvetica,sans-serif;font-size:14px;line-height:1.7;color:#D4D4D4;">
                                        {{ $slot }}
                                    </td>
                                </tr>
                            </table>
                        </td>
                    </tr>
                    <tr>
                        <td align="center" style="padding:24px 16px 0;font-family:Arial,Helvetica,sans-serif;font-size:11px;line-height:1.8;color:#6B6B6B;">
                            &copy; {{ date('Y') }} BerRuang. All rights reserved.<br>
                            This email was sent automatically. Please do not reply.
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>
</body>
</html>
