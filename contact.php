<?php
// =============================================
// Contact Form Mail Handler
// To: sammehtasam@gmail.com
// =============================================

header('Content-Type: application/json; charset=UTF-8');

// Only allow POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Method not allowed.']);
    exit;
}

// Sanitize & validate inputs
$name    = htmlspecialchars(strip_tags(trim($_POST['name']    ?? '')), ENT_QUOTES, 'UTF-8');
$email   = filter_var(trim($_POST['email']   ?? ''), FILTER_SANITIZE_EMAIL);
$subject = htmlspecialchars(strip_tags(trim($_POST['subject'] ?? 'Portfolio Enquiry')), ENT_QUOTES, 'UTF-8');
$service = htmlspecialchars(strip_tags(trim($_POST['service'] ?? 'Not specified')),     ENT_QUOTES, 'UTF-8');
$message = htmlspecialchars(strip_tags(trim($_POST['message'] ?? '')),                 ENT_QUOTES, 'UTF-8');

// Required field checks
if (empty($name) || strlen($name) < 2) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Please enter your full name.']);
    exit;
}
if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Please enter a valid email address.']);
    exit;
}
if (empty($message) || strlen($message) < 10) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Message must be at least 10 characters.']);
    exit;
}

// Recipient
$to          = 'sammehtasam@gmail.com';
$mail_subject = 'Portfolio Contact: ' . $subject;

// Plain-text email body
$body  = "You have received a new message from your portfolio website.\n";
$body .= str_repeat('-', 50) . "\n\n";
$body .= "Name    : {$name}\n";
$body .= "Email   : {$email}\n";
$body .= "Service : {$service}\n";
$body .= "Subject : {$subject}\n\n";
$body .= "Message :\n{$message}\n\n";
$body .= str_repeat('-', 50) . "\n";
$body .= "Sent from sumanmehta.dev portfolio contact form.\n";

// Headers
$headers  = "MIME-Version: 1.0\r\n";
$headers .= "Content-Type: text/plain; charset=UTF-8\r\n";
$headers .= "From: Portfolio Contact <noreply@sumanmehta.dev>\r\n";
$headers .= "Reply-To: {$name} <{$email}>\r\n";
$headers .= "X-Mailer: PHP/" . phpversion() . "\r\n";

// Send
if (mail($to, $mail_subject, $body, $headers)) {
    echo json_encode(['success' => true, 'message' => 'Your message has been sent! I will get back to you shortly.']);
} else {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Mail could not be sent from this server. Please email me directly at sammehtasam@gmail.com'
    ]);
}
