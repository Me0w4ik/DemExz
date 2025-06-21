<?php
$db = [
    'host' => 'localhost',
    'user' => 'root',
    'pass' => '',
    'name' => 'test_db'
];

// Создание БД и таблицы
try {
    $tempPdo = new PDO("mysql:host={$db['host']}", $db['user'], $db['pass']);
    $tempPdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    $tempPdo->exec("CREATE DATABASE IF NOT EXISTS `{$db['name']}`");
    $tempPdo->exec("USE `{$db['name']}`");
    $tempPdo->exec("CREATE TABLE IF NOT EXISTS `items` (
        `id` INT AUTO_INCREMENT PRIMARY KEY,
        `name` VARCHAR(255) NOT NULL,
        `description` TEXT,
        `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    )");
} catch (PDOException $e) {
    die("Database error: " . $e->getMessage());
}

// Основное подключение
try {
    $pdo = new PDO("mysql:host={$db['host']};dbname={$db['name']}", $db['user'], $db['pass']);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    die("Connection failed: " . $e->getMessage());
}

function html($data) {
    return htmlspecialchars($data, ENT_QUOTES, 'UTF-8');
}
?>








<?php
require 'config.php';

// CRUD операции
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        if (isset($_POST['create'])) {
            $pdo->prepare("INSERT INTO items (name, description) VALUES (?, ?)")
               ->execute([$_POST['name'], $_POST['description']]);
        } elseif (isset($_POST['update'])) {
            $pdo->prepare("UPDATE items SET name = ?, description = ? WHERE id = ?")
               ->execute([$_POST['name'], $_POST['description'], $_POST['id']]);
        } elseif (isset($_POST['delete'])) {
            $pdo->prepare("DELETE FROM items WHERE id = ?")->execute([$_POST['id']]);
        }
        header("Location: ".$_SERVER['PHP_SELF']);
        exit();
    } catch (PDOException $e) {
        die("Ошибка операции: " . $e->getMessage());
    }
}

// Получение данных
$items = $pdo->query("SELECT * FROM items ORDER BY id DESC")->fetchAll();
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Управление записями</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <div class="container">
        <header><h1>Управление записями</h1></header>

        <div class="card">
            <h2 class="form-title">Добавить запись</h2>
            <form method="POST">
                <div class="form-grid">
                    <div class="form-group">
                        <label for="name">Название</label>
                        <input type="text" id="name" name="name" required>
                    </div>
                    <div class="form-group">
                        <label for="description">Описание</label>
                        <textarea id="description" name="description"></textarea>
                    </div>
                </div>
                <button type="submit" name="create" class="btn-primary">Сохранить</button>
            </form>
        </div>

        <div class="card">
            <div class="table-container">
                <table>
                    <thead>
                        <tr>
                            <th class="col-id">ID</th>
                            <th class="col-name">Название</th>
                            <th class="col-desc">Описание</th>
                            <th class="col-date">Дата</th>
                            <th class="col-actions">Действия</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($items)): ?>
                            <tr><td colspan="5" class="empty-state">Нет записей</td></tr>
                        <?php else: ?>
                            <?php foreach ($items as $item): ?>
                            <tr>
                                <td class="col-id"><?= html($item['id']) ?></td>
                                <td class="col-name">
                                    <input type="text" class="table-input" name="name" 
                                           value="<?= html($item['name']) ?>" form="form-<?= html($item['id']) ?>" required>
                                </td>
                                <td class="col-desc">
                                    <textarea class="table-textarea" name="description" 
                                              form="form-<?= html($item['id']) ?>"><?= html($item['description']) ?></textarea>
                                </td>
                                <td class="col-date"><?= html($item['created_at']) ?></td>
                                <td class="col-actions">
                                    <form method="POST" id="form-<?= html($item['id']) ?>">
                                        <input type="hidden" name="id" value="<?= html($item['id']) ?>">
                                        <button type="submit" name="update" class="action-btn btn-outline">Изменить</button>
                                        <button type="submit" name="delete" class="action-btn btn-danger">Удалить</button>
                                    </form>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</body>
</html>










:root {
    --primary: #4895ef;
    --danger: #f72585;
    --text: #f8f9fa;
    --bg: #212529;
    --card-bg: #343a40;
    --border: #495057;
}

* {
    margin: 0;
    padding: 0;
    box-sizing: border-box;
}

body {
    font-family: 'Inter', sans-serif;
    background-color: var(--bg);
    color: var(--text);
    padding: 20px;
}

.container {
    max-width: 100%;
    margin: 0 auto;
    padding: 0 20px;
}

header {
    margin-bottom: 30px;
    padding: 20px 0;
    border-bottom: 1px solid var(--border);
}

.card {
    background-color: var(--card-bg);
    border-radius: 10px;
    padding: 25px;
    margin-bottom: 30px;
}

.form-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
    gap: 20px;
    margin-bottom: 20px;
}

input, textarea, button {
    width: 100%;
    padding: 12px 15px;
    border: 1px solid var(--border);
    border-radius: 6px;
    background-color: var(--card-bg);
    color: var(--text);
    margin-bottom: 15px;
}

button {
    background-color: var(--primary);
    color: white;
    cursor: pointer;
}

.btn-danger {
    background-color: var(--danger);
}

table {
    width: 100%;
    border-collapse: collapse;
}

th, td {
    padding: 12px 15px;
    border-bottom: 1px solid var(--border);
}

.empty-state {
    text-align: center;
    padding: 40px 0;
}

@media (max-width: 768px) {
    .form-grid {
        grid-template-columns: 1fr;
    }
}
