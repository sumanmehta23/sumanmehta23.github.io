<?php
/**
 * Contact Form Handler — PHPMailer + Gmail SMTP
 * -----------------------------------------------
 * SETUP (one-time):
 *   1. Go to myaccount.google.com/apppasswords
 *   2. Create an App Password → copy the 16-character code (no spaces)
 *   3. Replace YOUR_APP_PASSWORD_HERE below with that code
 */

// ── CONFIGURATION ─────────────────────────────────────────────────────────────
define('GMAIL_ADDRESS',      'sammehtasam@gmail.com');
define('GMAIL_APP_PASSWORD', 'dqqf gvnm slkc sdsm'); // <-- paste App Password here
define('RECIPIENT_NAME',     'Suman Mehta');
// ──────────────────────────────────────────────────────────────────────────────

header('Content-Type: application/json');
header('X-Content-Type-Options: nosniff');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Method not allowed.']);
    exit;
}

require_once __DIR__ . '/vendor/autoload.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

// ── SANITISE & VALIDATE ───────────────────────────────────────────────────────
$name    = trim(strip_tags($_POST['name']    ?? ''));
$email   = trim(strip_tags($_POST['email']   ?? ''));
$subject = trim(strip_tags($_POST['subject'] ?? ''));
$service = trim(strip_tags($_POST['service'] ?? ''));
$message = trim(strip_tags($_POST['message'] ?? ''));

$errors = [];
if (strlen($name) < 2)                             $errors[] = 'Please enter your full name.';
if (!filter_var($email, FILTER_VALIDATE_EMAIL))    $errors[] = 'Please enter a valid email address.';
if (strlen($subject) < 2)                          $errors[] = 'Please enter a subject.';
if (strlen($message) < 10)                         $errors[] = 'Message must be at least 10 characters.';

if (!empty($errors)) {
    http_response_code(422);
    echo json_encode(['success' => false, 'message' => implode(' ', $errors)]);
    exit;
}

if (GMAIL_APP_PASSWORD === 'YOUR_APP_PASSWORD_HERE') {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Contact form is not yet configured. Please email sammehtasam@gmail.com directly.'
    ]);
    exit;
}

// ── SEND VIA GMAIL SMTP ───────────────────────────────────────────────────────
$mail = new PHPMailer(true);

try {
    $mail->isSMTP();
    $mail->Host       = 'smtp.gmail.com';
    $mail->SMTPAuth   = true;
    $mail->Username   = GMAIL_ADDRESS;
    $mail->Password   = GMAIL_APP_PASSWORD;
    $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;
    $mail->Port       = 465;
    $mail->CharSet    = 'UTF-8';

    $mail->setFrom(GMAIL_ADDRESS, RECIPIENT_NAME . ' Portfolio');
    $mail->addAddress(GMAIL_ADDRESS, RECIPIENT_NAME);
    $mail->addReplyTo($email, $name);

    $serviceNote = $service ? " [{$service}]" : '';
    $mail->Subject = "Portfolio Contact{$serviceNote}: {$subject}";

    $mail->AltBody = "New message from your portfolio.\n\n"
        . "Name:    {$name}\n"
        . "Email:   {$email}\n"
        . "Service: " . ($service ?: 'Not specified') . "\n"
        . "Subject: {$subject}\n\n"
        . "Message:\n{$message}";

    $htmlService = $service
        ? "<tr><td style='padding:6px 0;color:#6b7280;font-size:14px;width:90px'>Service</td>"
          . "<td style='padding:6px 0;font-size:14px'>" . htmlspecialchars($service) . "</td></tr>"
        : '';

    $mail->isHTML(true);
    $mail->Body = "<!DOCTYPE html><html><head><meta charset='UTF-8'></head>
<body style='margin:0;padding:0;background:#f0f2f8;font-family:Arial,sans-serif'>
<table width='100%' cellpadding='0' cellspacing='0' style='background:#f0f2f8;padding:30px 0'>
<tr><td align='center'>
<table width='580' cellpadding='0' cellspacing='0' style='background:#fff;border-radius:10px;overflow:hidden;box-shadow:0 4px 20px rgba(0,0,0,0.08)'>
<tr><td style='background:linear-gradient(135deg,#1b2a47,#2e4170);padding:28px 36px'>
  <h1 style='margin:0;color:#fff;font-size:22px'>&lt;SM/&gt; Portfolio</h1>
  <p style='margin:5px 0 0;color:#90c6f0;font-size:13px'>New contact form submission</p>
</td></tr>
<tr><td style='padding:32px 36px'>
  <p style='margin:0 0 20px;color:#374151;font-size:15px'>
    Hi <strong>Suman</strong>, you have a new message from your portfolio:
  </p>
  <table width='100%' style='background:#f8f9fc;border-radius:8px;padding:20px 24px;margin-bottom:24px;border-left:4px solid #6c63ff'>
    <tr><td style='padding:6px 0;color:#6b7280;font-size:14px;width:90px'>Name</td>
        <td style='padding:6px 0;font-size:14px;font-weight:600;color:#1e2d3d'>" . htmlspecialchars($name) . "</td></tr>
    <tr><td style='padding:6px 0;color:#6b7280;font-size:14px'>Email</td>
        <td style='padding:6px 0;font-size:14px'><a href='mailto:" . htmlspecialchars($email) . "' style='color:#6c63ff'>" . htmlspecialchars($email) . "</a></td></tr>
    {$htmlService}
    <tr><td style='padding:6px 0;color:#6b7280;font-size:14px'>Subject</td>
        <td style='padding:6px 0;font-size:14px;color:#1e2d3d'>" . htmlspecialchars($subject) . "</td></tr>
  </table>
  <h3 style='margin:0 0 10px;color:#1b2a47;font-size:15px'>Message:</h3>
  <div style='background:#f8f9fc;border-radius:8px;padding:20px 24px;color:#374151;font-size:14px;line-height:1.7;border:1px solid #e2e8f0'>
    " . nl2br(htmlspecialchars($message)) . "
  </div>
  <div style='margin-top:28px;text-align:center'>
    <a href='mailto:" . htmlspecialchars($email) . "' style='display:inline-block;padding:12px 28px;background:linear-gradient(135deg,#6c63ff,#4a90d9);color:#fff;text-decoration:none;border-radius:8px;font-size:14px;font-weight:600'>
      Reply to " . htmlspecialchars($name) . "
    </a>
  </div>
</td></tr>
<tr><td style='background:#f8f9fc;padding:16px 36px;border-top:1px solid #e2e8f0;text-align:center'>
  <p style='margin:0;color:#9ca3af;font-size:12px'>Sent from your portfolio contact form</p>
</td></tr>
</table></td></tr></table>
</body></html>";

    $mail->send();
    echo json_encode([
        'success' => true,
        'message' => "Thank you {$name}! Your message has been sent. I'll get back to you within 24 hours."
    ]);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Could not send message. Please email sammehtasam@gmail.com directly.'
    ]);
}
