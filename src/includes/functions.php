<?php
/**
 * Hilfsfunktionen für die Pflanzendatenbank
 */

/**
 * HTML-Escape für Ausgabe in der Webseite
 * 
 * @param string $string Der zu escapende String
 * @return string Der escapte String
 */
if (!function_exists('escape')) {
    function escape($string) {
        return htmlspecialchars($string, ENT_QUOTES, 'UTF-8');
    }
}

/**
 * Überprüft, ob ein Datum im Format Y-m-d gültig ist
 * 
 * @param string $date Das zu prüfende Datum
 * @return bool True wenn gültig, sonst false
 */
if (!function_exists('isValidDate')) {
    function isValidDate($date) {
        if (empty($date)) {
            return true; // Leeres Datum ist erlaubt
        }
        
        $d = DateTime::createFromFormat('Y-m-d', $date);
        return $d && $d->format('Y-m-d') === $date;
    }
}

/**
 * Generiert einen zufälligen Dateinamen für ein Bild
 * 
 * @param string $originalName Der Originalname der Datei
 * @return string Ein zufälliger Dateiname mit gleicher Dateiendung
 */
if (!function_exists('generateRandomFileName')) {
    function generateRandomFileName($originalName) {
        $extension = pathinfo($originalName, PATHINFO_EXTENSION);
        return md5($originalName . time() . rand(1000, 9999)) . '.' . $extension;
    }
}

/**
 * Formatiert ein Datum vom Format Y-m-d zu einer lesbaren Form
 * 
 * @param string $date Das zu formatierende Datum
 * @return string Das formatierte Datum oder leer, wenn keine Eingabe
 */
if (!function_exists('formatDate')) {
    function formatDate($date) {
        if (empty($date)) {
            return '';
        }
        
        $d = DateTime::createFromFormat('Y-m-d', $date);
        if ($d) {
            return $d->format('d.m.Y');
        }
        
        return $date;
    }
}

/**
 * Trunkiert einen Text auf eine bestimmte Länge
 * 
 * @param string $text Der zu kürzende Text
 * @param int $length Maximale Länge
 * @param string $append Text, der bei Kürzung angehängt wird
 * @return string Der gekürzte Text
 */
if (!function_exists('truncateText')) {
    function truncateText($text, $length = 100, $append = '...') {
        if (mb_strlen($text, 'UTF-8') <= $length) {
            return $text;
        }
        
        return mb_substr($text, 0, $length, 'UTF-8') . $append;
    }
}