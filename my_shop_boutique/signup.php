<?php
session_start();
require_once 'classes/Database.php';
require_once 'classes/User.php';

$base_donnees = new Database();
$base_D = $base_donnees->recupConnexion();
$user = new User($base_D);

$error = '';
$success = '';

if ($_POST) {
    $username = $_POST['username'] ?? '';
    $email = $_POST['email'] ?? '';
    $password = $_POST['password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';
    
    if ($password !== $confirm_password) {
        $error = 'Les mots de passe ne correspondent pas';
    } elseif (strlen($password) < 6) {
        $error = 'Le mot de passe doit contenir au moins 6 caractères';
    } else {
        $result = $user->creerCompte($username, $email, $password);
        
        if ($result === true) {
            $success = 'Compte créé avec succès ! Vous pouvez maintenant vous connecter.';
        } else {
            $error = $result;
        }
    }
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Inscription — Y.E.F Shop</title>
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
            <h2>Inscription</h2>
            <p>Créez votre compte pour commencer</p>
        </div>

        <?php if ($error): ?>
            <div class="alert-error">
                <i class="bi bi-exclamation-triangle"></i>
                <?php echo htmlspecialchars($error); ?>
            </div>
        <?php endif; ?>

        <?php if ($success): ?>
            <div class="alert-success">
                <i class="bi bi-check-circle"></i>
                <?php echo htmlspecialchars($success); ?>
            </div>
        <?php endif; ?>

        <form method="post" class="form-boite">
            <div class="form-group">
                <label>Nom d'utilisateur</label>
                <input type="text" name="username" placeholder="Choisissez un nom" required 
                       value="<?php echo htmlspecialchars($_POST['username'] ?? ''); ?>">
            </div>
            
            <div class="form-group">
                <label>Email</label>
                <input type="email" name="email" placeholder="votre@email.com" required 
                       value="<?php echo htmlspecialchars($_POST['email'] ?? ''); ?>">
            </div>
            
            <div class="form-group">
                <label>Mot de passe</label>
                <input type="password" name="password" placeholder="Min 6 caractères" required>
            </div>
            
            <div class="form-group">
                <label>Confirmer le mot de passe</label>
                <input type="password" name="confirm_password" placeholder="Répétez le mot de passe" required>
            </div>
            
            <button type="submit" class="btn-submit">
                <i class="bi bi-person-plus"></i> Créer mon compte
            </button>
        </form>

        <div class="form-footer">
            <p>Déjà un compte ? <a href="signin.php">Se connecter</a></p>
            <a href="index.php" class="back-link">
                <i class="bi bi-arrow-left"></i> Retour à l'accueil
            </a>
        </div>
    </div>
</body>
</html>
