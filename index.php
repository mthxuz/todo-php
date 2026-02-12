<?php
declare(strict_types=1);
require __DIR__ . "/db.php";

$action = $_GET["action"] ?? "list";

function redirect_home(): void {
  header("Location: index.php");
  exit;
}

if ($action === "add" && $_SERVER["REQUEST_METHOD"] === "POST") {
  $title = trim($_POST["title"] ?? "");
  if ($title !== "") {
    $stmt = $pdo->prepare("INSERT INTO tasks (title, done, created_at) VALUES (?, 0, ?)");
    $stmt->execute([$title, date("c")]);
  }
  redirect_home();
}

if ($action === "toggle") {
  $id = (int)($_GET["id"] ?? 0);
  if ($id > 0) {
    $stmt = $pdo->prepare("UPDATE tasks SET done = CASE done WHEN 1 THEN 0 ELSE 1 END WHERE id = ?");
    $stmt->execute([$id]);
  }
  redirect_home();
}

if ($action === "delete") {
  $id = (int)($_GET["id"] ?? 0);
  if ($id > 0) {
    $stmt = $pdo->prepare("DELETE FROM tasks WHERE id = ?");
    $stmt->execute([$id]);
  }
  redirect_home();
}

if ($action === "edit" && $_SERVER["REQUEST_METHOD"] === "POST") {
  $id = (int)($_POST["id"] ?? 0);
  $title = trim($_POST["title"] ?? "");
  if ($id > 0 && $title !== "") {
    $stmt = $pdo->prepare("UPDATE tasks SET title = ? WHERE id = ?");
    $stmt->execute([$title, $id]);
  }
  redirect_home();
}

// LISTAGEM
$tasks = $pdo->query("SELECT * FROM tasks ORDER BY done ASC, id DESC")->fetchAll(PDO::FETCH_ASSOC);

// caso o usuário clique em "Editar", pegamos a task
$editId = (int)($_GET["edit_id"] ?? 0);
$taskToEdit = null;
if ($editId > 0) {
  $stmt = $pdo->prepare("SELECT * FROM tasks WHERE id = ?");
  $stmt->execute([$editId]);
  $taskToEdit = $stmt->fetch(PDO::FETCH_ASSOC) ?: null;
}
?>
<!doctype html>
<html lang="pt-BR">
<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>To-Do PHP (SQLite)</title>
  <style>
    body { font-family: Arial, sans-serif; max-width: 760px; margin: 40px auto; padding: 0 16px; }
    .card { border: 1px solid #ddd; border-radius: 10px; padding: 16px; margin-bottom: 16px; }
    form { display: flex; gap: 8px; }
    input[type="text"] { flex: 1; padding: 10px; border-radius: 8px; border: 1px solid #ccc; }
    button { padding: 10px 12px; border: 0; border-radius: 8px; cursor: pointer; }
    ul { list-style: none; padding: 0; }
    li { display: flex; justify-content: space-between; align-items: center; padding: 10px 0; border-bottom: 1px solid #eee; }
    .done { text-decoration: line-through; opacity: 0.65; }
    .actions a { margin-left: 10px; text-decoration: none; }
    .small { font-size: 12px; color: #666; }
  </style>
</head>
<body>

  <h1>✅ To-Do em PHP + SQLite</h1>

  <div class="card">
    <?php if ($taskToEdit): ?>
      <h3>Editar tarefa</h3>
      <form method="post" action="index.php?action=edit">
        <input type="hidden" name="id" value="<?= (int)$taskToEdit["id"] ?>">
        <input type="text" name="title" value="<?= htmlspecialchars($taskToEdit["title"]) ?>" required>
        <button type="submit">Salvar</button>
        <a href="index.php" style="align-self:center;">Cancelar</a>
      </form>
    <?php else: ?>
      <h3>Adicionar tarefa</h3>
      <form method="post" action="index.php?action=add">
        <input type="text" name="title" placeholder="Ex: Estudar PHP + PDO" required>
        <button type="submit">Adicionar</button>
      </form>
    <?php endif; ?>
  </div>

  <div class="card">
    <h3>Minhas tarefas</h3>
    <ul>
      <?php foreach ($tasks as $t): ?>
        <li>
          <div>
            <div class="<?= $t["done"] ? "done" : "" ?>">
              <?= htmlspecialchars($t["title"]) ?>
            </div>
            <div class="small">Criada em: <?= htmlspecialchars($t["created_at"]) ?></div>
          </div>

          <div class="actions">
            <a href="index.php?action=toggle&id=<?= (int)$t["id"] ?>">
              <?= $t["done"] ? "↩️ Reabrir" : "✅ Concluir" ?>
            </a>
            <a href="index.php?edit_id=<?= (int)$t["id"] ?>">✏️ Editar</a>
            <a href="index.php?action=delete&id=<?= (int)$t["id"] ?>" onclick="return confirm('Excluir esta tarefa?')">🗑️ Excluir</a>
          </div>
        </li>
      <?php endforeach; ?>
      <?php if (count($tasks) === 0): ?>
        <li>Nenhuma tarefa ainda 🙂</li>
      <?php endif; ?>
    </ul>
  </div>

</body>
</html>
