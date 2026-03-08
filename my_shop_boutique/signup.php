<?php
    session_start();
    require_once 'classes/Database.php';
    require_once 'classes/User.php';

    $base_donnees = new Database();
    $base_D       = $base_donnees->recupConnexion();
    $utilisateur  = new User($base_D);

    $message = "";

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $username = trim($_POST['username'] ?? '');
        $email    = trim($_POST['email']    ?? '');
        $password = trim($_POST['password'] ?? '');

        if (empty($username) || empty($email) || empty($password)) {
            $message = "Veuillez remplir tous les champs.";
        } elseif (strlen($username) < 3) {
            $message = "Le nom d'utilisateur doit contenir au moins 3 caractères.";
        } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $message = "L'adresse email n'est pas valide.";
        } elseif (strlen($password) < 6) {
            $message = "Le mot de passe doit contenir au moins 6 caractères.";
        } else {
            $result = $utilisateur->creerCompte($username, $email, $password);
            if ($result === true) {
                header("Location: signin.php?success=1");
                exit;
            }
            $message = $result;
        }
    }
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Inscription — Y.E.F Shop</title>
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
        <div class="brand-sub">Créer un nouveau compte</div>

        <?php if ($message): ?>
            <div class="alerte-erreur">
                <i class="bi bi-exclamation-circle"></i>
                <?php echo htmlspecialchars($message); ?>
            </div>
        <?php endif; ?>

        <form method="post" novalidate>
            <div class="mb-3">
                <label class="form-label">Nom d'utilisateur</label>
                <div class="input-group">
                    <span class="input-group-text"><i class="bi bi-person"></i></span>
                    <input type="text" name="username" class="form-control"
                           placeholder="Entrez votre nom"
                           value="<?php echo isset($_POST['username']) ? htmlspecialchars($_POST['username']) : ''; ?>"
                           required minlength="3">
                </div>
                <div class="form-text">Minimum 3 caractères</div>
            </div>

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
                           placeholder="Entrez votre mot de passe   " required minlength="6">
                </div>
                <div class="form-text">Minimum 6 caractères</div>
            </div>

            <button type="submit" class="btn-inscription">
                Créer mon compte <i class="bi bi-arrow-right"></i>
            </button>
        </form>

        <hr class="divider">

        <div class="lien-secondaire">
            Déjà un compte ? <a href="signin.php">Se connecter</a>
        </div>
        <div class="lien-secondaire mt-2">
            <a href="index.php"><i class="bi bi-house me-1"></i>Retour à la boutique</a>
        </div>
    </div>
</body>
</html>