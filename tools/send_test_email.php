<?php
require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../includes/mail.php';

$to = 'fritziecadao@gmail.com';
$subject = 'Test email from F&L Glam Co';
$html = '<h3>Test</h3><p>This is a test email sent from the local app.</p>';

$res = smtp_send_mail($to, $subject, $html);

if (!empty($res['success']) && $res['success'] === true) {
    echo "Email sent OK\n";
} else {
    echo "Email failed: " . ($res['error'] ?? 'unknown') . "\n";
}
