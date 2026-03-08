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
    <html lang="fr">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Inscription - My Shop</title>
        <link rel="stylesheet" href="auth.css">
    </head>
    <body>
        <div class="auth-conteneur">
            <div class="auth-boite">
                <h2>Créer un compte</h2>

                <?php if ($message): ?>
                    <p class="message-erreur"><?php echo htmlspecialchars($message); ?></p>
                <?php
endif; ?>

                <form method="post" class="formulaire-auth">
                    <div class="champ-formulaire">
                        <label for="username">Nom d'utilisateur</label>
                        <input type="text" id="username" name="username" placeholder="Votre nom" required>
                    </div>
                    <div class="champ-formulaire">
                        <label for="email">Email</label>
                        <input type="email" id="email" name="email" placeholder="votre@email.com" required>
                    </div>
                    <div class="champ-formulaire">
                        <label for="password">Mot de passe</label>
                        <input type="password" id="password" name="password" placeholder="••••••••" required>
                    </div>
                    <button type="submit" class="btn-soumettre">S'inscrire</button>
                </form>

                <p class="lien-bas">Déjà un compte ? <a href="signin.php">Se connecter</a></p>
            </div>
        </div>
    </body>
    </html>