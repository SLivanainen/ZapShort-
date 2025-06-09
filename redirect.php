<?php
// redirect.php

$db_file = 'urls.sqlite';
$code = $_GET['code'] ?? null;

if (!$code || !preg_match('/^[a-zA-Z0-9]{6}$/', $code)) {
    header("HTTP/1.0 404 Not Found");
    echo '<h1>404 Not Found</h1><p>The link you followed is invalid or has expired.</p>';
    exit;
}

try {
    $pdo = new PDO('sqlite:' . $db_file);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Find the URL and its settings
    $stmt = $pdo->prepare("SELECT long_url, enable_preview FROM urls WHERE short_code = ?");
    $stmt->execute([$code]);
    $url_data = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($url_data) {
        // Increment the click counter (fire-and-forget)
        $update_stmt = $pdo->prepare("UPDATE urls SET clicks = clicks + 1 WHERE short_code = ?");
        $update_stmt->execute([$code]);

        $long_url = $url_data['long_url'];
        $enable_preview = $url_data['enable_preview'];

        if ($enable_preview) {
            // Show the preview page
            echo '<!DOCTYPE html><html lang="en"><head><meta charset="UTF-8"><title>Redirecting...</title>';
            echo '<meta http-equiv="refresh" content="5;url=' . htmlspecialchars($long_url) . '">';
            echo '<style>body{font-family: sans-serif; text-align: center; padding-top: 50px; background: #f4f4f4;} .container { max-width: 600px; margin: auto; background: white; padding: 20px; border-radius: 8px; box-shadow: 0 2px 5px rgba(0,0,0,0.1); } a { color: #0062ff; text-decoration: none; font-weight: bold; } p { color: #555; }</style>';
            echo '</head><body><div class="container"><h1>Hold on...</h1>';
            echo '<p>You are being redirected to the following URL:</p>';
            echo '<p><strong>' . htmlspecialchars($long_url) . '</strong></p>';
            echo '<p>If you are not redirected automatically in 5 seconds, <a href="' . htmlspecialchars($long_url) . '">click here</a>.</p>';
            // You can place another ad here
            echo '</div></body></html>';
        } else {
            // Perform a direct 301 redirect for permanent links
            header("Location: " . $long_url, true, 301);
            exit;
        }
    } else {
        // Code not found
        header("HTTP/1.0 404 Not Found");
        echo '<h1>404 Not Found</h1><p>The link you followed is invalid or has expired.</p>';
    }

} catch (PDOException $e) {
    header("HTTP/1.0 500 Internal Server Error");
    echo '<h1>500 Internal Server Error</h1><p>A database error occurred.</p>';
}
?>
