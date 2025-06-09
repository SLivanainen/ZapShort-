<?php
// db_setup.php

// This is the name for your SQLite database file
$db_file = 'urls.sqlite';

try {
    // Create (connect to) the database
    $pdo = new PDO('sqlite:' . $db_file);
    // Set errormode to exceptions
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // SQL to create a new table
    $sql = "CREATE TABLE IF NOT EXISTS urls (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        short_code VARCHAR(10) NOT NULL UNIQUE,
        long_url TEXT NOT NULL,
        created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
        clicks INTEGER DEFAULT 0,
        enable_preview BOOLEAN NOT NULL DEFAULT 0
    )";

    // Execute the query
    $pdo->exec($sql);

    // Create an index for faster lookups on short_code
    $pdo->exec("CREATE INDEX IF NOT EXISTS idx_short_code ON urls(short_code)");

    echo "Database and 'urls' table created successfully!";

} catch (PDOException $e) {
    // Print error message
    die("Error creating database: " . $e->getMessage());
}
?>
