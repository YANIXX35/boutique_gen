<?php
    session_start();
    require_once 'classes/Database.php';
    require_once 'classes/User.php';

    if (!isset($_SESSION['is_admin']) || $_SESSION['is_admin'] != 1) {
        header("Location: index.php");
        exit();
    }

    if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
        header("Location: admin.php?section=utilisateurs");
        exit();
    }

    $base_donnees = new Database();
    $base_D       = $base_donnees->recupConnexion();
    $utilisateur  = new User($base_D);

    $id = intval($_GET['id']);

    $u = $utilisateur->trouver($id);

    if (!$u) {
        header("Location: admin.php?section=utilisateurs");
        exit();
    }

    $erreurs = [];

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {

        $username = trim($_POST['username'] ?? '');
        $email    = trim($_POST['email']    ?? '');
        $admin    = isset($_POST['admin']) ? 1 : 0;

        if (empty($username)) {
            $erreurs[] = "Le nom d'utilisateur est obligatoire.";
        }

        if (empty($email)) {
            $erreurs[] = "L'email est obligatoire.";
        } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $erreurs[] = "L'email n'est pas valide.";
        }

        if (empty($erreurs)) {
            if ($utilisateur->modifier($id, $username, $email, $admin)) {
                header("Location: admin.php?section=utilisateurs&updated=1");
                exit();
            } else {
                $erreurs[] = "Une erreur est survenue lors de la mise à jour.";
            }
        }

        $u['username'] = $username;
        $u['email']    = $email;
        $u['admin']    = $admin;
    }
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Modifier l'utilisateur — Y.E.F Shop</title>
    <link rel="stylesheet" href="style.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
</head>
<body class="form-page">

    <form method="post" class="form-boite">
        <h2>Modifier l'utilisateur</h2>

        <!-- Affichage des erreurs -->
        <?php if (!empty($erreurs)) { ?>
            <div class="erreurs">
                <?php foreach ($erreurs as $erreur) { ?>
                    <p><i class="bi bi-exclamation-circle"></i> <?php echo htmlspecialchars($erreur); ?></p>
                <?php } ?>
            </div>
        <?php } ?>

        <label for="username">Nom d'utilisateur</label>
        <input
            type="text"
            id="username"
            name="username"
            placeholder="Nom d'utilisateur"
            value="<?php echo htmlspecialchars($u['username']); ?>"
            required>

        <label for="email">Adresse email</label>
        <input
            type="email"
            id="email"
            name="email"
            placeholder="email@exemple.com"
            value="<?php echo htmlspecialchars($u['email']); ?>"
            required>

        <label>Droits administrateur</label>
        <label class="admin-toggle">
            <input
                type="checkbox"
                name="admin"
                <?php echo ($u['admin'] == 1) ? 'checked' : ''; ?>>
            <div class="admin-toggle-label">
                Accès administrateur
                <span>Cocher pour accorder les droits admin à cet utilisateur</span>
            </div>
        </label>

        <button type="submit" class="avec-icone">
            Enregistrer les modifications
        </button>
        <a href="admin.php?section=utilisateurs" class="lien-retour">
            &larr; Retour aux utilisateurs
        </a>
    </form>

</body>
</html>