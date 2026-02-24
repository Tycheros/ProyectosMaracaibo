<?php
// backend/api/chat.php
ini_set('display_errors', 0);
header('Content-Type: application/json; charset=utf-8');
require '../config.php';

function getUserId() {
    $auth = $_SERVER['HTTP_AUTHORIZATION'] ?? '';
    if (preg_match('/^Bearer\s+(.*)$/i', $auth, $m)) {
        $decoded = @json_decode(base64_decode($m[1]), true);
        return $decoded['id'] ?? null;
    }
    return null;
}

// --- FUNCIÓN DE SEGURIDAD CRÍTICA ---
function tienePermiso($pdo, $uid, $idProyecto) {
    if (!$uid) return false;
    
    // 1. ¿Es el Dueño?
    $stmt = $pdo->prepare("SELECT id_usuario FROM proyectos WHERE id_proyecto = ?");
    $stmt->execute([$idProyecto]);
    $dueño = $stmt->fetchColumn();
    if ($dueño == $uid) return true;

    // 2. ¿Es Voluntario Registrado?
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM participaciones WHERE id_proyecto = ? AND id_usuario = ?");
    $stmt->execute([$idProyecto, $uid]);
    if ($stmt->fetchColumn() > 0) return true;

    return false;
}

$action = $_GET['action'] ?? '';
$uid = getUserId();

try {
    if (!$uid) throw new Exception("Debes iniciar sesión.");

    // --- ENVIAR MENSAJE ---
    if ($action === 'enviar') {
        $input = json_decode(file_get_contents('php://input'), true);
        $idProyecto = $input['id_proyecto'];
        $msg = trim($input['mensaje']);

        if (!tienePermiso($pdo, $uid, $idProyecto)) throw new Exception("No perteneces a este equipo.");
        if (empty($msg)) throw new Exception("Mensaje vacío.");

        $sql = "INSERT INTO chat_proyectos (id_proyecto, id_usuario, mensaje, fecha_envio) VALUES (?, ?, ?, NOW())";
        $pdo->prepare($sql)->execute([$idProyecto, $uid, $msg]);

        echo json_encode(['ok' => true]);
        exit;
    }

    // --- LEER MENSAJES ---
    if ($action === 'listar') {
        $idProyecto = $_GET['id_proyecto'];

        if (!tienePermiso($pdo, $uid, $idProyecto)) {
            // Si no tiene permiso, devolvemos array vacío (o error, según prefieras)
            // Aquí devolvemos vacío para no romper el frontend, simplemente no verá nada.
            echo json_encode(['ok' => true, 'data' => []]); 
            exit;
        }
        
        $sql = "SELECT c.*, u.nombre, u.foto_perfil, 
                (CASE WHEN c.id_usuario = ? THEN 1 ELSE 0 END) as es_mio
                FROM chat_proyectos c
                JOIN usuarios u ON c.id_usuario = u.id_usuario
                WHERE c.id_proyecto = ?
                ORDER BY c.fecha_envio ASC";
        
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$uid, $idProyecto]);
        
        echo json_encode(['ok' => true, 'data' => $stmt->fetchAll(PDO::FETCH_ASSOC)]);
        exit;
    }

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['ok' => false, 'msg' => $e->getMessage()]);
}
?>