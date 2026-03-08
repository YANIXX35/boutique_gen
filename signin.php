<?php
session_start(); // OBLIGATOIRE : doit être tout en haut, avant tout autre code
require_once 'classes/Database.php';
require_once 'classes/User.php';

$base_donnees = new Database();
$base_D = $base_donnees->recupConnexion();
$utilisateur = new User($base_D);

$error = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = $_POST['email'];
    $password = $_POST['password'];

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = "Email invalide.";
    }
    else {
        $connexionReussie = $utilisateur->seConnecter($email, $password);

        if ($connexionReussie) {
            if ($_SESSION['is_admin'] == 1) {
                header("Location: admin.php");
            }
            else {
                header("Location: index.php");
            }
            exit;
        }

        $error = "Email ou mot de passe incorrect.";
    }
}
?>

    <!DOCTYPE html>
    <html lang="fr">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Connexion - My Shop</title>
        <link rel="stylesheet" href="auth.css">
    </head>
    <body>
        <div class="auth-conteneur">
            <div class="auth-boite">
                <h2>Connexion</h2>

                <?php if (isset($_GET['success'])): ?>
                    <p class="message-succes">Inscription réussie ! Connectez-vous.</p>
                <?php
endif; ?>

                <?php if ($error): ?>
                    <p class="message-erreur"><?php echo htmlspecialchars($error); ?></p>
                <?php
endif; ?>

                <form method="post" class="formulaire-auth">
                    <div class="champ-formulaire">
                        <label for="email">Email</label>
                        <input type="email" id="email" name="email" placeholder="votre@email.com" required>
                    </div>
                    <div class="champ-formulaire">
                        <label for="password">Mot de passe</label>
                        <input type="password" id="password" name="password" placeholder="••••••••" required>
                    </div>
                    <button type="submit" class="btn-soumettre">Se connecter</button>
                </form>

                <p class="lien-bas">Pas encore de compte ? <a href="signup.php">S'inscrire</a></p>
            </div>
        </div>
    </body>
    </html>