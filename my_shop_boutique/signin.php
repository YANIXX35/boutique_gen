<?php
    session_start();
    require_once 'classes/Database.php';
    require_once 'classes/User.php';

    $base_donnees = new Database();
    $base_D       = $base_donnees->recupConnexion();
    $utilisateur  = new User($base_D);

    $error = "";

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $email    = trim($_POST['email']    ?? '');
        $password = trim($_POST['password'] ?? '');

        if (empty($email) || empty($password)) {
            $error = "Veuillez remplir tous les champs.";
        } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
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
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Connexion — My Shop</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
    <link rel="stylesheet" href="style.css">
</head>
<body class="auth-page">
    <div class="auth-card">

        <div class="brand-logo">
            <i class="bi bi-bag-heart-fill"></i>
            <span class="brand-title">Y.E.F Shop</span>
        </div>
        <div class="brand-sub">Connectez-vous à votre compte</div>

        <?php if (isset($_GET['success'])): ?>
            <div class="alerte-succes">
                <i class="bi bi-check-circle"></i> Inscription réussie ! Connectez-vous.
            </div>
        <?php endif; ?>

        <?php if ($error): ?>
            <div class="alerte-erreur">
                <i class="bi bi-exclamation-circle"></i>
                <?php echo htmlspecialchars($error); ?>
            </div>
        <?php endif; ?>

        <form method="post" novalidate>
            <div class="mb-3">
                <label class="form-label">Email</label>
                <div class="input-group">
                    <span class="input-group-text"><i class="bi bi-envelope"></i></span>
                    <input type="email" name="email" class="form-control"
                           placeholder="Entrez votre email"
                           value="<?php echo isset($_POST['email']) ? htmlspecialchars($_POST['email']) : ''; ?>"
                           required>
                </div>
            </div>

            <div class="mb-3">
                <label class="form-label">Mot de passe</label>
                <div class="input-group">
                    <span class="input-group-text"><i class="bi bi-lock"></i></span>
                    <input type="password" name="password" class="form-control"
                           placeholder="Entrez votre mot de passe" required>
                </div>
            </div>

            <button type="submit" class="btn-connexion">
                Se connecter <i class="bi bi-arrow-right"></i>
            </button>
        </form>

        <hr class="divider">

        <div class="lien-secondaire">
            Pas encore de compte ? <a href="signup.php">Créer un compte</a>
        </div>
        <div class="lien-secondaire mt-2">
            <a href="index.php"><i class="bi bi-house me-1"></i>Retour à la boutique</a>
        </div>
    </div>
</body>
</html>