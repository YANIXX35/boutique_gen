<?php
require_once 'config.php';

class User {
    private $db;
    
    public function __construct() {
        $this->db = Database::getInstance()->getConnection();
    }
    
    public function authenticate($email, $password) {
        $stmt = $this->db->prepare("SELECT id, username, email, password, admin FROM users WHERE email = ?");
        $stmt->execute([$email]);
        $user = $stmt->fetch();
        
        if ($user && password_verify($password, $user['password'])) {
            unset($user['password']);
            return $user;
        }
        return false;
    }
}

class AuthService {
    private $userModel;
    
    public function __construct() {
        $this->userModel = new User();
    }
    
    public function login($email, $password) {
        $user = $this->userModel->authenticate($email, $password);
        if ($user) {
            session_start();
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['username'] = $user['username'];
            $_SESSION['user_email'] = $user['email'];
            $_SESSION['is_admin'] = $user['admin'] == 1;
            
            header('Location: admin/dashboard.php');
            exit();
        }
        return false;
    }
    
    public function validateEmail($email) {
        return filter_var($email, FILTER_VALIDATE_EMAIL) !== false;
    }
}

$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = $_POST['email'] ?? '';
    $password = $_POST['password'] ?? '';
    
    $authService = new AuthService();
    
    if (empty($email) || empty($password)) {
        $errors[] = "Champs obligatoires";
    }
    
    if (!$authService->validateEmail($email)) {
        $errors[] = "Email invalide";
    }
    
    if (empty($errors)) {
        $authService->login($email, $password);
        $errors[] = "Email ou mot de passe incorrect";
    }
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Male Fashion | Sign In</title>
    <link href="https://fonts.googleapis.com/css2?family=Nunito+Sans:wght@400;600;700&display=swap" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.2/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">

<div class="d-flex align-items-center justify-content-center min-vh-100 py-5">
    <div class="bg-white border p-5" style="width: 100%; max-width: 520px;">
        <h2 class="fw-bold mb-4">Se connecter</h2>
        
        <?php if (!empty($errors)): ?>
            <div class="alert alert-danger">
                <?php foreach ($errors as $error): ?>
                    <div><?= htmlspecialchars($error) ?></div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>

        <form method="POST">
            <div class="mb-3">
                <label class="form-label fw-bold small">Email</label>
                <input type="email" class="form-control rounded-0" name="email" placeholder="Votre email" required>
            </div>

            <div class="mb-4">
                <label class="form-label fw-bold small">Mot de passe</label>
                <input type="password" class="form-control rounded-0" name="password" placeholder="Votre mot de passe" required>
                <div class="text-end mt-1">
                    <a href="forgot-password.php" class="text-muted small">Mot de passe oublié ?</a>
                </div>
            </div>

            <button type="submit" class="btn w-100 rounded-0 fw-bold text-white py-3 small"
                    style="background-color: #1a1a1a;">
                Se connecter →
            </button>
        </form>

        <hr class="my-4">
        <p class="text-center text-muted small mb-0">
            Pas de compte ? 
            <a href="signup.php" class="text-dark fw-bold">Créer un compte</a>
        </p>
    </div>
</div>

</body>
</html>