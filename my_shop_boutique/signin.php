<?php
session_start();
require_once 'classes/Database.php';
require_once 'classes/User.php';

$base_donnees = new Database();
$base_D = $base_donnees->recupConnexion();
$user = new User($base_D);

$error = '';

if ($_POST) {
    $email = $_POST['email'] ?? '';
    $password = $_POST['password'] ?? '';
    
    if ($user->seConnecter($email, $password)) {
        header('Location: index.php');
        exit();
    } else {
        $error = 'Email ou mot de passe incorrect';
    }
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Connexion — Y.E.F Shop</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
    <link rel="stylesheet" href="style.css">
</head>
<body class="form-page">
    <div class="form-container">
        <div class="form-header">
            <a href="index.php" class="site-logo">
                <i class="bi bi-bag-heart-fill"></i> Y.E.F Shop
            </a>
            <h2>Connexion</h2>
            <p>Connectez-vous à votre compte</p>
        </div>

        <?php if ($error): ?>
            <div class="alert-error">
                <i class="bi bi-exclamation-triangle"></i>
                <?php echo htmlspecialchars($error); ?>
            </div>
        <?php endif; ?>

        <form method="post" class="form-boite">
            <div class="form-group">
                <label>Email</label>
                <input type="email" name="email" placeholder="votre@email.com" required 
                       value="<?php echo htmlspecialchars($_POST['email'] ?? ''); ?>">
            </div>
            
            <div class="form-group">
                <label>Mot de passe</label>
                <input type="password" name="password" placeholder="••••••••" required>
            </div>
            
            <button type="submit" class="btn-submit">
                <i class="bi bi-box-arrow-in-right"></i> Se connecter
            </button>
        </form>

        <div class="form-footer">
            <p>Pas encore de compte ? <a href="signup.php">S'inscrire</a></p>
            <a href="index.php" class="back-link">
                <i class="bi bi-arrow-left"></i> Retour à l'accueil
            </a>
        </div>
    </div>
</body>
</html>
