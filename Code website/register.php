<!DOCTYPE html>
<html lang="de">
<head>
    <meta charset="UTF-8">
    <title>Registrieren</title>
</head>
<body>
<h2>Neuen Benutzer registrieren</h2>
<form action="register_submit.php" method="post">
    <label for="email">E-Mail:</label><br>
    <input type="email" name="email" required><br><br>

    <label for="password">Passwort:</label><br>
    <input type="password" name="password" required><br><br>

    <label for="confirm_password">Passwort wiederholen:</label><br>
    <input type="password" name="confirm_password" required><br><br>

    <input type="submit" value="Registrieren">
</form>
</body>
</html>
