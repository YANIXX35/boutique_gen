<?php
session_start();
require_once '../config.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: ../signin.php');
    exit();
}

$stmt = $pdo->prepare("SELECT admin FROM users WHERE id = ?");
$stmt->execute([$_SESSION['user_id']]);
$user = $stmt->fetch();

if (!$user || $user['admin'] != 1) {
    header('Location: ../index.php');
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = $_POST['name'] ?? '';
    $parent_id = $_POST['parent_id'] ?? null;
    $errors = [];
    
    if (empty($name)) $errors[] = "Nom obligatoire";
    
    if (empty($errors)) {
        $stmt = $pdo->prepare("INSERT INTO categories (name, parent_id) VALUES (?, ?)");
        $stmt->execute([$name, $parent_id]);
        
        $_SESSION['success'] = "Catégorie ajoutée";
        header('Location: categories.php');
        exit();
    }
}

$categories = $pdo->query("SELECT * FROM categories ORDER BY name")->fetchAll();
?>

<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Ajouter Catégorie</title>
</head>
<body>
    <h2>Ajouter une Catégorie</h2>
    
    <a href="categories.php">← Retour</a>
    
    <?php if (!empty($errors)): ?>
        <div style="color: red; padding: 10px; margin: 10px 0; border: 1px solid red;">
            <?php foreach ($errors as $error): ?>
                <?= $error ?><br>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>

    <form method="POST">
        <table>
            <tr>
                <td>Nom de la catégorie:</td>
                <td><input type="text" name="name" required></td>
            </tr>
            <tr>
                <td>Catégorie parente:</td>
                <td>
                    <select name="parent_id">
                        <option value="">Aucune (principale)</option>
                        <?php foreach ($categories as $cat): ?>
                            <option value="<?= $cat['id'] ?>"><?= htmlspecialchars($cat['name']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </td>
            </tr>
            <tr>
                <td colspan="2" style="text-align: center;">
                    <input type="submit" value="Ajouter la catégorie">
                </td>
            </tr>
        </table>
    </form>
</body>
</html>
