// config.php
<?php
$host = 'localhost';
$db   = 'dein_kalender';
$user = 'dein_user';
$pass = 'dein_passwort';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$db;charset=utf8mb4", $user, $pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Verbindung fehlgeschlagen: " . $e->getMessage());
}
?>


// register.php
<?php
require 'config.php';

$email = $_POST['email'];
$password = $_POST['password'];
$confirm = $_POST['confirm_password'];

if ($password !== $confirm) {
    die("Passwörter stimmen nicht überein.");
}

$role = ($email === 'admin@htl-leoben.at') ? 'admin' : 'reader';
$hash = password_hash($password, PASSWORD_DEFAULT);
$token = bin2hex(random_bytes(32));

$stmt = $pdo->prepare("INSERT INTO users (email, password_hash, role, verification_token) VALUES (?, ?, ?, ?)");
$stmt->execute([$email, $hash, $role, $token]);

$verifyLink = "http://dein-server/pfad/verify.php?token=$token";
// mail($email, "E-Mail bestätigen", "Bitte hier klicken: $verifyLink");

echo "Registrierung erfolgreich. Bitte E-Mail bestätigen.";
?>


// verify.php
<?php
require 'config.php';

$token = $_GET['token'];
$stmt = $pdo->prepare("UPDATE users SET is_verified = 1, verification_token = NULL WHERE verification_token = ?");
$stmt->execute([$token]);

if ($stmt->rowCount()) {
    echo "E-Mail bestätigt. Du kannst dich jetzt anmelden.";
} else {
    echo "Ungültiger oder bereits verwendeter Token.";
}
?>


// login.php
<?php
session_start();
require 'config.php';

$email = $_POST['email'];
$password = $_POST['password'];

$stmt = $pdo->prepare("SELECT * FROM users WHERE email = ?");
$stmt->execute([$email]);
$user = $stmt->fetch();

if ($user && $user['is_verified'] && password_verify($password, $user['password_hash'])) {
    $_SESSION['user_id'] = $user['id'];
    $_SESSION['role'] = $user['role'];
    echo "Erfolgreich angemeldet!";
} else {
    echo "Falsche Daten oder nicht bestätigt.";
}
?>


// calendar_api.php
<?php
session_start();
require 'config.php';

if (!isset($_SESSION['user_id'])) {
    http_response_code(403);
    exit('Nicht eingeloggt');
}

$action = $_GET['action'];

if ($action === 'fetch') {
    $stmt = $pdo->query("SELECT * FROM events ORDER BY start");
    echo json_encode($stmt->fetchAll(PDO::FETCH_ASSOC));
} elseif ($action === 'add' && $_SESSION['role'] === 'admin') {
    $data = json_decode(file_get_contents('php://input'), true);
    $stmt = $pdo->prepare("INSERT INTO events (title, description, start, end, created_by) VALUES (?, ?, ?, ?, ?)");
    $stmt->execute([$data['title'], $data['desc'], $data['start'], $data['end'], $_SESSION['user_id']]);
    echo "Termin erstellt.";
}
?>
