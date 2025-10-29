<?php
// back.php — Panel de administración (crea DB si no existe)
// Config
$dbFile = __DIR__ . '/jocarsanews.db';
$adminPassword = 'admin123'; // CAMBIA esto en producción

// Inicia sesión
session_start();

// Crear DB y tabla si no existe (SQLite3 via PDO)
try {
    $pdo = new PDO('sqlite:' . $dbFile);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    $pdo->exec("CREATE TABLE IF NOT EXISTS articulos (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        titulo TEXT NOT NULL,
        autor TEXT,
        fecha TEXT,
        contenido TEXT
    )");
} catch (Exception $e) {
    die("Error BD: " . htmlspecialchars($e->getMessage()));
}

// Login simple
if (isset($_POST['login_password'])) {
    if ($_POST['login_password'] === $adminPassword) {
        $_SESSION['is_admin'] = true;
    } else {
        $login_error = "Contraseña incorrecta.";
    }
}
if (isset($_GET['logout'])) {
    unset($_SESSION['is_admin']);
    header("Location: back.php");
    exit;
}

// Solo permitir operaciones si es admin
if (!empty($_SESSION['is_admin'])) {
    // Insertar artículo
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['accion']) && $_POST['accion'] === 'insertar') {
        $titulo = trim($_POST['titulo'] ?? '');
        $autor = trim($_POST['autor'] ?? '');
        $fecha = trim($_POST['fecha'] ?? '');
        $contenido = trim($_POST['contenido'] ?? '');

        // Si no se pone fecha, guardamos la fecha actual
        if ($fecha === '') {
            $fecha = date('Y-m-d H:i:s');
        }

        if ($titulo !== '' && $contenido !== '') {
            $stmt = $pdo->prepare("INSERT INTO articulos (titulo, autor, fecha, contenido) VALUES (:titulo,:autor,:fecha,:contenido)");
            $stmt->execute([
                ':titulo' => $titulo,
                ':autor' => $autor,
                ':fecha' => $fecha,
                ':contenido' => $contenido
            ]);
            $msg = "Artículo guardado correctamente.";
        } else {
            $msg = "El título y el contenido son obligatorios.";
        }
    }

    // Borrar artículo (POST)
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['accion']) && $_POST['accion'] === 'borrar') {
        $id = (int)($_POST['id'] ?? 0);
        if ($id > 0) {
            $stmt = $pdo->prepare("DELETE FROM articulos WHERE id = :id");
            $stmt->execute([':id' => $id]);
            $msg = "Artículo eliminado.";
        }
    }

    // Leer todos los artículos
    $stmt = $pdo->query("SELECT * FROM articulos ORDER BY id DESC");
    $articulos = $stmt->fetchAll(PDO::FETCH_ASSOC);
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width,initial-scale=1" />
  <title>JOCARSAnews - Panel</title>
  <link rel="stylesheet" href="estilo.css">
  <style>
    /* Pequeños ajustes para el panel */
    .container { max-width: 1000px; margin: 20px auto; padding: 15px; }
    .grid { display: grid; grid-template-columns: 1fr 2fr; gap: 20px; align-items:start; }
    .tabla { width:100%; border-collapse: collapse; }
    .tabla th, .tabla td { padding: 8px 10px; border-bottom: 1px solid #e6eefc; text-align:left;}
    .btn-danger { background:#ff4444; color:white; border:none; padding:6px 10px; border-radius:6px; cursor:pointer;}
    .btn { background:#0044cc;color:white;border:none;padding:8px 12px;border-radius:6px;cursor:pointer;}
    .small { font-size:0.9rem; color:#333; }
    form.field { display:flex; flex-direction:column; gap:8px; }
    label{ font-weight:700;}
  </style>
</head>
<body>
  <header class="site-header">
    <h1>JOCARSAnews — Panel de administración</h1>
    <nav><a href="front.php" class="link">Ver portada</a> <?php if(!empty($_SESSION['is_admin'])): ?> | <a href="back.php?logout=1" class="link">Cerrar sesión</a><?php endif; ?></nav>
  </header>

  <main class="container">
    <?php if (empty($_SESSION['is_admin'])): ?>
      <section>
        <h2>Login administrador</h2>
        <?php if(!empty($login_error)): ?><p class="error"><?=htmlspecialchars($login_error)?></p><?php endif; ?>
        <form method="post" action="back.php">
          <label>Contraseña:</label>
          <input type="password" name="login_password" required>
          <button class="btn" type="submit">Entrar</button>
        </form>
      </section>
    <?php else: ?>
      <div class="grid">
        <section>
          <h2>Añadir artículo</h2>
          <?php if(!empty($msg)): ?><p class="small"><?=htmlspecialchars($msg)?></p><?php endif; ?>
          <form method="post" action="back.php" class="field">
            <input type="hidden" name="accion" value="insertar">
            <label for="titulo">Título *</label>
            <input id="titulo" name="titulo" type="text" required>
            <label for="autor">Autor</label>
            <input id="autor" name="autor" type="text">
            <label for="fecha">Fecha (YYYY-MM-DD HH:MM:SS) — opcional</label>
            <input id="fecha" name="fecha" type="text" placeholder="<?=date('Y-m-d H:i:s')?>">
            <label for="contenido">Contenido *</label>
            <textarea id="contenido" name="contenido" rows="8" style="resize:vertical;"></textarea>
            <button class="btn" type="submit">Guardar artículo</button>
          </form>
        </section>

        <section>
          <h2>Artículos en la base de datos</h2>
          <?php if (empty($articulos)): ?>
            <p class="small">No hay artículos aún.</p>
          <?php else: ?>
            <table class="tabla">
              <thead>
                <tr><th>ID</th><th>Título</th><th>Autor</th><th>Fecha</th><th></th></tr>
              </thead>
              <tbody>
                <?php foreach($articulos as $a): ?>
                <tr>
                  <td><?= (int)$a['id'] ?></td>
                  <td><?= htmlspecialchars($a['titulo']) ?></td>
                  <td><?= htmlspecialchars($a['autor']) ?></td>
                  <td><?= htmlspecialchars($a['fecha']) ?></td>
                  <td>
                    <form method="post" action="back.php" style="display:inline;">
                      <input type="hidden" name="accion" value="borrar">
                      <input type="hidden" name="id" value="<?= (int)$a['id'] ?>">
                      <button class="btn-danger" type="submit" onclick="return confirm('¿Borrar artículo #<?= (int)$a['id'] ?>?')">❌</button>
                    </form>
                  </td>
                </tr>
                <?php endforeach; ?>
              </tbody>
            </table>
          <?php endif; ?>
        </section>
      </div>
    <?php endif; ?>
  </main>

  <footer class="site-footer">
    <p>&copy; <?=date('Y')?> — JOCARSAnews</p>
  </footer>
</body>
</html>
