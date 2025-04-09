<?php

// Session starten
session_start();

// DB und Konfiguration
$config = require __DIR__ . '/../config/config.php';
$pdo = new PDO("mysql:host={$config['host']};dbname={$config['db']};charset=utf8mb4", $config['user'], $config['pass']);
$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

// Funktionen einbinden
require_once __DIR__ . '/includes/functions.php';

// Wichtiger Teil: Wenn es ein POST-Request zum Hinzufügen ist, sofort verarbeiten!
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_plant'])) {
    require './add_plants.php';
    exit;
}

?>

<!DOCTYPE html>
<html lang="de">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Herbarium Datenbank</title>
    <link rel="stylesheet" href="css/style.css">
    <link rel="icon" href="images/icon.png" type="image/png">
</head>
<body>
    <header>
        <h1>Herbarium Datenbank</h1>
        <nav>
            <ul>
                <li><a href="index.php">Startseite</a></li>
                <li><a href="index.php?page=add">Pflanze hinzufügen</a></li>
                <li><a href="index.php?page=search">Suche</a></li>
            </ul>
        </nav>
    </header>

    <main>
        <?php
        $page = isset($_GET['page']) ? $_GET['page'] : 'list';

        switch ($page) {
            case 'list':
                include './list_plants.php';
                break;
            case 'add':
                include './add_plants.php';
                break;
            case 'edit':
                include './edit_plants.php';
                break;
            case 'view':
                include './view_plants.php';
                break;
            case 'search':
                include './search.php';
                break;
            default:
                include './list_plants.php';
        }
        ?>
    </main>

    <footer>
        <p>&copy; <?php echo date('Y'); ?> Herbarium Datenbank by Curd Rohde</p>
    </footer>
</body>
</html>