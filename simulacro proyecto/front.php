<?php
// front.php — muestra los artículos públicamente
$dbFile = __DIR__ . '/jocarsanews.db';
try {
    $pdo = new PDO('sqlite:' . $dbFile);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $stmt = $pdo->query("SELECT * FROM articulos ORDER BY id DESC");
    $articulos = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (Exception $e) {
    die("Error BD: " . htmlspecialchars($e->getMessage()));
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width,initial-scale=1" />
  <title>JOCARSAnews — Portada</title>
  <link rel="stylesheet" href="estilo.css">
  <style>
    main.container { max-width:960px; margin:20px auto; padding:10px; }
    article.news { background:white; padding:16px; border-radius:10px; margin-bottom:12px; box-shadow:0 3px 8px rgba(0,0,0,0.08);}
    article.news h2 { color:#0044cc; margin-bottom:6px;}
    article.news time { color:#666; font-size:0.9rem; display:block; margin-bottom:8px;}
  </style>
</head>
<body>
  <header class="site-header"><h1>JOCARSAnews</h1><nav><a href="back.php">Panel</a></nav></header>

  <main class="container">
    <?php if(empty($articulos)): ?>
      <p>No hay artículos todavía.</p>
    <?php else: ?>
      <?php foreach($articulos as $a): ?>
      <article class="news">
        <h2><?= htmlspecialchars($a['titulo']) ?></h2>
        <time><?= htmlspecialchars($a['fecha']) ?></time>
        <p><strong>Autor:</strong> <?= htmlspecialchars($a['autor']) ?></p>
        <p><?= nl2br(htmlspecialchars($a['contenido'])) ?></p>
      </article>
      <?php endforeach; ?>
    <?php endif; ?>
  </main>

  <footer class="site-footer"><p>&copy; <?=date('Y')?> JOCARSAnews</p></footer>
</body>
</html>
