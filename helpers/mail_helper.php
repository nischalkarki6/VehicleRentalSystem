<?php

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require_once __DIR__ . '/../vendor/autoload.php';

class EmailHandler
{
    private string $smtpHost;
    private int    $smtpPort;
    private string $smtpUser;
    private string $smtpPass;
    private string $fromEmail;
    private string $fromName;
    private string $appName;

    public function __construct()
    {
        $this->smtpHost  = $_ENV['SMTP_HOST']       ?? 'smtp.gmail.com';
        $this->smtpPort  = (int)($_ENV['SMTP_PORT']  ?? 587);
        $this->smtpUser  = $_ENV['SMTP_USERNAME']    ?? '';
        $this->smtpPass  = $_ENV['SMTP_PASSWORD']    ?? '';
        $this->fromEmail = $_ENV['SMTP_FROM_EMAIL']  ?? '';
        $this->fromName  = $_ENV['SMTP_FROM_NAME']   ?? 'DriveEase';
        $this->appName   = $_ENV['APP_NAME']         ?? 'DriveEase';
    }

    private function createMailer(): PHPMailer
    {
        $mail = new PHPMailer(true);

        $mail->isSMTP();
        $mail->Host       = $this->smtpHost;
        $mail->SMTPAuth   = true;
        $mail->Username   = $this->smtpUser;
        $mail->Password   = $this->smtpPass;
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port       = $this->smtpPort;
        $mail->CharSet    = 'UTF-8';

        $mail->setFrom($this->fromEmail, $this->fromName);

        return $mail;
    }

    public function sendVerificationEmail(
        string $recipientEmail,
        string $recipientName,
        string $otp
    ): bool {
        $subject = 'Verify Your Email - ' . $this->appName;
        $body    = $this->buildVerificationTemplate($recipientName, $otp);

        return $this->send($recipientEmail, $recipientName, $subject, $body);
    }

    public function sendPasswordResetOtpEmail(
        string $recipientEmail,
        string $recipientName,
        string $otp
    ): bool {
        $subject = 'Password Reset Code - ' . $this->appName;
        $body    = $this->buildResetOtpTemplate($recipientName, $otp);

        return $this->send($recipientEmail, $recipientName, $subject, $body);
    }

    public function sendLoginOtpEmail(
        string $recipientEmail,
        string $recipientName,
        string $otp
    ): bool {
        $subject = 'Login Verification Code - ' . $this->appName;
        $body    = $this->buildLoginOtpTemplate($recipientName, $otp);

        return $this->send($recipientEmail, $recipientName, $subject, $body);
    }

    private function send(
        string $toEmail,
        string $toName,
        string $subject,
        string $htmlBody
    ): bool {
        try {
            $mail = $this->createMailer();
            $mail->addAddress($toEmail, $toName);
            $mail->isHTML(true);
            $mail->Subject = $subject;
            $mail->Body    = $htmlBody;
            $mail->AltBody = strip_tags(
                str_replace(['<br>', '<br/>', '<br />'], "\n", $htmlBody)
            );

            $mail->send();
            return true;
        } catch (Exception $e) {
            error_log('[EmailHandler] Failed to send to ' . $toEmail . ': ' . $e->getMessage());
            return false;
        }
    }

    private function buildVerificationTemplate(string $name, string $otp): string
    {
        $year = date('Y');
        return <<<HTML
<!DOCTYPE html>
<html lang="en">
<head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1.0"></head>
<body style="margin:0;padding:0;background-color:#f7f8fa;font-family:'Segoe UI',Roboto,'Helvetica Neue',Arial,sans-serif;">
<table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="background-color:#f7f8fa;padding:40px 0;">
<tr><td align="center">
<table role="presentation" width="560" cellpadding="0" cellspacing="0" style="background:#ffffff;border-radius:16px;box-shadow:0 4px 24px rgba(0,0,0,0.06);overflow:hidden;">
  <!-- Header -->
  <tr>
    <td style="background:linear-gradient(135deg,#0f1115 0%,#1e2330 100%);padding:40px 48px;text-align:center;">
      <h1 style="margin:0;color:#ffffff;font-size:22px;font-weight:800;letter-spacing:-0.5px;">{$this->appName}</h1>
      <p style="margin:8px 0 0;color:rgba(255,255,255,0.6);font-size:11px;text-transform:uppercase;letter-spacing:2px;font-weight:700;">Email Verification</p>
    </td>
  </tr>
  <!-- Body -->
  <tr>
    <td style="padding:48px;">
      <p style="margin:0 0 8px;font-size:13px;color:#6b7280;font-weight:600;text-transform:uppercase;letter-spacing:1px;">Welcome aboard</p>
      <h2 style="margin:0 0 24px;font-size:24px;color:#0f1115;font-weight:800;letter-spacing:-0.5px;">Hi {$name},</h2>
      <p style="margin:0 0 28px;font-size:15px;color:#374151;line-height:1.7;">
        Thank you for joining <strong>{$this->appName}</strong>! Use this one-time code to verify your email address and activate your account.
      </p>
      <div style="margin:0 auto 28px;text-align:center;background:#f0f2f5;border-radius:10px;padding:20px 24px;font-size:32px;font-weight:800;letter-spacing:8px;color:#0f1115;font-family:Consolas,Monaco,monospace;">
        {$otp}
      </div>
      <!-- Notice -->
      <div style="border-left:3px solid #e5e7eb;padding-left:16px;margin-bottom:0;">
        <p style="margin:0;font-size:13px;color:#9ca3af;line-height:1.6;">
          This code expires in <strong style="color:#6b7280;">60 minutes</strong>. If you did not create an account, you can safely ignore this email.
        </p>
      </div>
    </td>
  </tr>
  <!-- Footer -->
  <tr>
    <td style="background:#f9fafb;padding:24px 48px;border-top:1px solid #eaedf1;text-align:center;">
      <p style="margin:0;font-size:12px;color:#9ca3af;">&copy; {$year} {$this->appName}. All rights reserved.</p>
    </td>
  </tr>
</table>
</td></tr>
</table>
</body>
</html>
HTML;
    }

    private function buildLoginOtpTemplate(string $name, string $otp): string
    {
        $year = date('Y');
        return <<<HTML
<!DOCTYPE html>
<html lang="en">
<head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1.0"></head>
<body style="margin:0;padding:0;background-color:#f7f8fa;font-family:'Segoe UI',Roboto,'Helvetica Neue',Arial,sans-serif;">
<table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="background-color:#f7f8fa;padding:40px 0;">
<tr><td align="center">
<table role="presentation" width="560" cellpadding="0" cellspacing="0" style="background:#ffffff;border-radius:16px;box-shadow:0 4px 24px rgba(0,0,0,0.06);overflow:hidden;">
  <!-- Header -->
  <tr>
    <td style="background:linear-gradient(135deg,#0f1115 0%,#1e2330 100%);padding:40px 48px;text-align:center;">
      <h1 style="margin:0;color:#ffffff;font-size:22px;font-weight:800;letter-spacing:-0.5px;">{$this->appName}</h1>
      <p style="margin:8px 0 0;color:rgba(255,255,255,0.6);font-size:11px;text-transform:uppercase;letter-spacing:2px;font-weight:700;">Login Security</p>
    </td>
  </tr>
  <!-- Body -->
  <tr>
    <td style="padding:48px;">
      <p style="margin:0 0 8px;font-size:13px;color:#6b7280;font-weight:600;text-transform:uppercase;letter-spacing:1px;">Identity Verification</p>
      <h2 style="margin:0 0 24px;font-size:24px;color:#0f1115;font-weight:800;letter-spacing:-0.5px;">Hi {$name},</h2>
      <p style="margin:0 0 28px;font-size:15px;color:#374151;line-height:1.7;">
        Someone is trying to sign in to your <strong>{$this->appName}</strong> account. If this is you, use the security code below to complete your login.
      </p>
      <div style="margin:0 auto 28px;text-align:center;background:#fff9eb;border:1px solid #fee2e2;border-radius:10px;padding:20px 24px;font-size:32px;font-weight:800;letter-spacing:8px;color:#0f1115;font-family:Consolas,Monaco,monospace;">
        {$otp}
      </div>
      <!-- Warning -->
      <div style="border-left:3px solid #fbbf24;padding-left:16px;margin-bottom:0;">
        <p style="margin:0;font-size:13px;color:#92400e;line-height:1.6;">
          This code expires in <strong>10 minutes</strong>. If you did not attempt to sign in, we recommend changing your password immediately.
        </p>
      </div>
    </td>
  </tr>
  <!-- Footer -->
  <tr>
    <td style="background:#f9fafb;padding:24px 48px;border-top:1px solid #eaedf1;text-align:center;">
      <p style="margin:0;font-size:12px;color:#9ca3af;">&copy; {$year} {$this->appName}. All rights reserved.</p>
    </td>
  </tr>
</table>
</td></tr>
</table>
</body>
</html>
HTML;
    }

    private function buildResetOtpTemplate(string $name, string $otp): string
    {
        $year = date('Y');
        return <<<HTML
<!DOCTYPE html>
<html lang="en">
<head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1.0"></head>
<body style="margin:0;padding:0;background-color:#f7f8fa;font-family:'Segoe UI',Roboto,'Helvetica Neue',Arial,sans-serif;">
<table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="background-color:#f7f8fa;padding:40px 0;">
<tr><td align="center">
<table role="presentation" width="560" cellpadding="0" cellspacing="0" style="background:#ffffff;border-radius:16px;box-shadow:0 4px 24px rgba(0,0,0,0.06);overflow:hidden;">
  <tr>
    <td style="background:linear-gradient(135deg,#0f1115 0%,#1e2330 100%);padding:40px 48px;text-align:center;">
      <h1 style="margin:0;color:#ffffff;font-size:22px;font-weight:800;letter-spacing:-0.5px;">{$this->appName}</h1>
      <p style="margin:8px 0 0;color:rgba(255,255,255,0.6);font-size:11px;text-transform:uppercase;letter-spacing:2px;font-weight:700;">Password Reset</p>
    </td>
  </tr>
  <tr>
    <td style="padding:48px;">
      <p style="margin:0 0 8px;font-size:13px;color:#6b7280;font-weight:600;text-transform:uppercase;letter-spacing:1px;">Security Code</p>
      <h2 style="margin:0 0 24px;font-size:24px;color:#0f1115;font-weight:800;letter-spacing:-0.5px;">Hi {$name},</h2>
      <p style="margin:0 0 28px;font-size:15px;color:#374151;line-height:1.7;">
        Use this one-time code to continue resetting your <strong>{$this->appName}</strong> password.
      </p>
      <div style="margin:0 auto 28px;text-align:center;background:#f0f2f5;border-radius:10px;padding:20px 24px;font-size:32px;font-weight:800;letter-spacing:8px;color:#0f1115;font-family:Consolas,Monaco,monospace;">
        {$otp}
      </div>
      <div style="border-left:3px solid #fbbf24;padding-left:16px;margin-bottom:0;">
        <p style="margin:0;font-size:13px;color:#92400e;line-height:1.6;">
          This code expires in <strong>10 minutes</strong>. If you did not request a password reset, you can ignore this email.
        </p>
      </div>
    </td>
  </tr>
  <tr>
    <td style="background:#f9fafb;padding:24px 48px;border-top:1px solid #eaedf1;text-align:center;">
      <p style="margin:0;font-size:12px;color:#9ca3af;">&copy; {$year} {$this->appName}. All rights reserved.</p>
    </td>
  </tr>
</table>
</td></tr>
</table>
</body>
</html>
HTML;
    }

}
