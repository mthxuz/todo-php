<?php
// db.php
declare(strict_types=1);

$dbFile = __DIR__ . "/todo.sqlite";
$pdo = new PDO("sqlite:" . $dbFile);
$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

// cria tabela se não existir
$pdo->exec("
  CREATE TABLE IF NOT EXISTS tasks (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    title TEXT NOT NULL,
    done INTEGER NOT NULL DEFAULT 0,
    created_at TEXT NOT NULL
  );
");
