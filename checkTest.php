<?php
// Encryption function
function encryptData($plaintext, $key) {
    $ivLength = openssl_cipher_iv_length('aes-256-cbc');
    $iv = openssl_random_pseudo_bytes($ivLength);
    $ciphertext = openssl_encrypt($plaintext, 'aes-256-cbc', $key, 0, $iv);
    // Combine IV and ciphertext for storage
    return base64_encode($iv . $ciphertext);
}

// Decryption function
function decryptData($encryptedData, $key) {
    // 1. Decode the Base64 string
    $data = base64_decode($encryptedData);
    
    // 2. Get the IV length for aes-256-cbc
    $ivLength = openssl_cipher_iv_length('aes-256-cbc');
    
    // 3. Extract the IV from the beginning of the string
    $iv = substr($data, 0, $ivLength);
    
    // 4. Extract the ciphertext from the remaining part of the string
    $ciphertext = substr($data, $ivLength);
    
    // 5. Decrypt the data
    return openssl_decrypt($ciphertext, 'aes-256-cbc', $key, 0, $iv);
}

// ... (Keep your encryptData and decryptData functions the same) ...

$key = '6057110_this_needs_to_be_32_bytes'; 

// --- READ ORIGINAL PHP FILE ---
if (!file_exists('bestand.php')) {
    // Creating a dummy PHP file if it doesn't exist
    file_put_contents('bestand.php', '<?php echo "Hello from the original file!"; ?>');
}
$plaintext = file_get_contents('bestand.php'); // Reads the raw PHP code

// --- ENCRYPTION & SAVING ---
$encryptedData = encryptData($plaintext, $key);
file_put_contents('bestandE.php', $encryptedData);
echo "Encrypted and saved to bestandE.php<br>";

// --- READING & DECRYPTION ---
$encryptedContentFromFile = file_get_contents('bestandE.php');
$decryptedText = decryptData($encryptedContentFromFile, $key);

// Save the decrypted PHP code back
file_put_contents('bestandP.php', $decryptedText);
echo "Decrypted and saved back to bestandP.php";
?>