-- Script pour définir un utilisateur comme administrateur
-- Remplacez 'votre_email@example.com' par l'email de l'utilisateur que vous voulez rendre admin

UPDATE users SET admin = 1 WHERE email = 'votre_email@example.com';

-- Pour vérifier que l'utilisateur est bien admin :
SELECT id, username, email, admin FROM users WHERE email = 'votre_email@example.com';

-- Pour voir tous les utilisateurs et leur statut admin :
SELECT id, username, email, admin FROM users ORDER BY admin DESC, username;

-- Si vous voulez créer un utilisateur admin directement :
-- INSERT INTO users (username, email, password, admin) 
-- VALUES ('admin', 'admin@example.com', '$2y$10$votre_hash_de_mot_de_passe', 1);

-- Pour générer un hash de mot de passe sécurisé, utilisez PHP :
-- <?php echo password_hash('votre_mot_de_passe', PASSWORD_DEFAULT); ?>
