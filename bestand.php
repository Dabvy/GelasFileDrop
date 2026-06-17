<?php
/**
 * Functie om data te versleutelen met AES-256-CBC
 */
function encryptData($plaintext, $key) {
    // Bepaal de lengte van de Initialisatie Vector (IV) die nodig is voor AES-256-CBC (16 bytes)
    $ivLength = openssl_cipher_iv_length('aes-256-cbc');
    // Genereer een cryptografisch veilige, willekeurige IV
    $iv = openssl_random_pseudo_bytes($ivLength);
    // Versleutel de data
    $ciphertext = openssl_encrypt($plaintext, 'aes-256-cbc', $key, 0, $iv);
    // Plak de IV voor de versleutelde tekst en zet het om naar Base64 voor veilige opslag
    return base64_encode($iv . $ciphertext);
}

/**
 * Functie om data te ontsleutelen
 */
function decryptData($encryptedData, $key) {
    // Decodeer de Base64 string naar de ruwe IV + ciphertext combinatie
    $data = base64_decode($encryptedData);
    // Bepaal de IV lengte
    $ivLength = openssl_cipher_iv_length('aes-256-cbc');
    // Snijd de eerste 16 bytes eruit: dit is de originele IV
    $iv = substr($data, 0, $ivLength);
    // De rest van de string is de daadwerkelijke versleutelde tekst (ciphertext)
    $ciphertext = substr($data, $ivLength);
    // Ontsleutel de ciphertext met de sleutel en de IV
    return openssl_decrypt($ciphertext, 'aes-256-cbc', $key, 0, $iv);
}

// De geheime encryptiesleutel (moet exact 32 bytes lang zijn voor AES-256)
$key = '6057110_this_needs_to_be_32_bytes'; 
$melding = "";

// Controleren of het formulier is verzonden (POST) en of het bestand zonder fouten is geüpload
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['geheimBestand']) && $_FILES['geheimBestand']['error'] === UPLOAD_ERR_OK) {
    
    // Haal de tijdelijke locatie, de naam en de extensie van het geüploade bestand op
    $fileTmpPath = $_FILES['geheimBestand']['tmp_name'];
    $fileName = $_FILES['geheimBestand']['name'];
    $fileExtension = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));
    
    // Lijst met bestandstypen die ons systeem accepteert
    $toegestaneExtensies = ['jpg', 'jpeg', 'png', 'gif'];
    
    // Controleer of de extensie van het geüploade bestand is toegestaan
    if (in_array($fileExtension, $toegestaneExtensies)) {
        
        // 1. Lees de ruwe binaire inhoud van de geüploade afbeelding in
        $rawImageData = file_get_contents($fileTmpPath);

        // 2. Versleutel de afbeeldingsdata en sla het resultaat op in bestandE.php
        $encryptedData = encryptData($rawImageData, $key);
        file_put_contents('bestandE.php', $encryptedData);

        // 3. Lees de versleutelde data direct weer uit bestandE.php en ontsleutel deze
        $encryptedContentFromFile = file_get_contents('bestandE.php');
        $decryptedImageData = decryptData($encryptedContentFromFile, $key);

        // Bepaal het juiste Mime-type (nodig voor de browser, zet 'jpg' om naar 'jpeg')
        $mimeType = 'image/' . ($fileExtension === 'jpg' ? 'jpeg' : $fileExtension);
        
        // Zet de ontsleutelde binaire data om naar een Base64 string
        $base64Image = base64_encode($decryptedImageData);
        // Bouw een Data URL op zodat de afbeelding direct in HTML/JS gebruikt kan worden zonder fysiek bestand
        $dataUrl = 'data:' . $mimeType . ';base64,' . $base64Image;

        // 4. Bouw de broncode voor het nieuwe bestandP.php op
        $phpCodeVoorBestandP = "<?php\n?>\n";
        $phpCodeVoorBestandP .= "<!DOCTYPE html>\n<html lang='nl'>\n<head>\n";
        $phpCodeVoorBestandP .= "    <meta charset='UTF-8'>\n    <title>Ontsleuteld Bestand</title>\n</head>\n<body>\n\n";
        $phpCodeVoorBestandP .= "    <h1>Ontsleutelde Afbeelding:</h1>\n";
        
        // Voeg de img-tag toe met de Data URL als bron om de afbeelding op het scherm te tonen
        $phpCodeVoorBestandP .= "    <img src='" . $dataUrl . "' alt='Ontsleuteld' style='max-width:100%; height:auto;'><br><br>\n";
        $phpCodeVoorBestandP .= "    <p>Als het goed is, is de download ook automatisch gestart.</p>\n\n";
        
        // Voeg JavaScript toe die direct na het laden een onzichtbare downloadlink aanmaakt en 'aanklikt'
        $phpCodeVoorBestandP .= "    <script>\n";
        $phpCodeVoorBestandP .= "    window.addEventListener('DOMContentLoaded', () => {\n";
        $phpCodeVoorBestandP .= "        const link = document.createElement('a');\n"; // Maak een <a> element aan
        $phpCodeVoorBestandP .= "        link.href = '" . $dataUrl . "';\n";           // Koppel de afbeelding aan de link
        $phpCodeVoorBestandP .= "        link.download = 'gedownload_bestand." . $fileExtension . "';\n"; // Geef de downloadnaam mee
        $phpCodeVoorBestandP .= "        document.body.appendChild(link);\n";          // Voeg link tijdelijk toe aan de pagina
        $phpCodeVoorBestandP .= "        link.click();\n";                             // Klik er automatisch op
        $phpCodeVoorBestandP .= "        document.body.removeChild(link);\n";          // Verwijder de link direct weer
        $phpCodeVoorBestandP .= "    });\n";
        $phpCodeVoorBestandP .= "    </script>\n\n";
        
        $phpCodeVoorBestandP .= "</body>\n</html>";
        
        // Schrijf de opgebouwde HTML en JavaScript code daadwerkelijk weg naar bestandP.php
        file_put_contents('bestandP.php', $phpCodeVoorBestandP);
        
        // Toon succesmelding met een link naar het resultaat
        $melding = "<p style='color: green;'>Afbeelding verwerkt! Open <a href='bestandP.php' target='_blank'>bestandP.php</a> om het te bekijken en te downloaden.</p>";
    } else {
        // Foutmelding als het bestandstype niet in de lijst stond
        $melding = "<p style='color: red;'>Fout: Alleen JPG, JPEG, PNG en GIF bestanden zijn toegestaan.</p>";
    }
}
?>

<!DOCTYPE html>
<html lang="nl">
<head>
    <meta charset="UTF-8">
    <title>Afbeelding Encryptie Systeem</title>
</head>
<body>

    <h2>Upload een afbeelding:</h2>
    <?php echo $melding; ?>

</body>
</html>