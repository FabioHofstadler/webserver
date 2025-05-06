<?php
$host = 'localhost';
$db   = 'deinedatenbank';
$user = 'deinbenutzer';
$pass = 'deinpasswort';
$dsn  = "pgsql:host=$host;dbname=$db";

$email = $_POST['email'];
$pass1 = $_POST['password'];
$pass2 = $_POST['confirm_password'];

if ($pass1 !== $pass2) {
    die("Passwörter stimmen nicht überein.");
}

try {
    $pdo = new PDO($dsn, $user, $pass);

    $stmt = $pdo->prepare("SELECT COUNT(*) FROM users WHERE email = :email");
    $stmt->execute([':email' => $email]);
    if ($stmt->fetchColumn() > 0) {
        die("Benutzer mit dieser E-Mail existiert bereits.");
    }

    $token = bin2hex(random_bytes(16));
    $hashed = password_hash($pass1, PASSWORD_DEFAULT);

    $roleStmt = $pdo->prepare("SELECT id FROM roles WHERE name = :role");
    $roleName = $email === 'admin@example.com' ? 'admin' : 'reader';
    $roleStmt->execute([':role' => $roleName]);
    $role_id = $roleStmt->fetchColumn();

    $stmt = $pdo->prepare("
        INSERT INTO users (email, password_hash, role_id, verify_token)
        VALUES (:email, :password, :role_id, :token)
    ");
    $stmt->execute([
        ':email' => $email,
        ':password' => $hashed,
        ':role_id' => $role_id,
        ':token' => $token
    ]);

    $link = "http://your-domain.de/verify_email.php?token=$token";
    mail($email, "E-Mail bestätigen", "Bitte bestätige deine E-Mail durch Klick auf diesen Link:\n$link");

    echo "Registrierung erfolgreich! Bitte bestätige deine E-Mail.";
} catch (PDOException $e) {
    die("Fehler: " . $e->getMessage());
}
?>
