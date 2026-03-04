<?php
// Inclure la configuration de la base de données
require_once 'config.php';


$errors = [];
$success_message = '';

// Si le formulaire est soumis
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = $_POST['email'] ?? '';
    $password = $_POST['password'] ?? '';
    
    // Validation
    if (empty($email) || empty($password)) {
        $errors[] = "L'email et le mot de passe sont obligatoires";
    }
    
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = "L'email n'est pas valide";
    }
    
    if (empty($errors)) {
        try {
            $stmt = $pdo->prepare("SELECT id, username, email, password FROM users WHERE email = ?");
            $stmt->execute([$email]);
            $user = $stmt->fetch();
            
            if ($user && password_verify($password, $user['password'])) {
                // Mot de passe correct - démarrer la session
                session_start();
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['username'] = $user['username'];
                $_SESSION['user_email'] = $user['email'];
                
                $is_admin = false;
                try {
                    $admin_stmt = $pdo->prepare("SELECT admin FROM users WHERE id = ?");
                    $admin_stmt->execute([$user['id']]);
                    $admin_user = $admin_stmt->fetch();
                    if ($admin_user && $admin_user['admin'] == 1) {
                        $is_admin = true;
                    }
                } catch (PDOException $e) {
                    // En cas d'erreur, continuer comme utilisateur normal
                }
                
                // Rediriger vers le dashboard admin si admin, sinon vers l'accueil
                if ($is_admin) {
                    header('Location: admin/dashboard.php');
                } else {
                    header('Location: index.php');
                }
                exit();
            } else {
                $errors[] = "Email ou mot de passe incorrect";
            }
        } catch (PDOException $e) {
            $errors[] = "Erreur lors de la connexion : " . $e->getMessage();
        }
    }
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Male Fashion | Sign In</title>
<link href="https://fonts.googleapis.com/css2?family=Nunito+Sans:wght@300;400;600;700;800;900&display=swap" rel="stylesheet">
<link href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.2/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">

<!-- Forme -->
<div class="d-flex align-items-center justify-content-center min-vh-100 py-5">
    <div class="bg-white border p-5" style="width: 100%; max-width: 520px;">
        <h2 class="fw-bold mb-4">Se connecter</h2>
        
        <?php if (!empty($errors)): ?>
            <div class="alert alert-danger" role="alert">
                <?php foreach ($errors as $error): ?>
                    <div><?php echo htmlspecialchars($error); ?></div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>

        <form method="POST">
            <!-- Email -->
            <div class="mb-3">
                <label for="email" class="form-label fw-bold text-uppercase small">Adresse Email</label>
                <input type="email" class="form-control rounded-0" id="email" name="email" placeholder="Votre Adresse mail" required>
            </div>

            <!-- Mot de passe -->
            <div class="mb-4">
                <label for="password" class="form-label fw-bold text-uppercase small">Mot de passe</label>
                <input type="password" class="form-control rounded-0" id="password" name="password" placeholder="Votre mot de passe" required>
                <div class="text-end mt-1">
                    <a href="forgot-password.php" class="text-muted small text-decoration-none">Mot de passe oublié ?</a>
                </div>
            </div>

            <!-- Bouton -->
            <button type="submit" class="btn w-100 rounded-0 fw-bold text-uppercase text-white py-3 small"
                style="background-color: #1a1a1a; letter-spacing: 1.5px;">
                Se connecter &rarr;
            </button>

        </form>

        <hr class="my-4">
        <p class="text-center text-muted small mb-0">
            Vous n'avez pas de compte ?
            <a href="signup.php" class="text-dark fw-bold text-decoration-none">Créer un compte</a>
        </p>
    </div>
</div>

</body>
</html>