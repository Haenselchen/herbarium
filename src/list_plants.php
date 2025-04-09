<?php
// Pflanzen aus der Datenbank abrufen
$stmt = $pdo->query('SELECT p.*,
                     (SELECT file_path FROM images WHERE plant_id = p.id LIMIT 1) AS thumbnail
                     FROM plants p ORDER BY scientific_name');
$plants = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Löschlogik
if (isset($_POST['delete']) && isset($_POST['id'])) {
    $id = (int)$_POST['id'];

    try {
        // Bilder löschen
        $imagePath = $pdo->prepare('SELECT file_path FROM images WHERE plant_id = ?');
        $imagePath->execute([$id]);

        while ($path = $imagePath->fetch(PDO::FETCH_ASSOC)) {
            if (file_exists('images/' . $path['file_path'])) {
                unlink('images/' . $path['file_path']);
            }
        }

        // Bilder aus der Datenbank löschen
        $deleteImages = $pdo->prepare('DELETE FROM images WHERE plant_id = ?');
        $deleteImages->execute([$id]);

        // Pflanze löschen
        $deletePlant = $pdo->prepare('DELETE FROM plants WHERE id = ?');
        $deletePlant->execute([$id]);

        $_SESSION['message'] = ['type' => 'success', 'text' => 'Pflanze wurde erfolgreich gelöscht.'];
        exit;
    } catch (PDOException $e) {
        $_SESSION['message'] = ['type' => 'error', 'text' => 'Fehler beim Löschen: ' . $e->getMessage()];
        exit;
    }
}
?>

<h2>Pflanzenübersicht</h2>

<div class="plant-grid">
    <?php if (count($plants) > 0): ?>
        <?php foreach ($plants as $plant): ?>
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
                    <form action="index.php" method="post" onsubmit="return confirm('Wirklich löschen?');" style="display: inline;">
                        <input type="hidden" name="id" value="<?= $plant['id'] ?>">
                        <button type="submit" name="delete" class="button danger">Löschen</button>
                    </form>
                </div>
            </div>
        <?php endforeach; ?>
    <?php else: ?>
        <p>Keine Pflanzen gefunden. <a href="index.php?page=add">Erste Pflanze hinzufügen</a>.</p>
    <?php endif; ?>
</div>