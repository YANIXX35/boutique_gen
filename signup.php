<?php
    require_once 'classes/Database.php';
    require_once 'classes/User.php';

    $base_donnees = new Database();
    $base_D = $base_donnees->recupConnexion();
    $utilisateur = new User($base_D);

    $message = "";

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $username = $_POST['username'];
        $email = $_POST['email'];
        $password = $_POST['password'];

        $result = $utilisateur->creerCompte($username, $email, $password);

        if ($result === true) {
            header("Location: signin.php?success=1");
            exit;
        }

        $message = $result;
    }
    ?>

    <!DOCTYPE html>
    <html>
    <head>
        <title>Inscription</title>
        <link rel="stylesheet" href="style.css">
    </head>
    <body>
        <h2>Créer un compte</h2>
        <p style="color:red"><?php echo htmlspecialchars($message); ?></p>
        <form method="post">
            <input type="text" name="username" placeholder="Nom d'utilisateur" required><br>
            <input type="email" name="email" placeholder="Email" required><br>
            <input type="password" name="password" placeholder="Mot de passe" required><br>
            <button type="submit">S'inscrire</button>
        </form>
        <a href="signin.php">Déjà un compte ? Connectez-vous</a>
    </body>
    </html>