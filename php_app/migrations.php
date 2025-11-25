<?php
$host = getenv('DB_HOST');
$db   = getenv('DB_NAME');
$user = getenv('DB_USER');
$pass = getenv('DB_PASSWORD');

$pdo = new PDO("mysql:host=$host;dbname=$db;charset=utf8mb4", $user, $pass, [
    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
]);

// Create migrations table if not exists
$pdo->exec("
CREATE TABLE IF NOT EXISTS migrations (
    id INT AUTO_INCREMENT PRIMARY KEY,
    filename VARCHAR(255) NOT NULL,
    applied_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
)
");

$dir = __DIR__ . '/sql';
$files = glob("$dir/*.sql");
sort($files);

foreach ($files as $file) {
    $filename = basename($file);

    $stmt = $pdo->prepare("SELECT COUNT(*) FROM migrations WHERE filename = ?");
    $stmt->execute([$filename]);
    if ($stmt->fetchColumn() > 0) {
        echo "$filename already applied.\n";
        continue;
    }

    $sql = file_get_contents($file);
    $pdo->beginTransaction();
    try {
        $pdo->exec($sql);
        $pdo->commit();

        $stmt = $pdo->prepare("INSERT INTO migrations (filename) VALUES (?)");
        $stmt->execute([$filename]);

        echo "Applied $filename\n";
    } catch (Exception $e) {
        $pdo->rollBack();
        echo "Error applying $filename: " . $e->getMessage() . "\n";
        exit(1);
    }
}