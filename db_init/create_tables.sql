-- Tabelle für Pflanzen
CREATE TABLE plants (
    id INT AUTO_INCREMENT PRIMARY KEY,
    scientific_name VARCHAR(255) NOT NULL,
    common_name VARCHAR(255),
    family VARCHAR(100),
    description TEXT,
    habitat TEXT,
    discovery_date DATE,
    discovery_location VARCHAR(255),
    added_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Tabelle für Bilder mit Fremdschlüsselbeziehung zu plants
CREATE TABLE images (
    id INT AUTO_INCREMENT PRIMARY KEY,
    plant_id INT NOT NULL,
    file_path VARCHAR(255) NOT NULL,
    caption TEXT,
    taken_date DATE,
    FOREIGN KEY (plant_id) REFERENCES plants(id) ON DELETE CASCADE
);