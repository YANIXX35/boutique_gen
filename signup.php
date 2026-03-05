<?php
require_once 'config.php';

class User {
    private $db;
    
    public function __construct() {
        $this->db = Database::getInstance()->getConnection();
    }
    
    public function findByEmail($email) {
        $stmt = $this->db->prepare("SELECT id FROM users WHERE email = ?");
        $stmt->execute([$email]);
        return $stmt->fetch();
    }
    
    public function create($username, $email, $password, $admin = 0) {
        $hashed_password = password_hash($password, PASSWORD_DEFAULT);
        $stmt = $this->db->prepare("INSERT INTO users (username, email, password, admin, created_at) VALUES (?, ?, ?, ?, NOW())");
        return $stmt->execute([$username, $email, $hashed_password, $admin]);
    }
}

class ValidationService {
    public static function validateUser($username, $email, $password, $confirmPassword) {
        $errors = [];
        
        if (empty($username) || empty($email) || empty($password)) {
            $errors[] = "Tous les champs sont obligatoires";
        }
        
        if ($password !== $confirmPassword) {
            $errors[] = "Les mots de passe ne correspondent pas";
        }
        
        if (strlen($password) < 8) {
            $errors[] = "Le mot de passe doit contenir au moins 8 caractères";
        }
        
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $errors[] = "L'email n'est pas valide";
        }
        
        return $errors;
    }
}

$errors = [];
$success_message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = $_POST['username'] ?? '';
    $email = $_POST['email'] ?? '';
    $password = $_POST['password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';
    
    $errors = ValidationService::validateUser($username, $email, $password, $confirm_password);
    
    if (empty($errors)) {
        $userModel = new User();
        if ($userModel->findByEmail($email)) {
            $errors[] = "Cet email est déjà utilisé";
        }
    }
    
    if (empty($errors)) {
        $userModel = new User();
        if ($userModel->create($username, $email, $password)) {
            $success_message = "Compte créé avec succès ! Vous pouvez maintenant vous connecter.";
        } else {
            $errors[] = "Erreur lors de la création du compte";
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
    <link href="https://fonts.googleapis.com/css2?family=Nunito+Sans:wght@400;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="css/bootstrap.min.css">
</head>
<body class="bg-light">

<div class="d-flex align-items-center justify-content-center min-vh-100 py-5">
    <div class="bg-white border p-5" style="width: 100%; max-width: 520px;">
        <h2 class="fw-bold mb-4">Créer un compte</h2>
        
        <?php if ($success_message): ?>
            <div class="alert alert-success">
                <?= $success_message ?>
                <div class="mt-3">
                    <a href="signin.php" class="btn btn-primary">Se connecter</a>
                </div>
            </div>
        <?php endif; ?>
        
        <?php if (!empty($errors)): ?>
            <div class="alert alert-danger">
                <?php foreach ($errors as $error): ?>
                    <div><?= htmlspecialchars($error) ?></div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>

        <?php if (!$success_message): ?>
            <form method="POST">
                <div class="mb-3">
                    <label class="form-label fw-bold small">Nom d'utilisateur</label>
                    <input type="text" class="form-control rounded-0" name="username" placeholder="Votre nom" required>
                </div>

                <div class="mb-3">
                    <label class="form-label fw-bold small">Email</label>
                    <input type="email" class="form-control rounded-0" name="email" placeholder="Votre email" required>
                </div>

                <div class="mb-3">
                    <label class="form-label fw-bold small">Mot de passe</label>
                    <input type="password" class="form-control rounded-0" name="password" placeholder="Votre mot de passe" required>
                </div>

                <div class="mb-4">
                    <label class="form-label fw-bold small">Confirmer le mot de passe</label>
                    <input type="password" class="form-control rounded-0" name="confirm_password" placeholder="Confirmez votre mot de passe" required>
                </div>

                <button type="submit" class="btn w-100 rounded-0 fw-bold text-white py-3 small"
                        style="background-color: #1a1a1a;">
                    Créer un compte →
                </button>
            </form>

            <hr class="my-4">
            <p class="text-center text-muted small mb-0">
                Vous avez déjà un compte ?
                <a href="signin.php" class="text-dark fw-bold">Se connecter</a>
            </p>
        <?php endif; ?>
    </div>
</div>

</body>
</html>