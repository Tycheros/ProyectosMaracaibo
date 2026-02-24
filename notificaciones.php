<?php
// backend/api/notificaciones.php
ini_set('display_errors', 0);
header('Access-Control-Allow-Origin: *');
header('Content-Type: application/json; charset=utf-8');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') exit;

try {
    if (file_exists('../config.php')) require '../config.php';
    elseif (file_exists('../../config.php')) require '../../config.php';
    else throw new Exception("Config no encontrado");

    function getUserId() {
        $auth = $_SERVER['HTTP_AUTHORIZATION'] ?? '';
        if (preg_match('/^Bearer\s+(.*)$/i', $auth, $m)) {
            $decoded = @json_decode(base64_decode($m[1]), true);
            return $decoded['id'] ?? null;
        }
        return null;
    }

    $action = $_GET['action'] ?? '';
    $uid = getUserId();

    if (!$uid) { echo json_encode(['ok'=>false]); exit; }

    // 1. LISTAR NOTIFICACIONES
    if ($action === 'listar') {
        // Traemos las últimas 10
        $sql = "SELECT * FROM notificaciones WHERE id_usuario = ? ORDER BY fecha_creacion DESC LIMIT 10";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$uid]);
        $notis = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        // Contamos cuántas hay sin leer
        $stmtCount = $pdo->prepare("SELECT COUNT(*) FROM notificaciones WHERE id_usuario = ? AND leido = 0");
        $stmtCount->execute([$uid]);
        $sinLeer = $stmtCount->fetchColumn();

        echo json_encode(['ok' => true, 'data' => $notis, 'sin_leer' => $sinLeer]);
        exit;
    }

    // 2. MARCAR COMO LEÍDA (Una o Todas)
    if ($action === 'leer') {
        $idNoti = $_GET['id'] ?? null;
        if($idNoti) {
            $pdo->prepare("UPDATE notificaciones SET leido = 1 WHERE id_notificacion = ? AND id_usuario = ?")->execute([$idNoti, $uid]);
        } else {
            $pdo->prepare("UPDATE notificaciones SET leido = 1 WHERE id_usuario = ?")->execute([$uid]);
        }
        echo json_encode(['ok' => true]);
        exit;
    }

} catch (Exception $e) { echo json_encode(['ok'=>false]); }
?>