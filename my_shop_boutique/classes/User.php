<?php
    class User {

        private $connexion;
        private $nom_Table = "users";

        public function __construct($base_D) {
            $this->connexion = $base_D;
        }

        // 1. CRÉER UN COMPTE
        public function creerCompte($username, $email, $password) {
            $verif = $this->connexion->prepare("SELECT id FROM " . $this->nom_Table . " WHERE email = ?");
            $verif->execute([$email]);

            if ($verif->rowCount() > 0) {
                return "Cet email est déjà utilisé.";
            }

            $motDePasse_Hache = password_hash($password, PASSWORD_BCRYPT);
            $requete = "INSERT INTO " . $this->nom_Table . " (username, email, password, admin) VALUES (?, ?, ?, 0)";
            $stmt = $this->connexion->prepare($requete);

            if ($stmt->execute([$username, $email, $motDePasse_Hache])) {
                return true;
            }

            return "Une erreur est survenue lors de l'inscription.";
        }

        // 2. SE CONNECTER
        public function seConnecter($email, $password) {
            $requete = "SELECT * FROM " . $this->nom_Table . " WHERE email = ?";
            $stmt    = $this->connexion->prepare($requete);
            $stmt->execute([$email]);
            $utilisateur = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($utilisateur && password_verify($password, $utilisateur['password'])) {
                if (session_status() === PHP_SESSION_NONE) {
                    session_start();
                }
                $_SESSION['user_id']  = $utilisateur['id'];
                $_SESSION['username'] = $utilisateur['username'];
                $_SESSION['is_admin'] = $utilisateur['admin'];
                return true;
            }

            return false;
        }

        // 3. LIRE TOUS LES UTILISATEURS
        public function lire_Tout() {
            $requete = "SELECT id, username, email, admin FROM " . $this->nom_Table;
            $stmt    = $this->connexion->prepare($requete);
            $stmt->execute();
            return $stmt;
        }

        // 4. TROUVER UN UTILISATEUR PAR SON ID
        public function trouver($id) {
            $requete = "SELECT id, username, email, admin FROM " . $this->nom_Table . " WHERE id = ?";
            $stmt    = $this->connexion->prepare($requete);
            $stmt->execute([$id]);
            return $stmt->fetch(PDO::FETCH_ASSOC);
        }

        // 5. MODIFIER UN UTILISATEUR
        public function modifier($id, $username, $email, $admin) {
            $requete = "UPDATE " . $this->nom_Table . " SET username = ?, email = ?, admin = ? WHERE id = ?";
            $stmt    = $this->connexion->prepare($requete);
            return $stmt->execute([$username, $email, $admin, $id]);
        }

        // 6. SUPPRIMER UN COMPTE
        public function supprimer_Compte($id) {
            $requete = "DELETE FROM " . $this->nom_Table . " WHERE id = ?";
            $stmt    = $this->connexion->prepare($requete);
            return $stmt->execute([$id]);
        }

        // 7. CHANGER LE RÔLE (membre ↔ admin)
        public function miseAjourStatus($id, $adminStatut) {
            $requete = "UPDATE " . $this->nom_Table . " SET admin = ? WHERE id = ?";
            $stmt    = $this->connexion->prepare($requete);
            return $stmt->execute([$adminStatut, $id]);
        }
    }