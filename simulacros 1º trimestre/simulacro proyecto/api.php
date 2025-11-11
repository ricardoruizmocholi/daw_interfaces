<?php
header('Content-Type: application/json; charset=utf-8');
$dbFile = __DIR__ . '/jocarsanews.db';
try {
    $pdo = new PDO('sqlite:' . $dbFile);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $stmt = $pdo->query("SELECT * FROM articulos ORDER BY id DESC");
    $articulos = $stmt->fetchAll(PDO::FETCH_ASSOC);
    echo json_encode($articulos, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => $e->getMessage()]);
}
