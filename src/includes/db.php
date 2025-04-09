<?php
/**
 * Datenbankverbindung für die Pflanzendatenbank
 */

// Konfiguration aus der config.php laden
$config = require __DIR__ . '/../../config/config.php';

// Konfiguration für die Datenbankverbindung aus config.php nehmen
$db_host = $config['host'];
$db_name = $config['db'];
$db_user = $config['user'];
$db_pass = $config['pass'];
$db_charset = 'utf8mb4';

$dsn = "mysql:host=$db_host;dbname=$db_name;charset=$db_charset";
$options = [
    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::ATTR_EMULATE_PREPARES => false,
];

try {
    $pdo = new PDO($dsn, $db_user, $db_pass, $options);
} catch (PDOException $e) {
    die('Datenbankverbindungsfehler: ' . $e->getMessage());
}

/**
 * Stellt sicher, dass die benötigten Tabellen existieren
 */
function ensureTablesExist($pdo) {
    // Tabelle für Pflanzen
    $pdo->exec("CREATE TABLE IF NOT EXISTS plants (
        id INT AUTO_INCREMENT PRIMARY KEY,
        scientific_name VARCHAR(255) NOT NULL,
        common_name VARCHAR(255),
        family VARCHAR(255),
        description TEXT,
        habitat TEXT,
        discovery_date DATE,
        discovery_location VARCHAR(255),
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");

    // Tabelle für Bilder
    $pdo->exec("CREATE TABLE IF NOT EXISTS images (
        id INT AUTO_INCREMENT PRIMARY KEY,
        plant_id INT NOT NULL,
        file_path VARCHAR(255) NOT NULL,
        caption VARCHAR(255),
        taken_date DATE,
        uploaded_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (plant_id) REFERENCES plants(id) ON DELETE CASCADE
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");
}

// Stellt sicher, dass die Tabellen existieren
ensureTablesExist($pdo);