<?php
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
        } else {
            $connexionReussie = $utilisateur->seConnecter($email, $password);

            if ($connexionReussie) {
                if ($_SESSION['is_admin'] == 1) {
                    header("Location: admin.php");
                } else {
                    header("Location: index.php");
                }
                exit;
            }

            $error = "Email ou mot de passe incorrect.";
        }
    }
    ?>

    <!DOCTYPE html>
    <html>
    <head><title>Connexion</title></head>
    <body>
        <h2>Connexion</h2>
        <?php if(isset($_GET['success'])) echo "<p style='color:green'>Inscription réussie ! Connectez-vous.</p>"; ?>
        <p style="color:red"><?php echo $error; ?></p>
        <form method="post">
            <input type="email" name="email" placeholder="Email" required><br>
            <input type="password" name="password" placeholder="Mot de passe" required><br>
            <button type="submit">Se connecter</button>
        </form>
    </body>
    </html>