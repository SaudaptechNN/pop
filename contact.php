<?php
require __DIR__ . '/includes/bootstrap.php';

$back = url('index.php') . '#event-contact';
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirect($back);
}

$input = [
    'name'    => trim((string) ($_POST['name'] ?? '')),
    'email'   => trim((string) ($_POST['email'] ?? '')),
    'event'   => trim((string) ($_POST['event'] ?? '')),
    'date'    => trim((string) ($_POST['date'] ?? '')),
    'message' => trim((string) ($_POST['message'] ?? '')),
];

// Bots fill the hidden "website" field; pretend success and drop it.
if (!empty($_POST['website'])) {
    $_SESSION['inquiry_status'] = 'sent';
    redirect($back);
}

$errors = [];
if (!csrf_valid()) {
    $errors[] = 'Your session expired. Please submit the form again.';
}
if ($input['name'] === '' || mb_strlen($input['name']) > 150) {
    $errors[] = 'Please enter your name.';
}
if (!filter_var($input['email'], FILTER_VALIDATE_EMAIL) || mb_strlen($input['email']) > 190) {
    $errors[] = 'Please enter a valid email address.';
}
if ($input['event'] === '' || mb_strlen($input['event']) > 190) {
    $errors[] = 'Please tell us the event type.';
}
$eventDate = null;
if ($input['date'] !== '') {
    $d = DateTime::createFromFormat('Y-m-d', $input['date']);
    if (!$d || $d->format('Y-m-d') !== $input['date']) {
        $errors[] = 'Please choose a valid event date.';
    } else {
        $eventDate = $input['date'];
    }
}
if ($input['message'] === '' || mb_strlen($input['message']) > 5000) {
    $errors[] = 'Please tell us about your vision.';
}
// Basic flood protection: one inquiry per 30 seconds per visitor.
if (!$errors && isset($_SESSION['last_inquiry']) && time() - $_SESSION['last_inquiry'] < 30) {
    $errors[] = 'Please wait a moment before sending another inquiry.';
}

if ($errors) {
    $_SESSION['inquiry_errors'] = $errors;
    $_SESSION['inquiry_old'] = $input;
    redirect($back);
}

$stmt = db()->prepare('INSERT INTO inquiries (name, email, event_type, event_date, message, ip_address) VALUES (?, ?, ?, ?, ?, ?)');
$stmt->execute([$input['name'], $input['email'], $input['event'], $eventDate, $input['message'], substr($_SERVER['REMOTE_ADDR'] ?? '', 0, 45)]);
$_SESSION['last_inquiry'] = time();

// Email alert. Fails silently when the server has no mail setup (e.g. local XAMPP).
$notify = setting('notify_email');
if (filter_var($notify, FILTER_VALIDATE_EMAIL)) {
    $subject = 'New event inquiry from ' . preg_replace('/[\r\n]+/', ' ', $input['name']);
    $body = "Name: {$input['name']}\nEmail: {$input['email']}\nEvent type: {$input['event']}\nEvent date: " . ($eventDate ?: 'Not specified') . "\n\nVision:\n{$input['message']}\n";
    $headers = "Reply-To: {$input['email']}\r\nContent-Type: text/plain; charset=UTF-8";
    @mail($notify, $subject, $body, $headers);
}

$_SESSION['inquiry_status'] = 'sent';
redirect($back);
