<?php
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    echo '<div class="error-message">Ungültige Pflanzen-ID.</div>';
    echo '<p><a href="index.php" class="button">Zurück zur Übersicht</a></p>';
    exit;
}

$plant_id = (int)$_GET['id'];

// Pflanzendaten abrufen
try {
    $stmt = $pdo->prepare('SELECT * FROM plants WHERE id = ?');
    $stmt->execute([$plant_id]);
    $plant = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$plant) {
        echo '<div class="error-message">Pflanze nicht gefunden.</div>';
        echo '<p><a href="index.php" class="button">Zurück zur Übersicht</a></p>';
        exit;
    }

    // Bilder abrufen
    $stmt = $pdo->prepare('SELECT * FROM images WHERE plant_id = ?');
    $stmt->execute([$plant_id]);
    $images = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    echo '<div class="error-message">Datenbankfehler: ' . $e->getMessage() . '</div>';
    exit;
}
?>

<div class="plant-detail">
    <h2><?= escape($plant['scientific_name']) ?></h2>

    <div class="plant-meta">
        <?php if (!empty($plant['common_name'])): ?>
            <p><strong>Gebräuchlicher Name:</strong> <?= escape($plant['common_name']) ?></p>
        <?php endif; ?>

        <?php if (!empty($plant['family'])): ?>
            <p><strong>Familie:</strong> <?= escape($plant['family']) ?></p>
        <?php endif; ?>

        <?php if (!empty($plant['discovery_date'])): ?>
            <p><strong>Entdeckungsdatum:</strong> <?= date('d.m.Y', strtotime($plant['discovery_date'])) ?></p>
        <?php endif; ?>

        <?php if (!empty($plant['discovery_location'])): ?>
            <p><strong>Entdeckungsort:</strong> <?= escape($plant['discovery_location']) ?></p>
        <?php endif; ?>

        <p><strong>Hinzugefügt am:</strong> <?= date('d.m.Y', strtotime($plant['added_date'])) ?></p>
    </div>

    <?php if (!empty($plant['description'])): ?>
        <div class="plant-section">
            <h3>Beschreibung</h3>
            <p><?= nl2br(escape($plant['description'])) ?></p>
        </div>
    <?php endif; ?>

    <?php if (!empty($plant['habitat'])): ?>
        <div class="plant-section">
            <h3>Lebensraum</h3>
            <p><?= nl2br(escape($plant['habitat'])) ?></p>
        </div>
    <?php endif; ?>

    <?php if (count($images) > 0): ?>
        <div class="plant-section">
            <h3>Bilder</h3>
            <div class="image-gallery">
                <?php foreach ($images as $image): ?>
                    <div class="gallery-item">
                        <a href="images/<?= escape($image['file_path']) ?>" target="_blank">
                            <img src="images/<?= escape($image['file_path']) ?>" alt="<?= escape($image['caption']) ?>">
                        </a>
                        <?php if (!empty($image['caption'])): ?>
                            <p class="caption"><?= escape($image['caption']) ?></p>
                        <?php endif; ?>
                        <?php if (!empty($image['taken_date'])): ?>
                            <p class="taken-date">Aufgenommen am: <?= date('d.m.Y', strtotime($image['taken_date'])) ?></p>
                        <?php endif; ?>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    <?php endif; ?>

    <div class="plant-actions">
        <a href="index.php?page=edit&id=<?= $plant_id ?>" class="button">Bearbeiten</a>
        <a href="index.php" class="button">Zurück zur Übersicht</a>
        <form action="index.php" method="post" onsubmit="return confirm('Wirklich löschen?');" style="display: inline;">
            <input type="hidden" name="id" value="<?= $plant_id ?>">
            <button type="submit" name="delete" class="button danger">Löschen</button>
        </form>
    </div>
</div>