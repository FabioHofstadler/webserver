<?php
$pdo = new PDO("pgsql:host=localhost;dbname=deinedatenbank", "deinbenutzer", "deinpasswort");

$email = $_POST['email'] ?? '';
$password = $_POST['password'] ?? '';

$stmt = $pdo->prepare("SELECT * FROM users WHERE email = :email AND is_verified = TRUE");
$stmt->execute([':email' => $email]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);

if ($user && password_verify($password, $user['password_hash'])) {
    echo "Login erfolgreich! Willkommen, " . htmlspecialchars($user['email']) . ".";
} else {
    echo "Login fehlgeschlagen oder E-Mail noch nicht bestätigt.";
}
?>
