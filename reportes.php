<?php
// backend/api/reportes.php
ini_set('display_errors', 0);
error_reporting(E_ALL);

header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');
header('Content-Type: application/json; charset=utf-8');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') exit;

try {
    if (file_exists('../config.php')) require '../config.php';
    elseif (file_exists('../../config.php')) require '../../config.php';
    else throw new Exception("No se encuentra config.php");

    $action = $_GET['action'] ?? '';
    
    // --- CONFIGURACIÓN DE SUSPENSIÓN ---
    $LIMITE_REPORTES = 5; // Si un proyecto recibe 5 reportes, se oculta.

    function getUserId() {
        $auth = $_SERVER['HTTP_AUTHORIZATION'] ?? '';
        if (preg_match('/^Bearer\s+(.*)$/i', $auth, $m)) {
            $decoded = @json_decode(base64_decode($m[1]), true);
            return $decoded['id'] ?? null;
        }
        return null;
    }

    // --- 1. CREAR REPORTE ---
    if ($action === 'crear') {
        $uid = getUserId();
        if (!$uid) throw new Exception("Debes iniciar sesión para reportar.");

        $input = json_decode(file_get_contents('php://input'), true);
        
        $tipoObj = $input['tipo_objeto'] ?? ''; // 'proyecto', 'usuario', 'donacion'
        $idObj   = $input['id_objeto'] ?? 0;
        $motivo  = $input['motivo'] ?? 'otro';
        $desc    = $input['descripcion'] ?? '';

        if (!$idObj || !$tipoObj) throw new Exception("Datos incompletos.");

        // Definir en qué columna guardar según el tipo
        $columnaID = '';
        $tablaObjetivo = '';
        $columnaEstado = 'estado';
        $nuevoEstado = 'revision'; // Estado al suspender
        $columnaPK = ''; // Clave primaria de la tabla objetivo

        if ($tipoObj === 'proyecto') {
            $columnaID = 'id_proyecto';
            $tablaObjetivo = 'proyectos';
            $columnaPK = 'id_proyecto';
            $sql = "INSERT INTO reportes (id_usuario, id_proyecto, tipo_reporte, descripcion, fecha_reporte) VALUES (?, ?, ?, ?, NOW())";
        } 
        elseif ($tipoObj === 'usuario') {
            $columnaID = 'id_usuario_reportado';
            $tablaObjetivo = 'usuarios';
            $columnaPK = 'id_usuario';
            $nuevoEstado = 'suspendido'; // Los usuarios se 'suspenden'
            $sql = "INSERT INTO reportes (id_usuario, id_usuario_reportado, tipo_reporte, descripcion, fecha_reporte) VALUES (?, ?, ?, ?, NOW())";
        }
        elseif ($tipoObj === 'donacion') {
            $columnaID = 'id_donacion';
            $tablaObjetivo = 'donaciones';
            $columnaPK = 'id_donacion';
            $sql = "INSERT INTO reportes (id_usuario, id_donacion, tipo_reporte, descripcion, fecha_reporte) VALUES (?, ?, ?, ?, NOW())";
        } else {
            throw new Exception("Tipo no válido.");
        }

        // 1. Guardar el reporte
        $pdo->prepare($sql)->execute([$uid, $idObj, $motivo, $desc]);

        // 2. Verificar suspensión automática (Circuit Breaker)
        // Contamos cuántos reportes tiene este objeto en total
        $stmtCount = $pdo->prepare("SELECT COUNT(*) FROM reportes WHERE $columnaID = ?");
        $stmtCount->execute([$idObj]);
        $total = $stmtCount->fetchColumn();

        $fueSuspendido = false;
        if ($total >= $LIMITE_REPORTES) {
            // ¡Alerta! Muchos reportes. Cambiar estado automáticamente.
            $sqlSuspender = "UPDATE $tablaObjetivo SET $columnaEstado = ? WHERE $columnaPK = ?";
            $pdo->prepare($sqlSuspender)->execute([$nuevoEstado, $idObj]);
            $fueSuspendido = true;
        }

        $msg = "Reporte enviado correctamente.";
        if ($fueSuspendido) {
            $msg .= " Debido al alto volumen de reportes, el contenido ha sido puesto en revisión temporalmente.";
        }

        echo json_encode(['ok' => true, 'msg' => $msg]);
        exit;
    }

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['ok' => false, 'msg' => $e->getMessage()]);
}
?>