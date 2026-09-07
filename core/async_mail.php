<?php
ignore_user_abort(true);
set_time_limit(0);

require_once __DIR__ . '/mailer.php';

$to = $_POST['to'] ?? '';
$name = $_POST['name'] ?? '';
$subject = $_POST['subject'] ?? '';
$body = $_POST['body'] ?? '';

if (!empty($to) && !empty($subject) && !empty($body)) {
    // Add a small delay to ensure the calling script has closed the connection
    sleep(1);
    sendMail($to, $name, $subject, $body);
}
