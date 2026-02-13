diff --git a/index.php b/index.php
index 088951629c0a28691830b5a379b02808cf26c205..052fa1adef70a0f4a6ff691a58268454542ac459 100755
--- a/index.php
+++ b/index.php
@@ -1,390 +1,464 @@
-<?php
-declare(strict_types=1);
-require __DIR__ . "/db.php";
-
-$action = $_GET["action"] ?? "list";
-
-function redirect_home(): void {
-  header("Location: index.php");
-  exit;
-}
-
-if ($action === "add" && $_SERVER["REQUEST_METHOD"] === "POST") {
-  $title = trim($_POST["title"] ?? "");
-  if ($title !== "") {
-    $stmt = $pdo->prepare("INSERT INTO tasks (title, done, created_at) VALUES (?, 0, ?)");
-    $stmt->execute([$title, date("c")]);
-  }
-  redirect_home();
-}
-
-if ($action === "toggle") {
-  $id = (int)($_GET["id"] ?? 0);
-  if ($id > 0) {
-    $stmt = $pdo->prepare("UPDATE tasks SET done = CASE done WHEN 1 THEN 0 ELSE 1 END WHERE id = ?");
-    $stmt->execute([$id]);
-  }
-  redirect_home();
-}
-
-if ($action === "delete") {
-  $id = (int)($_GET["id"] ?? 0);
-  if ($id > 0) {
-    $stmt = $pdo->prepare("DELETE FROM tasks WHERE id = ?");
-    $stmt->execute([$id]);
-  }
-  redirect_home();
-}
-
-if ($action === "edit" && $_SERVER["REQUEST_METHOD"] === "POST") {
-  $id = (int)($_POST["id"] ?? 0);
-  $title = trim($_POST["title"] ?? "");
-  if ($id > 0 && $title !== "") {
-    $stmt = $pdo->prepare("UPDATE tasks SET title = ? WHERE id = ?");
-    $stmt->execute([$title, $id]);
-  }
-  redirect_home();
-}
-
-// LISTAGEM
-$tasks = $pdo->query("SELECT * FROM tasks ORDER BY done ASC, id DESC")->fetchAll(PDO::FETCH_ASSOC);
-
-// caso o usuário clique em "Editar", pegamos a task
-$editId = (int)($_GET["edit_id"] ?? 0);
-$taskToEdit = null;
-if ($editId > 0) {
-  $stmt = $pdo->prepare("SELECT * FROM tasks WHERE id = ?");
-  $stmt->execute([$editId]);
-  $taskToEdit = $stmt->fetch(PDO::FETCH_ASSOC) ?: null;
-}
-?>
-<!doctype html>
-<html lang="pt-BR">
-<head>
-  <meta charset="utf-8" />
-  <meta name="viewport" content="width=device-width, initial-scale=1" />
-  <title>To-Do PHP (SQLite)</title>
-  <style>
-    * {
-      margin: 0;
-      padding: 0;
-      box-sizing: border-box;
-    }
-
-    body {
-      font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
-      background: linear-gradient(135deg, #0f0c29, #302b63, #24243e);
-      min-height: 100vh;
-      padding: 40px 20px;
-      color: #fff;
-    }
-
-    .container {
-      max-width: 800px;
-      margin: 0 auto;
-    }
-
-    h1 {
-      text-align: center;
-      font-size: 3em;
-      margin-bottom: 10px;
-      background: linear-gradient(120deg, #00d4ff, #7b2fff, #ff006e);
-      -webkit-background-clip: text;
-      -webkit-text-fill-color: transparent;
-      background-clip: text;
-      animation: fade-in 0.8s ease-out;
-      font-weight: 700;
-      letter-spacing: 1px;
-    }
-
-    .subtitle {
-      text-align: center;
-      color: rgba(255, 255, 255, 0.7);
-      margin-bottom: 40px;
-      font-size: 0.95em;
-      animation: fade-in 1s ease-out;
-    }
-
-    .card {
-      background: rgba(255, 255, 255, 0.08);
-      backdrop-filter: blur(10px);
-      border: 1px solid rgba(255, 255, 255, 0.15);
-      border-radius: 20px;
-      padding: 30px;
-      margin-bottom: 25px;
-      box-shadow: 0 20px 60px rgba(0, 0, 0, 0.3);
-      animation: slide-up 0.6s ease-out;
-      transition: all 0.3s ease;
-    }
-
-    .card:hover {
-      border-color: rgba(255, 255, 255, 0.25);
-      box-shadow: 0 25px 70px rgba(0, 0, 0, 0.4);
-    }
-
-    .card h3 {
-      font-size: 1.3em;
-      margin-bottom: 20px;
-      color: #00d4ff;
-      text-transform: uppercase;
-      letter-spacing: 0.5px;
-    }
-
-    form {
-      display: flex;
-      gap: 12px;
-      flex-wrap: wrap;
-    }
-
-    input[type="text"] {
-      flex: 1;
-      min-width: 200px;
-      padding: 14px 18px;
-      border-radius: 12px;
-      border: 2px solid rgba(0, 212, 255, 0.3);
-      background: rgba(255, 255, 255, 0.05);
-      color: #fff;
-      font-size: 1em;
-      transition: all 0.3s ease;
-      font-family: inherit;
-    }
-
-    input[type="text"]::placeholder {
-      color: rgba(255, 255, 255, 0.5);
-    }
-
-    input[type="text"]:focus {
-      outline: none;
-      border-color: #00d4ff;
-      background: rgba(255, 255, 255, 0.1);
-      box-shadow: 0 0 20px rgba(0, 212, 255, 0.3);
-    }
-
-    button {
-      padding: 14px 32px;
-      border: 2px solid transparent;
-      border-radius: 12px;
-      cursor: pointer;
-      font-weight: 600;
-      font-size: 1em;
-      transition: all 0.3s ease;
-      background: linear-gradient(120deg, #00d4ff, #7b2fff);
-      color: #fff;
-      text-transform: uppercase;
-      letter-spacing: 0.5px;
-      box-shadow: 0 8px 25px rgba(0, 212, 255, 0.3);
-      font-family: inherit;
-    }
-
-    button:hover {
-      transform: translateY(-2px);
-      box-shadow: 0 12px 35px rgba(0, 212, 255, 0.5);
-    }
-
-    button:active {
-      transform: translateY(0);
-    }
-
-    .cancel-link {
-      align-self: center;
-      color: #00d4ff;
-      text-decoration: none;
-      font-weight: 600;
-      transition: all 0.3s ease;
-      padding: 14px 20px;
-      border-radius: 12px;
-    }
-
-    .cancel-link:hover {
-      color: #ff006e;
-      background: rgba(0, 212, 255, 0.1);
-    }
-
-    ul {
-      list-style: none;
-      padding: 0;
-    }
-
-    li {
-      display: flex;
-      justify-content: space-between;
-      align-items: center;
-      padding: 16px 0;
-      border-bottom: 1px solid rgba(255, 255, 255, 0.1);
-      transition: all 0.3s ease;
-    }
-
-    li:last-child {
-      border-bottom: none;
-    }
-
-    li:hover {
-      background: rgba(0, 212, 255, 0.05);
-      margin: 0 -10px;
-      padding-left: 10px;
-      padding-right: 10px;
-      border-radius: 10px;
-    }
-
-    li > div:first-child {
-      flex: 1;
-    }
-
-    .done {
-      text-decoration: line-through;
-      opacity: 0.55;
-      color: #888;
-    }
-
-    .task-title {
-      font-size: 1.05em;
-      font-weight: 500;
-      margin-bottom: 6px;
-    }
-
-    .small {
-      font-size: 0.85em;
-      color: rgba(255, 255, 255, 0.6);
-    }
-
-    .actions {
-      display: flex;
-      gap: 12px;
-      flex-wrap: wrap;
-      justify-content: flex-end;
-    }
-
-    .actions a {
-      text-decoration: none;
-      color: #fff;
-      background: rgba(0, 212, 255, 0.15);
-      padding: 8px 14px;
-      border-radius: 8px;
-      font-size: 0.9em;
-      transition: all 0.3s ease;
-      border: 1px solid rgba(0, 212, 255, 0.3);
-      font-weight: 500;
-    }
-
-    .actions a:hover {
-      background: rgba(0, 212, 255, 0.3);
-      border-color: #00d4ff;
-      transform: translateY(-1px);
-      box-shadow: 0 6px 20px rgba(0, 212, 255, 0.2);
-    }
-
-    .empty-state {
-      text-align: center;
-      color: rgba(255, 255, 255, 0.6);
-      font-size: 1.1em;
-      padding: 20px;
-    }
-
-    @keyframes fade-in {
-      from {
-        opacity: 0;
-      }
-      to {
-        opacity: 1;
-      }
-    }
-
-    @keyframes slide-up {
-      from {
-        opacity: 0;
-        transform: translateY(30px);
-      }
-      to {
-        opacity: 1;
-        transform: translateY(0);
-      }
-    }
-
-    @media (max-width: 600px) {
-      h1 {
-        font-size: 2em;
-      }
-
-      .card {
-        padding: 20px;
-      }
-
-      form {
-        gap: 10px;
-      }
-
-      input[type="text"] {
-        min-width: 100%;
-      }
-
-      button {
-        min-width: 100%;
-      }
-
-      li {
-        flex-direction: column;
-        align-items: flex-start;
-        gap: 12px;
-      }
-
-      .actions {
-        width: 100%;
-      }
-    }
-  </style>
-</head>
-<body>
-
-  <div class="container">
-    <h1>🚀 TO-DO APP</h1>
-    <p class="subtitle">Suas tarefas, seu controle total</p>
-
-  <div class="card">
-    <?php if ($taskToEdit): ?>
-      <h3>✏️ Editar tarefa</h3>
-      <form method="post" action="index.php?action=edit">
-        <input type="hidden" name="id" value="<?= (int)$taskToEdit["id"] ?>">
-        <input type="text" name="title" value="<?= htmlspecialchars($taskToEdit["title"]) ?>" required>
-        <button type="submit">💾 Salvar</button>
-        <a href="index.php" class="cancel-link">✕ Cancelar</a>
-      </form>
-    <?php else: ?>
-      <h3>➕ Adicionar nova tarefa</h3>
-      <form method="post" action="index.php?action=add">
-        <input type="text" name="title" placeholder="Ex: Estudar PHP + PDO" required>
-        <button type="submit">✨ Adicionar</button>
-      </form>
-    <?php endif; ?>
-  </div>
-
-  <div class="card">
-    <h3>📋 Minhas Tarefas</h3>
-    <ul>
-      <?php foreach ($tasks as $t): ?>
-        <li>
-          <div>
-            <div class="task-title <?= $t["done"] ? "done" : "" ?>">
-              <?= $t["done"] ? "✅ " : "⭕ " ?><?= htmlspecialchars($t["title"]) ?>
-            </div>
-            <div class="small">📅 <?= htmlspecialchars($t["created_at"]) ?></div>
-          </div>
-
-          <div class="actions">
-            <a href="index.php?action=toggle&id=<?= (int)$t["id"] ?>">
-              <?= $t["done"] ? "↩️ Reabrir" : "✅ Feito" ?>
-            </a>
-            <a href="index.php?edit_id=<?= (int)$t["id"] ?>">✏️ Editar</a>
-            <a href="index.php?action=delete&id=<?= (int)$t["id"] ?>" onclick="return confirm('Tem certeza que deseja excluir esta tarefa?')">🗑️ Remover</a>
-          </div>
-        </li>
-      <?php endforeach; ?>
-      <?php if (count($tasks) === 0): ?>
-        <li><div class="empty-state">🎉 Nenhuma tarefa! Você está livre!</div></li>
-      <?php endif; ?>
-    </ul>
-  </div>
-
-  </div>
-
-</body>
-</html>
+<?php
+declare(strict_types=1);
+
+$cookieSecure = filter_var(getenv('SESSION_COOKIE_SECURE') ?: '0', FILTER_VALIDATE_BOOL);
+session_set_cookie_params([
+  'httponly' => true,
+  'samesite' => 'Lax',
+  'secure' => $cookieSecure,
+]);
+session_start();
+
+require __DIR__ . '/db.php';
+
+header('X-Frame-Options: DENY');
+header('X-Content-Type-Options: nosniff');
+header('Referrer-Policy: no-referrer');
+
+$action = $_GET['action'] ?? 'list';
+
+if (empty($_SESSION['csrf_token'])) {
+  $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
+}
+$csrfToken = $_SESSION['csrf_token'];
+
+function redirect_home(): void {
+  header('Location: index.php');
+  exit;
+}
+
+function validate_csrf_token(string $token): bool {
+  return hash_equals($_SESSION['csrf_token'] ?? '', $token);
+}
+
+if ($action === 'add' && $_SERVER['REQUEST_METHOD'] === 'POST') {
+  if (!validate_csrf_token($_POST['csrf_token'] ?? '')) {
+    http_response_code(403);
+    exit('CSRF token inválido.');
+  }
+
+  $title = trim($_POST['title'] ?? '');
+  if ($title !== '') {
+    $stmt = $pdo->prepare('INSERT INTO tasks (title, done, created_at) VALUES (?, 0, ?)');
+    $stmt->execute([$title, date('c')]);
+  }
+  redirect_home();
+}
+
+if ($action === 'toggle' && $_SERVER['REQUEST_METHOD'] === 'POST') {
+  if (!validate_csrf_token($_POST['csrf_token'] ?? '')) {
+    http_response_code(403);
+    exit('CSRF token inválido.');
+  }
+
+  $id = (int)($_POST['id'] ?? 0);
+  if ($id > 0) {
+    $stmt = $pdo->prepare('UPDATE tasks SET done = CASE done WHEN 1 THEN 0 ELSE 1 END WHERE id = ?');
+    $stmt->execute([$id]);
+  }
+  redirect_home();
+}
+
+if ($action === 'delete' && $_SERVER['REQUEST_METHOD'] === 'POST') {
+  if (!validate_csrf_token($_POST['csrf_token'] ?? '')) {
+    http_response_code(403);
+    exit('CSRF token inválido.');
+  }
+
+  $id = (int)($_POST['id'] ?? 0);
+  if ($id > 0) {
+    $stmt = $pdo->prepare('DELETE FROM tasks WHERE id = ?');
+    $stmt->execute([$id]);
+  }
+  redirect_home();
+}
+
+if ($action === 'edit' && $_SERVER['REQUEST_METHOD'] === 'POST') {
+  if (!validate_csrf_token($_POST['csrf_token'] ?? '')) {
+    http_response_code(403);
+    exit('CSRF token inválido.');
+  }
+
+  $id = (int)($_POST['id'] ?? 0);
+  $title = trim($_POST['title'] ?? '');
+  if ($id > 0 && $title !== '') {
+    $stmt = $pdo->prepare('UPDATE tasks SET title = ? WHERE id = ?');
+    $stmt->execute([$title, $id]);
+  }
+  redirect_home();
+}
+
+$tasks = $pdo->query('SELECT * FROM tasks ORDER BY done ASC, id DESC')->fetchAll(PDO::FETCH_ASSOC);
+
+$editId = (int)($_GET['edit_id'] ?? 0);
+$taskToEdit = null;
+if ($editId > 0) {
+  $stmt = $pdo->prepare('SELECT * FROM tasks WHERE id = ?');
+  $stmt->execute([$editId]);
+  $taskToEdit = $stmt->fetch(PDO::FETCH_ASSOC) ?: null;
+}
+?>
+<!doctype html>
+<html lang="pt-BR">
+<head>
+  <meta charset="utf-8" />
+  <meta name="viewport" content="width=device-width, initial-scale=1" />
+  <title>To-Do PHP (SQLite)</title>
+  <style>
+    * {
+      margin: 0;
+      padding: 0;
+      box-sizing: border-box;
+    }
+
+    body {
+      font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
+      background: linear-gradient(135deg, #0f0c29, #302b63, #24243e);
+      min-height: 100vh;
+      padding: 40px 20px;
+      color: #fff;
+    }
+
+    .container {
+      max-width: 800px;
+      margin: 0 auto;
+    }
+
+    h1 {
+      text-align: center;
+      font-size: 3em;
+      margin-bottom: 10px;
+      background: linear-gradient(120deg, #00d4ff, #7b2fff, #ff006e);
+      -webkit-background-clip: text;
+      -webkit-text-fill-color: transparent;
+      background-clip: text;
+      animation: fade-in 0.8s ease-out;
+      font-weight: 700;
+      letter-spacing: 1px;
+    }
+
+    .subtitle {
+      text-align: center;
+      color: rgba(255, 255, 255, 0.7);
+      margin-bottom: 40px;
+      font-size: 0.95em;
+      animation: fade-in 1s ease-out;
+    }
+
+    .card {
+      background: rgba(255, 255, 255, 0.08);
+      backdrop-filter: blur(10px);
+      border: 1px solid rgba(255, 255, 255, 0.15);
+      border-radius: 20px;
+      padding: 30px;
+      margin-bottom: 25px;
+      box-shadow: 0 20px 60px rgba(0, 0, 0, 0.3);
+      animation: slide-up 0.6s ease-out;
+      transition: all 0.3s ease;
+    }
+
+    .card:hover {
+      border-color: rgba(255, 255, 255, 0.25);
+      box-shadow: 0 25px 70px rgba(0, 0, 0, 0.4);
+    }
+
+    .card h3 {
+      font-size: 1.3em;
+      margin-bottom: 20px;
+      color: #00d4ff;
+      text-transform: uppercase;
+      letter-spacing: 0.5px;
+    }
+
+    form {
+      display: flex;
+      gap: 12px;
+      flex-wrap: wrap;
+    }
+
+    input[type="text"] {
+      flex: 1;
+      min-width: 200px;
+      padding: 14px 18px;
+      border-radius: 12px;
+      border: 2px solid rgba(0, 212, 255, 0.3);
+      background: rgba(255, 255, 255, 0.05);
+      color: #fff;
+      font-size: 1em;
+      transition: all 0.3s ease;
+      font-family: inherit;
+    }
+
+    input[type="text"]::placeholder {
+      color: rgba(255, 255, 255, 0.5);
+    }
+
+    input[type="text"]:focus {
+      outline: none;
+      border-color: #00d4ff;
+      background: rgba(255, 255, 255, 0.1);
+      box-shadow: 0 0 20px rgba(0, 212, 255, 0.3);
+    }
+
+    button {
+      padding: 14px 32px;
+      border: 2px solid transparent;
+      border-radius: 12px;
+      cursor: pointer;
+      font-weight: 600;
+      font-size: 1em;
+      transition: all 0.3s ease;
+      background: linear-gradient(120deg, #00d4ff, #7b2fff);
+      color: #fff;
+      text-transform: uppercase;
+      letter-spacing: 0.5px;
+      box-shadow: 0 8px 25px rgba(0, 212, 255, 0.3);
+      font-family: inherit;
+    }
+
+    button:hover {
+      transform: translateY(-2px);
+      box-shadow: 0 12px 35px rgba(0, 212, 255, 0.5);
+    }
+
+    button:active {
+      transform: translateY(0);
+    }
+
+    .cancel-link {
+      align-self: center;
+      color: #00d4ff;
+      text-decoration: none;
+      font-weight: 600;
+      transition: all 0.3s ease;
+      padding: 14px 20px;
+      border-radius: 12px;
+    }
+
+    .cancel-link:hover {
+      color: #ff006e;
+      background: rgba(0, 212, 255, 0.1);
+    }
+
+    ul {
+      list-style: none;
+      padding: 0;
+    }
+
+    li {
+      display: flex;
+      justify-content: space-between;
+      align-items: center;
+      padding: 16px 0;
+      border-bottom: 1px solid rgba(255, 255, 255, 0.1);
+      transition: all 0.3s ease;
+    }
+
+    li:last-child {
+      border-bottom: none;
+    }
+
+    li:hover {
+      background: rgba(0, 212, 255, 0.05);
+      margin: 0 -10px;
+      padding-left: 10px;
+      padding-right: 10px;
+      border-radius: 10px;
+    }
+
+    li > div:first-child {
+      flex: 1;
+    }
+
+    .done {
+      text-decoration: line-through;
+      opacity: 0.55;
+      color: #888;
+    }
+
+    .task-title {
+      font-size: 1.05em;
+      font-weight: 500;
+      margin-bottom: 6px;
+    }
+
+    .small {
+      font-size: 0.85em;
+      color: rgba(255, 255, 255, 0.6);
+    }
+
+    .actions {
+      display: flex;
+      gap: 12px;
+      flex-wrap: wrap;
+      justify-content: flex-end;
+    }
+
+    .inline-action-form {
+      margin: 0;
+    }
+
+    .inline-action-form button {
+      text-decoration: none;
+      color: #fff;
+      background: rgba(0, 212, 255, 0.15);
+      padding: 8px 14px;
+      border-radius: 8px;
+      font-size: 0.9em;
+      transition: all 0.3s ease;
+      border: 1px solid rgba(0, 212, 255, 0.3);
+      font-weight: 500;
+      text-transform: none;
+      box-shadow: none;
+      min-width: auto;
+    }
+
+    .inline-action-form button:hover {
+      background: rgba(0, 212, 255, 0.3);
+      border-color: #00d4ff;
+      transform: translateY(-1px);
+      box-shadow: 0 6px 20px rgba(0, 212, 255, 0.2);
+    }
+
+    .actions a {
+      text-decoration: none;
+      color: #fff;
+      background: rgba(0, 212, 255, 0.15);
+      padding: 8px 14px;
+      border-radius: 8px;
+      font-size: 0.9em;
+      transition: all 0.3s ease;
+      border: 1px solid rgba(0, 212, 255, 0.3);
+      font-weight: 500;
+    }
+
+    .actions a:hover {
+      background: rgba(0, 212, 255, 0.3);
+      border-color: #00d4ff;
+      transform: translateY(-1px);
+      box-shadow: 0 6px 20px rgba(0, 212, 255, 0.2);
+    }
+
+    .empty-state {
+      text-align: center;
+      color: rgba(255, 255, 255, 0.6);
+      font-size: 1.1em;
+      padding: 20px;
+    }
+
+    @keyframes fade-in {
+      from {
+        opacity: 0;
+      }
+      to {
+        opacity: 1;
+      }
+    }
+
+    @keyframes slide-up {
+      from {
+        opacity: 0;
+        transform: translateY(30px);
+      }
+      to {
+        opacity: 1;
+        transform: translateY(0);
+      }
+    }
+
+    @media (max-width: 600px) {
+      h1 {
+        font-size: 2em;
+      }
+
+      .card {
+        padding: 20px;
+      }
+
+      form {
+        gap: 10px;
+      }
+
+      input[type="text"] {
+        min-width: 100%;
+      }
+
+      button {
+        min-width: 100%;
+      }
+
+      li {
+        flex-direction: column;
+        align-items: flex-start;
+        gap: 12px;
+      }
+
+      .actions {
+        width: 100%;
+      }
+    }
+  </style>
+</head>
+<body>
+
+  <div class="container">
+    <h1>🚀 TO-DO APP</h1>
+    <p class="subtitle">Suas tarefas, seu controle total</p>
+
+  <div class="card">
+    <?php if ($taskToEdit): ?>
+      <h3>✏️ Editar tarefa</h3>
+      <form method="post" action="index.php?action=edit">
+        <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrfToken) ?>">
+        <input type="hidden" name="id" value="<?= (int)$taskToEdit['id'] ?>">
+        <input type="text" name="title" value="<?= htmlspecialchars($taskToEdit['title']) ?>" required>
+        <button type="submit">💾 Salvar</button>
+        <a href="index.php" class="cancel-link">✕ Cancelar</a>
+      </form>
+    <?php else: ?>
+      <h3>➕ Adicionar nova tarefa</h3>
+      <form method="post" action="index.php?action=add">
+        <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrfToken) ?>">
+        <input type="text" name="title" placeholder="Ex: Estudar PHP + PDO" required>
+        <button type="submit">✨ Adicionar</button>
+      </form>
+    <?php endif; ?>
+  </div>
+
+  <div class="card">
+    <h3>📋 Minhas Tarefas</h3>
+    <ul>
+      <?php foreach ($tasks as $t): ?>
+        <li>
+          <div>
+            <div class="task-title <?= $t['done'] ? 'done' : '' ?>">
+              <?= $t['done'] ? '✅ ' : '⭕ ' ?><?= htmlspecialchars($t['title']) ?>
+            </div>
+            <div class="small">📅 <?= htmlspecialchars($t['created_at']) ?></div>
+          </div>
+
+          <div class="actions">
+            <form class="inline-action-form" method="post" action="index.php?action=toggle">
+              <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrfToken) ?>">
+              <input type="hidden" name="id" value="<?= (int)$t['id'] ?>">
+              <button type="submit"><?= $t['done'] ? '↩️ Reabrir' : '✅ Feito' ?></button>
+            </form>
+            <a href="index.php?edit_id=<?= (int)$t['id'] ?>">✏️ Editar</a>
+            <form class="inline-action-form" method="post" action="index.php?action=delete" onsubmit="return confirm('Tem certeza que deseja excluir esta tarefa?')">
+              <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrfToken) ?>">
+              <input type="hidden" name="id" value="<?= (int)$t['id'] ?>">
+              <button type="submit">🗑️ Remover</button>
+            </form>
+          </div>
+        </li>
+      <?php endforeach; ?>
+      <?php if (count($tasks) === 0): ?>
+        <li><div class="empty-state">🎉 Nenhuma tarefa! Você está livre!</div></li>
+      <?php endif; ?>
+    </ul>
+  </div>
+
+  </div>
+
+</body>
+</html>
