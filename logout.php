<?php
require_once 'config.php';

/**
 * Classe AuthService - Gestion de l'authentification en POO
 */
class AuthService {
    public static function logout() {
        session_start();
        
        // Détruire toutes les variables de session
        $_SESSION = array();

        // Détruire le cookie de session
        if (ini_get("session.use_cookies")) {
            $params = session_get_cookie_params();
            setcookie(session_name(), '', time() - 42000,
                $params["path"], $params["domain"],
                $params["secure"], $params["httponly"]
            );
        }

        // Détruire la session
        session_destroy();
        
        return true;
    }
}

// Utiliser la méthode logout de la classe AuthService
AuthService::logout();

// Rediriger vers la page de connexion
header("Location: signin.php");
exit();
?>
