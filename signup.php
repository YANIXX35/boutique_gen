<?php
// Importer PHPMailer
require 'PHPMailer/src/Exception.php';
require 'PHPMailer/src/PHPMailer.php';
require 'PHPMailer/src/SMTP.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;

// Si le formulaire est soumis
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Récupérer les données du formulaire
    $nom = $_POST['nom'] ?? '';
    $prenom = $_POST['prenom'] ?? '';
    $email = $_POST['email'] ?? '';
    $password = $_POST['password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';
    
    // Validation
    $errors = [];
    
    if (empty($nom) || empty($prenom) || empty($email) || empty($password)) {
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
    
    // Si pas d'erreurs, envoyer l'email
    if (empty($errors)) {
        // Créer une instance de PHPMailer
        $mail = new PHPMailer(true);
        
        try {
            // Configuration du serveur SMTP
            $mail->isSMTP();
            $mail->Host       = 'smtp.gmail.com';
            $mail->SMTPAuth   = true;
            $mail->Username   = 'kyliyanisse@gmail.com';
            $mail->Password   = 'qvqz oklq lbfl ouim';
            $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
            $mail->Port       = 587;
            
            // Destinataire et expéditeur
            $mail->setFrom('kyliyanisse@gmail.com', 'Male Fashion');
            $mail->addAddress($email, $prenom . ' ' . $nom);
            
            // Contenu de l'email
            $mail->isHTML(true);
            $mail->Subject = 'Confirmation de création de compte - Male Fashion';
            $mail->Body    = '
                <div style="font-family: Arial, sans-serif; max-width: 600px; margin: 0 auto; padding: 20px;">
                    <div style="background-color: #1a1a1a; color: white; padding: 20px; text-align: center;">
                        <h2 style="margin: 0;">Male Fashion</h2>
                    </div>
                    <div style="padding: 30px; background-color: #f9f9f9;">
                        <h3 style="color: #1a1a1a;">Bienvenue ' . htmlspecialchars($prenom) . ' !</h3>
                        <p style="color: #333; line-height: 1.6;">
                            Nous vous confirmons que votre compte a été créé avec succès sur notre boutique Male Fashion.
                        </p>
                        <div style="background-color: white; padding: 20px; border-radius: 5px; margin: 20px 0;">
                            <h4 style="color: #1a1a1a; margin-top: 0;">Informations du compte :</h4>
                            <p style="margin: 5px 0;"><strong>Nom :</strong> ' . htmlspecialchars($nom) . '</p>
                            <p style="margin: 5px 0;"><strong>Prénom :</strong> ' . htmlspecialchars($prenom) . '</p>
                            <p style="margin: 5px 0;"><strong>Email :</strong> ' . htmlspecialchars($email) . '</p>
                        </div>
                        <p style="color: #333; line-height: 1.6;">
                            Vous pouvez maintenant vous connecter et profiter de nos collections exclusives.
                        </p>
                        <div style="text-align: center; margin: 30px 0;">
                            <a href="http://localhost/boutique_gen/signin.php" 
                               style="background-color: #1a1a1a; color: white; padding: 12px 30px; 
                                      text-decoration: none; border-radius: 3px; display: inline-block;">
                                Me connecter
                            </a>
                        </div>
                    </div>
                    <div style="background-color: #1a1a1a; color: white; padding: 20px; text-align: center; font-size: 12px;">
                        <p style="margin: 0;"> 2024 Male Fashion. Tous droits réservés.</p>
                    </div>
                </div>
            ';
            $mail->AltBody = '
                Bienvenue ' . $prenom . ' !\n\n
                Nous vous confirmons que votre compte a été créé avec succès sur notre boutique Male Fashion.\n\n
                Informations du compte :\n
                Nom : ' . $nom . '\n
                Prénom : ' . $prenom . '\n
                Email : ' . $email . '\n\n
                Vous pouvez maintenant vous connecter : http://localhost/boutique_gen/signin.php\n\n
                 2024 Male Fashion. Tous droits réservés.
            ';
            
            $mail->send();
            $success_message = "Compte créé avec succès ! Un email de confirmation a été envoyé à " . htmlspecialchars($email);
            
        } catch (Exception $e) {
            $errors[] = "Erreur lors de l'envoi de l'email: " . $mail->ErrorInfo;
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
            <!-- Nom & Prénom -->
            <div class="row mb-3">
                <div class="col">
                    <label for="Nom" class="form-label fw-bold text-uppercase small">Nom</label>
                    <input type="text" class="form-control rounded-0" id="" name="nom" placeholder="Entrez votre Nom" required>
                </div>
                <div class="col">
                    <label for="Prenom" class="form-label fw-bold text-uppercase small">Prénom</label>
                    <input type="text" class="form-control rounded-0" id="lastname" name="prenom" placeholder="Entrez votre prénom" required>
                </div>
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