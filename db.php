<?php
// db.php
declare(strict_types=1);

/**
 * Carrega variáveis de ambiente a partir de um arquivo .env simples (KEY=VALUE).
 */
function loadEnv(string $envPath): void
{
    if (!is_readable($envPath)) {
        return;
    }

    $lines = file($envPath, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    if ($lines === false) {
        return;
    }

    foreach ($lines as $line) {
        $line = trim($line);

        if ($line === '' || str_starts_with($line, '#')) {
            continue;
        }

        $parts = explode('=', $line, 2);
        if (count($parts) !== 2) {
            continue;
        }

        $key = trim($parts[0]);
        $value = trim($parts[1]);

        if ($key === '') {
            continue;
        }

        $value = trim($value, "\"'");

        if (getenv($key) === false) {
            putenv("{$key}={$value}");
            $_ENV[$key] = $value;
            $_SERVER[$key] = $value;
        }
    }
}

$envFile = __DIR__ . '/.env';
loadEnv($envFile);

$dbPath = getenv('TODO_DB_PATH') ?: './storage/todo.sqlite';
if (!str_starts_with($dbPath, '/')) {
    $dbPath = __DIR__ . '/' . ltrim($dbPath, './');
}
$dbDirectory = dirname($dbPath);

if (!is_dir($dbDirectory)) {
    mkdir($dbDirectory, 0700, true);
}

if (!file_exists($dbPath)) {
    touch($dbPath);
    chmod($dbPath, 0600);
}

$pdo = new PDO('sqlite:' . $dbPath);
$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

// cria tabela se não existir
$pdo->exec(
    'CREATE TABLE IF NOT EXISTS tasks (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        title TEXT NOT NULL,
        done INTEGER NOT NULL DEFAULT 0,
        created_at TEXT NOT NULL
    )'
);