<?php
include 'includes/functions.php';
include 'includes/db.php';

// Sicherstellen, dass ein Pflanzen-ID übergeben wurde
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    header('Location: index.php');
    exit;
}

$plant_id = (int)$_GET['id'];

// Verarbeitung des Formulars bei POST-Request
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['edit_plant'])) {
    try {
        $pdo->beginTransaction();
        
        // Pflanzeninformationen aktualisieren
        $stmt = $pdo->prepare("
            UPDATE plants 
            SET scientific_name = ?, common_name = ?, family = ?, 
                description = ?, habitat = ?, discovery_date = ?, discovery_location = ?
            WHERE id = ?
        ");
        
        $stmt->execute([
            $_POST['scientific_name'],
            $_POST['common_name'],
            $_POST['family'],
            $_POST['description'],
            $_POST['habitat'],
            $_POST['discovery_date'] ?: null,
            $_POST['discovery_location'],
            $plant_id
        ]);
        
        // Bestehende Bilder aktualisieren/löschen
        if (isset($_POST['existing_image_ids']) && is_array($_POST['existing_image_ids'])) {
            foreach ($_POST['existing_image_ids'] as $index => $image_id) {
                // Prüfen, ob das Bild gelöscht werden soll
                if (isset($_POST['delete_image'][$index]) && $_POST['delete_image'][$index] == 1) {
                    // Bilddaten abrufen, um die Datei zu löschen
                    $stmt = $pdo->prepare("SELECT file_path FROM images WHERE id = ?");
                    $stmt->execute([$image_id]);
                    $image = $stmt->fetch(PDO::FETCH_ASSOC);
                    
                    if ($image && file_exists('images/' . $image['file_path'])) {
                        unlink('images/' . $image['file_path']);
                    }
                    
                    // Bildeintrag aus der Datenbank löschen
                    $stmt = $pdo->prepare("DELETE FROM images WHERE id = ?");
                    $stmt->execute([$image_id]);
                } else {
                    // Bild aktualisieren
                    $stmt = $pdo->prepare("
                        UPDATE images 
                        SET caption = ?, taken_date = ?
                        WHERE id = ?
                    ");
                    
                    $stmt->execute([
                        $_POST['existing_captions'][$index],
                        $_POST['existing_taken_dates'][$index] ?: null,
                        $image_id
                    ]);
                }
            }
        }
        
        // Neue Bilder hinzufügen
        if (isset($_FILES['images']) && is_array($_FILES['images']['name'])) {
            $uploadDir = 'images/';
            
            if (!file_exists($uploadDir)) {
                mkdir($uploadDir, 0755, true);
            }
            
            for ($i = 0; $i < count($_FILES['images']['name']); $i++) {
                if ($_FILES['images']['error'][$i] === UPLOAD_ERR_OK && $_FILES['images']['size'][$i] > 0) {
                    $tmpName = $_FILES['images']['tmp_name'][$i];
                    $originalName = basename($_FILES['images']['name'][$i]);
                    $extension = pathinfo($originalName, PATHINFO_EXTENSION);
                    $newFileName = md5($originalName . time() . rand(1000, 9999)) . '.' . $extension;
                    $targetFilePath = $uploadDir . $newFileName;
                    
                    if (move_uploaded_file($tmpName, $targetFilePath)) {
                        $stmt = $pdo->prepare("
                            INSERT INTO images (plant_id, file_path, caption, taken_date)
                            VALUES (?, ?, ?, ?)
                        ");
                        
                        $stmt->execute([
                            $plant_id,
                            $newFileName,
                            $_POST['captions'][$i] ?? '',
                            $_POST['taken_dates'][$i] ?? null
                        ]);
                    }
                }
            }
        }
        
        $pdo->commit();
        echo '<div class="success-message">Pflanze aktualisiert!</div>';
        exit;
    }
    catch (PDOException $e) {
        $pdo->rollBack();
        $error = 'Datenbankfehler: ' . $e->getMessage();
    }
    catch (Exception $e) {
        $pdo->rollBack();
        $error = 'Fehler: ' . $e->getMessage();
    }
}

// Pflanzendaten abrufen für die Anzeige im Formular
try {
    $stmt = $pdo->prepare('SELECT * FROM plants WHERE id = ?');
    $stmt->execute([$plant_id]);
    $plant = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$plant) {
        echo '<div class="error-message">Pflanze nicht gefunden.</div>';
        echo '<p><a href="index.php" class="button">Zurück zur Übersicht</a></p>';
        exit;
    }

    // Bilder der Pflanze abrufen
    $stmt = $pdo->prepare('SELECT * FROM images WHERE plant_id = ? ORDER BY id ASC');
    $stmt->execute([$plant_id]);
    $images = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    echo '<div class="error-message">Datenbankfehler: ' . escape($e->getMessage()) . '</div>';
    exit;
}
?>

<h1>Pflanze bearbeiten</h1>

<?php if (isset($error)): ?>
    <div class="error-message"><?= escape($error) ?></div>
<?php endif; ?>

<form method="post" enctype="multipart/form-data" class="edit-form">
    <div class="form-group">
        <label for="scientific_name">Wissenschaftlicher Name *</label>
        <input type="text" id="scientific_name" name="scientific_name" value="<?= escape($plant['scientific_name']) ?>" required>
    </div>

    <div class="form-group">
        <label for="common_name">Gebräuchlicher Name</label>
        <input type="text" id="common_name" name="common_name" value="<?= escape($plant['common_name']) ?>">
    </div>

    <div class="form-group">
        <label for="family">Familie</label>
        <input type="text" id="family" name="family" value="<?= escape($plant['family']) ?>">
    </div>

    <div class="form-group">
        <label for="description">Beschreibung</label>
        <textarea id="description" name="description" rows="5"><?= escape($plant['description']) ?></textarea>
    </div>

    <div class="form-group">
        <label for="habitat">Lebensraum</label>
        <textarea id="habitat" name="habitat" rows="3"><?= escape($plant['habitat']) ?></textarea>
    </div>

    <div class="form-group">
        <label for="discovery_date">Entdeckungsdatum</label>
        <input type="date" id="discovery_date" name="discovery_date" value="<?= escape($plant['discovery_date']) ?>">
    </div>

    <div class="form-group">
        <label for="discovery_location">Entdeckungsort</label>
        <input type="text" id="discovery_location" name="discovery_location" value="<?= escape($plant['discovery_location']) ?>">
    </div>

    <h2>Bestehende Bilder</h2>
    <?php if (!empty($images)): ?>
        <div class="existing-images">
            <?php foreach ($images as $index => $image): ?>
                <div class="image-entry">
                    <img src="images/<?= escape($image['file_path']) ?>" alt="<?= escape($image['caption']) ?>" class="thumbnail">
                    <input type="hidden" name="existing_image_ids[]" value="<?= $image['id'] ?>">

                    <div class="form-group">
                        <label>Bildunterschrift</label>
                        <input type="text" name="existing_captions[]" value="<?= escape($image['caption']) ?>">
                    </div>

                    <div class="form-group">
                        <label>Aufnahmedatum</label>
                        <input type="date" name="existing_taken_dates[]" value="<?= escape($image['taken_date']) ?>">
                    </div>

                    <div class="form-group checkbox">
                        <label>
                            <input type="checkbox" name="delete_image[]" value="1"> Bild löschen
                        </label>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php else: ?>
        <p>Keine Bilder vorhanden.</p>
    <?php endif; ?>

    <h2>Neue Bilder hinzufügen</h2>
    <div class="new-images">
        <div class="image-entry">
            <div class="form-group">
                <label>Bild auswählen</label>
                <input type="file" name="images[]" accept="image/*">
            </div>
            <div class="form-group">
                <label>Bildunterschrift</label>
                <input type="text" name="captions[]">
            </div>
            <div class="form-group">
                <label>Aufnahmedatum</label>
                <input type="date" name="taken_dates[]">
            </div>
        </div>

        <button type="button" id="add-more-images" class="button secondary">Weiteres Bild hinzufügen</button>
    </div>

    <div class="form-actions">
        <button type="submit" name="edit_plant" class="button primary">Pflanze aktualisieren</button>
        <a href="index.php?page=view&id=<?= $plant_id ?>" class="button">Abbrechen</a>
    </div>
</form>

<script>
document.getElementById('add-more-images').addEventListener('click', function() {
    const imageEntry = document.createElement('div');
    imageEntry.className = 'image-entry';
    imageEntry.innerHTML = `
        <div class="form-group">
            <label>Bild auswählen</label>
            <input type="file" name="images[]" accept="image/*">
        </div>
        <div class="form-group">
            <label>Bildunterschrift</label>
            <input type="text" name="captions[]">
        </div>
        <div class="form-group">
            <label>Aufnahmedatum</label>
            <input type="date" name="taken_dates[]">
        </div>
    `;

    const newImagesDiv = document.querySelector('.new-images');
    newImagesDiv.insertBefore(imageEntry, document.getElementById('add-more-images'));
});
</script>
