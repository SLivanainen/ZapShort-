<?php
session_start();
$PASSWORD = 'YOUR_SECRET_PASSWORD'; // <-- CHANGE THIS!

// Login logic
if (isset($_POST['password'])) {
    if ($_POST['password'] === $PASSWORD) {
        $_SESSION['loggedin'] = true;
    } else {
        $login_error = "Invalid password!";
    }
}

// Logout logic
if (isset($_GET['logout'])) {
    session_destroy();
    header('Location: admin.php');
    exit;
}

if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
    // Show login form
    echo '<!DOCTYPE html><html lang="en"><head><title>Admin Login</title><style>body{font-family:sans-serif;display:flex;justify-content:center;align-items:center;height:100vh;background:#f0f2f5;} form{background:white;padding:2rem;border-radius:8px;box-shadow:0 4px 12px rgba(0,0,0,0.1);} input{display:block;margin-bottom:1rem;padding:0.5rem;} .error{color:red;}</style></head><body>';
    echo '<form method="POST"><label for="password">Password:</label><input type="password" name="password" id="password" autofocus>';
    if (isset($login_error)) echo '<p class="error">'.$login_error.'</p>';
    echo '<button type="submit">Login</button></form>';
    echo '</body></html>';
    exit;
}

// --- If logged in, show the admin panel ---
$db_file = 'urls.sqlite';
$pdo = new PDO('sqlite:' . $db_file);
$stmt = $pdo->query("SELECT short_code, long_url, clicks, created_at, enable_preview FROM urls ORDER BY created_at DESC");
$links = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Admin Panel - Link Management</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <style>
        body { font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif; margin: 0; background: #f8f9fa; }
        .container { max-width: 1200px; margin: 20px auto; padding: 20px; }
        h1 { text-align: center; }
        a.logout { float: right; }
        table { width: 100%; border-collapse: collapse; margin-top: 20px; background: white; box-shadow: 0 2px 4px rgba(0,0,0,0.1); }
        th, td { padding: 12px 15px; text-align: left; border-bottom: 1px solid #ddd; }
        th { background-color: #f2f2f2; }
        td a { color: #0062ff; }
        td.long-url { max-width: 400px; overflow: hidden; text-overflow: ellipsis; white-space: nowrap; }
    </style>
</head>
<body>
    <div class="container">
        <a href="?logout=true" class="logout">Logout</a>
        <h1>Link Management</h1>
        <table>
            <thead>
                <tr>
                    <th>Short Link</th>
                    <th>Original URL</th>
                    <th>Clicks</th>
                    <th>Created At</th>
                    <th>Preview?</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($links as $link): ?>
                <tr>
                    <td><a href="/<?php echo htmlspecialchars($link['short_code']); ?>" target="_blank">/<?php echo htmlspecialchars($link['short_code']); ?></a></td>
                    <td class="long-url" title="<?php echo htmlspecialchars($link['long_url']); ?>"><a href="<?php echo htmlspecialchars($link['long_url']); ?>" target="_blank"><?php echo htmlspecialchars($link['long_url']); ?></a></td>
                    <td><?php echo $link['clicks']; ?></td>
                    <td><?php echo $link['created_at']; ?></td>
                    <td><?php echo $link['enable_preview'] ? 'Yes' : 'No'; ?></td>
                </tr>
                <?php endforeach; ?>
                <?php if (empty($links)): ?>
                <tr><td colspan="5">No links have been created yet.</td></tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</body>
</html>
