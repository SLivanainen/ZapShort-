<?php
// shorten.php

header('Content-Type: application/json');

$db_file = 'urls.sqlite';

// --- Helper function to generate a unique short code ---
function generateShortCode($length = 6) {
    // A more robust character set
    $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
    $charLength = strlen($characters);
    $randomString = '';
    for ($i = 0; $i < $length; $i++) {
        $randomString .= $characters[random_int(0, $charLength - 1)];
    }
    return $randomString;
}

// --- Main Logic ---
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $input = json_decode(file_get_contents('php://input'), true);
    $long_url = $input['url'] ?? null;
    $enable_preview = filter_var($input['preview'] ?? false, FILTER_VALIDATE_BOOLEAN);

    // Validate the URL
    if (!$long_url || !filter_var($long_url, FILTER_VALIDATE_URL)) {
        echo json_encode(['success' => false, 'error' => 'Invalid URL provided.']);
        exit;
    }

    try {
        $pdo = new PDO('sqlite:' . $db_file);
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        // Check if the URL (with same preview setting) has already been shortened
        $stmt = $pdo->prepare("SELECT short_code FROM urls WHERE long_url = ? AND enable_preview = ?");
        $stmt->execute([$long_url, $enable_preview]);
        $existing = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($existing) {
            echo json_encode(['success' => true, 'short_code' => $existing['short_code']]);
            exit;
        }

        // Generate a new unique short code
        do {
            $short_code = generateShortCode();
            $stmt = $pdo->prepare("SELECT id FROM urls WHERE short_code = ?");
            $stmt->execute([$short_code]);
        } while ($stmt->fetch());

        // Insert the new link into the database
        $stmt = $pdo->prepare("INSERT INTO urls (short_code, long_url, enable_preview) VALUES (?, ?, ?)");
        $stmt->execute([$short_code, $long_url, $enable_preview]);

        echo json_encode(['success' => true, 'short_code' => $short_code]);

    } catch (PDOException $e) {
        // Log error to a file for debugging instead of showing to user
        // error_log($e->getMessage());
        echo json_encode(['success' => false, 'error' => 'Database error. Could not shorten URL.']);
    }
} else {
    // Handle invalid request method
    http_response_code(405);
    echo json_encode(['success' => false, 'error' => 'Method Not Allowed']);
}
?>
