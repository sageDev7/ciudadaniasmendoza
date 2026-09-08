<?php
header('Content-Type: application/json; charset=utf-8');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'error' => 'method_not_allowed']);
    exit;
}

// Honeypot anti-spam: campo oculto que solo un bot completaría.
if (!empty($_POST['website'])) {
    echo json_encode(['success' => true]);
    exit;
}

function clean($value) {
    return trim(strip_tags($value ?? ''));
}

$nombre   = clean($_POST['nombre'] ?? '');
$telefono = clean($_POST['telefono'] ?? '');
$email    = clean($_POST['email'] ?? '');
$mensaje  = clean($_POST['mensaje'] ?? '');

if ($nombre === '' || $email === '' || $mensaje === '' || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
    http_response_code(400);
    echo json_encode(['success' => false, 'error' => 'invalid_fields']);
    exit;
}

$to      = 'alam.estudiojuridico@hotmail.com';
$subject = '=?UTF-8?B?' . base64_encode('Nueva consulta desde el sitio web - ' . $nombre) . '?=';

$body  = "Nueva consulta recibida desde el formulario del sitio web\n\n";
$body .= "Nombre: $nombre\n";
$body .= "Teléfono: " . ($telefono !== '' ? $telefono : '-') . "\n";
$body .= "Email: $email\n\n";
$body .= "Mensaje:\n$mensaje\n";

$headers   = [];
$headers[] = 'From: Estudio de Ciudadanías <no-reply@ciudadaniasmendoza.com>';
$headers[] = "Reply-To: $nombre <$email>";
$headers[] = 'Content-Type: text/plain; charset=UTF-8';
$headers[] = 'X-Mailer: PHP/' . phpversion();

$sent = mail($to, $subject, $body, implode("\r\n", $headers));

if ($sent) {
    echo json_encode(['success' => true]);
} else {
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => 'send_failed']);
}
