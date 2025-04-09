<?php
$searchResults = [];
$searchPerformed = false;

if ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['search'])) {
    $searchPerformed = true;
    $searchTerm = '%' . $_GET['search'] . '%';
    $searchType = $_GET['search_type'] ?? 'all';

    // SQL-Abfrage basierend auf Suchtyp erstellen
    switch ($searchType) {
        case 'scientific':
            $sql = "SELECT p.*,
                   (SELECT file_path FROM images WHERE plant_id = p.id LIMIT 1) AS thumbnail
                   FROM plants p
                   WHERE p.scientific_name LIKE ?
                   ORDER BY p.scientific_name";
            $params = [$searchTerm];
            break;

        case 'common':
            $sql = "SELECT p.*,
                   (SELECT file_path FROM images WHERE plant_id = p.id LIMIT 1) AS thumbnail
                   FROM plants p
                   WHERE p.common_name LIKE ?
                   ORDER BY p.scientific_name";
            $params = [$searchTerm];
            break;

        case 'family':
            $sql = "SELECT p.*,
                   (SELECT file_path FROM images WHERE plant_id = p.id LIMIT 1) AS thumbnail
                   FROM plants p
                   WHERE p.family LIKE ?
                   ORDER BY p.scientific_name";
            $params = [$searchTerm];
            break;

        case 'location':
            $sql = "SELECT p.*,
                   (SELECT file_path FROM images WHERE plant_id = p.id LIMIT 1) AS thumbnail
                   FROM plants p
                   WHERE p.discovery_location LIKE ?
                   ORDER BY p.scientific_name";
            $params = [$searchTerm];
            break;

        default: // all
            $sql = "SELECT p.*,
                   (SELECT file_path FROM images WHERE plant_id = p.id LIMIT 1) AS thumbnail
                   FROM plants p
                   WHERE p.scientific_name LIKE ?
                   OR p.common_name LIKE ?
                   OR p.family LIKE ?
                   OR p.description LIKE ?
                   OR p.habitat LIKE ?
                   OR p.discovery_location LIKE ?
                   ORDER BY p.scientific_name";
            $params = array_fill(0, 6, $searchTerm);
    }

    // Suchabfrage ausführen
    try {
        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
        $searchResults = $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        echo '<div class="error-message">Suchfehler: ' . $e->getMessage() . '</div>';
    }
}
?>

<h2>Pflanzen suchen</h2>

<form action="index.php" method="get" class="search-form">
    <input type="hidden" name="page" value="search">

    <div class="form-group">
        <label for="search">Suchbegriff</label>
        <input type="text" id="search" name="search" value="<?= escape($_GET['search'] ?? '') ?>" required>
    </div>

    <div class="form-group">
        <label for="search_type">Suchen in</label>
        <select id="search_type" name="search_type">
            <option value="all" <?= isset($_GET['search_type']) && $_GET['search_type'] === 'all' ? 'selected' : '' ?>>Allen Feldern</option>
            <option value="scientific" <?= isset($_GET['search_type']) && $_GET['search_type'] === 'scientific' ? 'selected' : '' ?>>Wissenschaftlicher Name</option>
            <option value="common" <?= isset($_GET['search_type']) && $_GET['search_type'] === 'common' ? 'selected' : '' ?>>Gebräuchlicher Name</option>
            <option value="family" <?= isset($_GET['search_type']) && $_GET['search_type'] === 'family' ? 'selected' : '' ?>>Familie</option>
            <option value="location" <?= isset($_GET['search_type']) && $_GET['search_type'] === 'location' ? 'selected' : '' ?>>Fundort</option>
        </select>
    </div>

    <button type="submit" class="button primary">Suchen</button>
</form>

<?php if ($searchPerformed): ?>
    <h3>Suchergebnisse</h3>

    <?php if (count($searchResults) > 0): ?>
        <div class="plant-grid">
            <?php foreach ($searchResults as $plant): ?>
                <div class="plant-card">
                    <div class="plant-image">
                        <?php if ($plant['thumbnail']): ?>
                            <img src="images/<?= escape($plant['thumbnail']) ?>" alt="<?= escape($plant['common_name']) ?>">
                        <?php else: ?>
                            <div class="no-image">Kein Bild</div>
                        <?php endif; ?>
                    </div>
                    <div class="plant-info">
                        <h3><?= escape($plant['scientific_name']) ?></h3>
                        <p><?= escape($plant['common_name']) ?></p>
                        <p>Familie: <?= escape($plant['family']) ?></p>
                    </div>
                    <div class="plant-actions">
                        <a href="index.php?page=view&id=<?= $plant['id'] ?>" class="button">Ansehen</a>
                        <a href="index.php?page=edit&id=<?= $plant['id'] ?>" class="button">Bearbeiten</a>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php else: ?>
        <p>Keine Pflanzen gefunden, die Ihren Suchkriterien entsprechen.</p>
    <?php endif; ?>
<?php endif; ?>