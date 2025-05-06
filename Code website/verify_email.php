<?php
$token = $_GET['token'];

$pdo = new PDO("pgsql:host=localhost;dbname=deinedatenbank", "deinbenutzer", "deinpasswort");

$stmt = $pdo->prepare("UPDATE users SET is_verified = TRUE, verify_token = NULL WHERE verify_token = :token");
$stmt->execute([':token' => $token]);

if ($stmt->rowCount()) {
    echo "E-Mail bestätigt! Du kannst dich jetzt einloggen.";
} else {
    echo "Ungültiger oder bereits verwendeter Bestätigungslink.";
}
?>
