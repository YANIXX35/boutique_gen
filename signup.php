<?php
// Inclure la configuration de la base de données
require_once 'config.php';

// Si le formulaire est soumis
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Récupérer les données du formulaire
    $username = $_POST['username'] ?? '';
    $email = $_POST['email'] ?? '';
    $password = $_POST['password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';
    
    // Validation
    $errors = [];
    
    if (empty($username) || empty($email) || empty($password)) {
        $errors[] = "Tous les champs sont obligatoires";
    }
    
    if ($password !== $confirm_password) {
        $errors[] = "Les mots de passe ne correspondent pas";
    }
    
    if (strlen($password) < 8) {
        $errors[] = "Le mot de passe doit contenir au moins 8 caractères";
    }
    
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = "L'email n'est pas valide";
    }
    
    // Vérifier si l'email existe déjà
    if (empty($errors)) {
        try {
            $stmt = $pdo->prepare("SELECT id FROM users WHERE email = ?");
            $stmt->execute([$email]);
            if ($stmt->fetch()) {
                $errors[] = "Cet email est déjà utilisé";
            }
        } catch (PDOException $e) {
            $errors[] = "Erreur lors de la vérification de l'email";
        }
    }
    
    // Si pas d'erreurs, insérer l'utilisateur dans la base de données
    if (empty($errors)) {
        try {
            // Hasher le mot de passe
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);
            
            // Insérer l'utilisateur dans la base de données
            $stmt = $pdo->prepare("INSERT INTO users (username, email, password, admin, created_at) VALUES (?, ?, ?, 0, NOW())");
            $stmt->execute([$username, $email, $hashed_password]);
            
            $success_message = "Compte créé avec succès ! Vous pouvez maintenant vous connecter.";
            
        } catch (PDOException $e) {
            $errors[] = "Erreur lors de l'inscription : " . $e->getMessage();
        }
    }
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Male Fashion | Sign Up</title>
<link href="https://fonts.googleapis.com/css2?family=Nunito+Sans:wght@300;400;600;700;800;900&display=swap" rel="stylesheet">
<link href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.2/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">

<!-- Forme -->
<div class="d-flex align-items-center justify-content-center min-vh-100 py-5">
    <div class="bg-white border p-5" style="width: 100%; max-width: 520px;">
        <h2 class="fw-bold mb-4">Créer un compte</h2>
        
        <?php if (!empty($errors)): ?>
            <div class="alert alert-danger" role="alert">
                <?php foreach ($errors as $error): ?>
                    <div><?php echo htmlspecialchars($error); ?></div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
        
        <?php if (!empty($success_message)): ?>
            <div class="alert alert-success" role="alert">
                <?php echo $success_message; ?>
            </div>
        <?php endif; ?>

        <form method="POST">
            <!-- Nom d'utilisateur -->
            <div class="mb-3">
                <label for="username" class="form-label fw-bold text-uppercase small">Nom d'utilisateur</label>
                <input type="text" class="form-control rounded-0" id="username" name="username" placeholder="Entrez votre nom d'utilisateur" required>
            </div>
            <!-- Email -->
            <div class="mb-3">
                <label for="email" class="form-label fw-bold text-uppercase small">Adresse Email</label>
                <input type="email" class="form-control rounded-0" id="email" name="email" placeholder="Votre Adresse mail" required>
            </div>
            <!-- Mot de passe -->
            <div class="mb-3">
                <label for="password" class="form-label fw-bold text-uppercase small">Mot de passe</label>
                <input type="password" class="form-control rounded-0" id="password" name="password" placeholder="Votre mot de passe" required>
                <div class="form-text text-muted">Minimum 8 caractères.</div>
            </div>
            <!-- Confirmer mot de passe -->
            <div class="mb-4">
                <label for="confirm_password" class="form-label fw-bold text-uppercase small">Confirmer le mot de passe</label>
                <input type="password" class="form-control rounded-0" id="confirm_password" name="confirm_password" placeholder="Confirmez votre mot de passe" required>
            </div>
            <!-- Bouton -->
            <button type="submit" class="btn w-100 rounded-0 fw-bold text-uppercase text-white py-3 small"
                style="background-color: #1a1a1a; letter-spacing: 1.5px;">
                Créer mon compte &rarr;
            </button>
        </form>
        
        <hr class="my-4">
        <p class="text-center text-muted small mb-0">
            Avez vous déjà un compte ?
            <a href="signin.php" class="text-dark fw-bold text-decoration-none">Se connecter</a>
        </p>
    </div>
</div>

</body>
</html>