<x-emails.layout title="Email Verification" preheader="Your 6-digit verification code">
    <p style="margin:0 0 20px;">Hi {{ $recipientName }},</p>
    <p style="margin:0 0 28px;">Your BerRuang verification code is:</p>
    <p style="margin:0 0 28px;text-align:center;">
        <span class="code" style="display:inline-block;font-family:Consolas,Menlo,Monaco,monospace;font-size:32px;font-weight:700;letter-spacing:10px;color:#E091A9;">{{ $code }}</span>
    </p>
    <p style="margin:0;font-size:12px;color:#8A8A8A;">This code expires in 10 minutes. If you didn't create a BerRuang account, you can safely ignore this email.</p>
</x-emails.layout>
