<?php
// backend/api/gestion_proyecto.php
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

$action = $_GET['action'] ?? '';
$uid = getUserId();

try {
    if (!$uid) throw new Exception("Acceso no autorizado");

    // --- 1. INICIAR EJECUCIÓN (Cambio de Fase) ---
    if ($action === 'iniciar') {
        $input = json_decode(file_get_contents('php://input'), true);
        $idProyecto = $input['id_proyecto'];

        // Verificar que eres el dueño y obtener título
        $stmt = $pdo->prepare("SELECT id_usuario, titulo FROM proyectos WHERE id_proyecto = ?");
        $stmt->execute([$idProyecto]);
        $p = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$p || $p['id_usuario'] != $uid) throw new Exception("No eres el dueño de este proyecto.");

        // Cambiar estado a 'en_progreso'
        $pdo->prepare("UPDATE proyectos SET estado = 'en_progreso' WHERE id_proyecto = ?")->execute([$idProyecto]);

        // NOTIFICAR A VOLUNTARIOS
        // Obtenemos los IDs de todos los voluntarios
        $stmtVol = $pdo->prepare("SELECT id_usuario FROM participaciones WHERE id_proyecto = ?");
        $stmtVol->execute([$idProyecto]);
        $voluntarios = $stmtVol->fetchAll(PDO::FETCH_COLUMN);

        $msg = "🚀 ¡El proyecto '{$p['titulo']}' ha comenzado su ejecución! Revisa el cronograma.";
        $link = "proyecto.html?id=" . $idProyecto;

        // Insertamos notificación para cada voluntario
        $sqlNotif = "INSERT INTO notificaciones (id_usuario, tipo, mensaje, enlace, fecha_creacion) VALUES (?, 'proyecto', ?, ?, NOW())";
        $stmtNotif = $pdo->prepare($sqlNotif);

        foreach ($voluntarios as $volId) {
            $stmtNotif->execute([$volId, $msg, $link]);
        }

        echo json_encode(['ok' => true, 'msg' => 'Proyecto iniciado y voluntarios notificados.']);
        exit;
    }

    // --- 2. AGREGAR ACTIVIDAD AL CRONOGRAMA ---
    if ($action === 'crear_actividad') {
        $input = json_decode(file_get_contents('php://input'), true);
        
        // Usamos 'fecha_objetivo' para coincidir con la base de datos
        $sql = "INSERT INTO actividades (id_proyecto, titulo, descripcion, fecha_objetivo, estado) VALUES (?, ?, ?, ?, 'pendiente')";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([
            $input['id_proyecto'],
            $input['titulo'],
            $input['descripcion'],
            $input['fecha']
        ]);
        echo json_encode(['ok' => true, 'msg' => 'Actividad agregada']);
        exit;
    }

    // --- 3. LISTAR ACTIVIDADES ---
    if ($action === 'listar_actividades') {
        $idProyecto = $_GET['id_proyecto'];
        // Ordenamos por fecha objetivo ascendente
        $stmt = $pdo->prepare("SELECT * FROM actividades WHERE id_proyecto = ? ORDER BY fecha_objetivo ASC");
        $stmt->execute([$idProyecto]);
        echo json_encode(['ok' => true, 'data' => $stmt->fetchAll(PDO::FETCH_ASSOC)]);
        exit;
    }

    // --- 4. FINALIZAR ACTIVIDAD ---
    if ($action === 'completar_actividad') {
        $input = json_decode(file_get_contents('php://input'), true);
        $pdo->prepare("UPDATE actividades SET estado = 'completada' WHERE id_actividad = ?")->execute([$input['id_actividad']]);
        echo json_encode(['ok' => true]);
        exit;
    }

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['ok' => false, 'msg' => $e->getMessage()]);
}
?>