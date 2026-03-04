<?php
// Démarrer la session et vérifier si l'utilisateur est admin
session_start();
require_once '../config.php';

// Vérifier si l'utilisateur est connecté et est admin
if (!isset($_SESSION['user_id']) || !isset($_SESSION['username'])) {
    header('Location: ../signin.php');
    exit();
}

// Vérifier si l'utilisateur est admin
$is_admin = false;
try {
    $stmt = $pdo->prepare("SELECT admin FROM users WHERE id = ?");
    $stmt->execute([$_SESSION['user_id']]);
    $user = $stmt->fetch();
    if ($user && $user['admin'] == 1) {
        $is_admin = true;
    }
} catch (PDOException $e) {
    $is_admin = false;
}

if (!$is_admin) {
    header('Location: ../index.php');
    exit();
}

// Traitement du formulaire d'ajout
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = $_POST['name'] ?? '';
    $parent_id = $_POST['parent_id'] ?? null;
    $errors = [];
    
    // Validation
    if (empty($name)) $errors[] = "Le nom de la catégorie est obligatoire";
    
    if (empty($errors)) {
        try {
            // Insérer la catégorie
            $stmt = $pdo->prepare("
                INSERT INTO categories (name, parent_id) 
                VALUES (?, ?)
            ");
            $stmt->execute([$name, $parent_id]);
            
            $success_message = "Catégorie ajoutée avec succès !";
            
        } catch (PDOException $e) {
            $errors[] = "Erreur lors de l'ajout de la catégorie : " . $e->getMessage();
        }
    }
}

// Récupérer les catégories
$categories = [];
try {
    $stmt = $pdo->query("SELECT * FROM categories ORDER BY name");
    $categories = $stmt->fetchAll();
} catch (PDOException $e) {
    $categories = [];
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ajouter une Catégorie - Admin</title>
</head>
<body>
    <h2>Ajouter une Nouvelle Catégorie</h2>
    
    <?php if (!empty($errors)): ?>
        <div style="color: red; border: 1px solid red; padding: 10px; margin: 10px 0;">
            <?php foreach ($errors as $error): ?>
                <?php echo htmlspecialchars($error); ?><br>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
    
    <?php if (!empty($success_message)): ?>
        <div style="color: green; border: 1px solid green; padding: 10px; margin: 10px 0;">
            <?php echo htmlspecialchars($success_message); ?>
        </div>
    <?php endif; ?>

    <form method="POST">
        <table border="1" cellpadding="5" cellspacing="0">
            <tr>
                <td><label for="name">Nom de la catégorie:</label></td>
                <td><input type="text" name="name" value="<?php echo htmlspecialchars($_POST['name'] ?? ''); ?>" required></td>
            </tr>
            <tr>
                <td><label for="parent_id">Catégorie parente:</label></td>
                <td>
                    <select name="parent_id">
                        <option value="">Aucune (catégorie principale)</option>
                        <?php foreach ($categories as $category): ?>
                            <option value="<?php echo $category['id']; ?>"
                                    <?php echo (isset($_POST['parent_id']) && $_POST['parent_id'] == $category['id']) ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($category['name']); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </td>
            </tr>
            <tr>
                <td colspan="2" align="center">
                    <input type="submit" value="Ajouter la catégorie">
                    <a href="categories.php" style="margin-left: 20px;">Retour</a>
                </td>
            </tr>
        </table>
    </form>
</body>
</html>
