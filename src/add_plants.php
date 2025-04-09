<?php
// Pflanzen aus der Datenbank abrufen - mit prepared statement
function getPlants($pdo) {
    $stmt = $pdo->query('SELECT p.*,
                     (SELECT file_path FROM images WHERE plant_id = p.id LIMIT 1) AS thumbnail
                     FROM plants p ORDER BY scientific_name');
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

// Löschfunktion für Pflanzen
function deletePlant($pdo, $id) {
    try {
        $pdo->beginTransaction();
        
        // Bilder löschen
        $imagePath = $pdo->prepare('SELECT file_path FROM images WHERE plant_id = ?');
        $imagePath->execute([$id]);

        while ($path = $imagePath->fetch(PDO::FETCH_ASSOC)) {
            $filePath = 'images/' . $path['file_path'];
            if (file_exists($filePath)) {
                unlink($filePath);
            }
        }

        // Bilder aus der Datenbank löschen
        $deleteImages = $pdo->prepare('DELETE FROM images WHERE plant_id = ?');
        $deleteImages->execute([$id]);

        // Pflanze löschen
        $deletePlant = $pdo->prepare('DELETE FROM plants WHERE id = ?');
        $deletePlant->execute([$id]);
        
        $pdo->commit();
        return true;
    } catch (PDOException $e) {
        $pdo->rollBack();
        return $e->getMessage();
    }
}

// Funktion zum Hinzufügen einer Pflanze
function addPlant($pdo, $plantData, $files) {
    $errors = [];
    
    // Validierung
    if (empty($plantData['scientific_name'])) {
        $errors[] = "Der wissenschaftliche Name ist erforderlich.";
    }
    
    if (!empty($errors)) {
        return ['success' => false, 'errors' => $errors];
    }
    
    try {
        $pdo->beginTransaction();
        
        // Pflanze in DB einfügen
        $stmt = $pdo->prepare("INSERT INTO plants
                             (scientific_name, common_name, family,
                             description, habitat, discovery_date,
                             discovery_location)
                             VALUES (?, ?, ?, ?, ?, ?, ?)");

        $stmt->execute([
            $plantData['scientific_name'],
            $plantData['common_name'] ?? '',
            $plantData['family'] ?? '',
            $plantData['description'] ?? '',
            $plantData['habitat'] ?? '',
            !empty($plantData['discovery_date']) ? $plantData['discovery_date'] : null,
            $plantData['discovery_location'] ?? ''
        ]);

        $plant_id = $pdo->lastInsertId();
        
        // Bilder verarbeiten
        if (!empty($files['images']['name'][0])) {
            // Prüfen ob der Ordner existiert, ansonsten erstellen
            if (!file_exists('images')) {
                mkdir('images', 0777, true);
            }

            foreach ($files['images']['tmp_name'] as $key => $tmp_name) {
                if ($files['images']['error'][$key] === 0) {
                    // Sicherer Dateiname generieren
                    $fileInfo = pathinfo($files['images']['name'][$key]);
                    $fileName = time() . '_' . uniqid() . '.' . $fileInfo['extension'];
                    $destination = 'images/' . $fileName;

                    // Bild hochladen
                    if (move_uploaded_file($tmp_name, $destination)) {
                        // In Datenbank speichern
                        $imgStmt = $pdo->prepare("INSERT INTO images
                                            (plant_id, file_path, caption, taken_date)
                                            VALUES (?, ?, ?, ?)");

                        $caption = $plantData['captions'][$key] ?? '';
                        $taken_date = !empty($plantData['taken_dates'][$key]) ? $plantData['taken_dates'][$key] : null;

                        $imgStmt->execute([
                            $plant_id,
                            $fileName,
                            $caption,
                            $taken_date
                        ]);
                    } else {
                        $errors[] = "Fehler beim Hochladen des Bildes: " . escape($files['images']['name'][$key]);
                    }
                }
            }
        }
        
        $pdo->commit();
        return ['success' => true, 'plant_id' => $plant_id];
    } catch (PDOException $e) {
        $pdo->rollBack();
        return ['success' => false, 'errors' => ["Datenbankfehler: " . $e->getMessage()]];
    }
}

// Hauptlogik für das Hinzufügen von Pflanzen - VOR jeglicher HTML-Ausgabe!
$errors = [];
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_plant'])) {
    $result = addPlant($pdo, $_POST, $_FILES);
    
    if ($result['success']) {
        $_SESSION['message'] = ['type' => 'success', 'text' => 'Pflanze wurde erfolgreich hinzugefügt!'];
        // Umleitung VOR jeglicher Ausgabe
        header("Location: index.php?page=view&id=" . $result['plant_id']);
        exit;
    } else {
        $errors = $result['errors'];
    }
}

// Ab hier kann HTML-Output beginnen
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Pflanze hinzufügen</title>
    <!-- CSS und weitere Head-Elemente hier einfügen -->
</head>
<body>
    <?php 
    // Nachrichten aus der Session anzeigen und entfernen, wenn vorhanden
    if (isset($_SESSION['message'])): ?>
        <div class="<?= $_SESSION['message']['type'] ?>-message">
            <?= $_SESSION['message']['text'] ?>
        </div>
        <?php unset($_SESSION['message']); ?>
    <?php endif; ?>

    <?php // Fehler anzeigen, falls vorhanden ?>
    <?php if (!empty($errors)): ?>
        <div class="error-message">
            <ul>
                <?php foreach ($errors as $error): ?>
                    <li><?= escape($error) ?></li>
                <?php endforeach; ?>
            </ul>
        </div>
    <?php endif; ?>

    <h2>Neue Pflanze hinzufügen</h2>

    <form action="<?= htmlspecialchars($_SERVER['PHP_SELF']) ?>?page=add" method="post" enctype="multipart/form-data" class="plant-form">
        <div class="form-group">
            <label for="scientific_name">Wissenschaftlicher Name *</label>
            <input type="text" id="scientific_name" name="scientific_name" required>
        </div>

        <div class="form-group">
            <label for="common_name">Gebräuchlicher Name</label>
            <input type="text" id="common_name" name="common_name">
        </div>

        <div class="form-group">
            <label for="family">Familie</label>
            <input type="text" id="family" name="family">
        </div>

        <div class="form-group">
            <label for="description">Beschreibung</label>
            <textarea id="description" name="description" rows="4"></textarea>
        </div>

        <div class="form-group">
            <label for="habitat">Lebensraum</label>
            <textarea id="habitat" name="habitat" rows="3"></textarea>
        </div>

        <div class="form-group">
            <label for="discovery_date">Entdeckungsdatum</label>
            <input type="date" id="discovery_date" name="discovery_date">
        </div>

        <div class="form-group">
            <label for="discovery_location">Entdeckungsort</label>
            <input type="text" id="discovery_location" name="discovery_location">
        </div>

        <h3>Bilder</h3>
        <div id="image-container">
            <div class="image-input-group">
                <div class="form-group">
                    <label>Bild</label>
                    <input type="file" name="images[]" accept="image/*">
                </div>
                <div class="form-group">
                    <label>Beschreibung</label>
                    <input type="text" name="captions[]">
                </div>
                <div class="form-group">
                    <label>Aufnahmedatum</label>
                    <input type="date" name="taken_dates[]">
                </div>
            </div>
        </div>

        <div class="form-actions">
            <button type="button" id="add-image" class="button">Weiteres Bild hinzufügen</button>
            <button type="submit" name="add_plant" class="button primary">Pflanze speichern</button>
            <a href="index.php" class="button">Abbrechen</a>
        </div>
    </form>

    <script>
    document.addEventListener('DOMContentLoaded', function() {
        // Button zum Hinzufügen weiterer Bilder
        const addImageBtn = document.getElementById('add-image');
        
        if (addImageBtn) {
            addImageBtn.addEventListener('click', function() {
                const container = document.getElementById('image-container');
                const group = document.querySelector('.image-input-group').cloneNode(true);

                // Eingabefelder zurücksetzen
                const inputs = group.querySelectorAll('input');
                inputs.forEach(function(input) {
                    input.value = '';
                });

                container.appendChild(group);
            });
        }
    });
    </script>
</body>
</html>