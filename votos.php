<?php
// backend/api/votos.php

// Ocultar errores visuales para no romper el JSON
ini_set('display_errors', 0);
error_reporting(E_ALL);

header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');
header('Content-Type: application/json; charset=utf-8');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') exit;

try {
    // Búsqueda inteligente del config.php
    if (file_exists('../config.php')) require '../config.php';
    elseif (file_exists('../../config.php')) require '../../config.php';
    else throw new Exception("No se encuentra config.php");

    $action = $_GET['action'] ?? '';

    if ($action === 'crear') {
        $data = json_decode(file_get_contents('php://input'), true) ?? [];
        $id_proyecto = $data['id_proyecto'] ?? null;
        $tipo_voto = $data['tipo_voto'] ?? null;

        if (!$id_proyecto || !$tipo_voto) {
            throw new Exception('Parámetros de votación incompletos');
        }

        // Obtener Usuario (Método seguro sin usar getallheaders)
        $auth = $_SERVER['HTTP_AUTHORIZATION'] ?? '';
        if (!$auth && function_exists('apache_request_headers')) {
            $headers = apache_request_headers();
            $auth = $headers['Authorization'] ?? $headers['authorization'] ?? '';
        }
        
        if (!$auth || !preg_match('/^Bearer\s+(.*)$/i', $auth, $m)) {
            throw new Exception('Debes iniciar sesión para votar.');
        }

        $decoded = @json_decode(base64_decode($m[1]), true);
        $id_usuario = $decoded['id'] ?? null;

        if (!$id_usuario) {
            throw new Exception('Token de usuario inválido o expirado.');
        }

        try {
            // 1. Insertar el voto
            $stmt = $pdo->prepare("INSERT INTO votos (id_usuario, id_proyecto, tipo_voto, fecha_voto) VALUES (?, ?, ?, NOW())");
            $stmt->execute([$id_usuario, $id_proyecto, $tipo_voto]);

            // 2. CÁLCULO MANUAL DE ESTADÍSTICAS
            $stmtCalc = $pdo->prepare("SELECT 
                COUNT(*) as total, 
                SUM(CASE WHEN tipo_voto = 'positivo' THEN 1 ELSE 0 END) as positivos 
                FROM votos WHERE id_proyecto = ?");
            $stmtCalc->execute([$id_proyecto]);
            $stats = $stmtCalc->fetch(PDO::FETCH_ASSOC);
            
            $total = $stats['total'];
            $pos = $stats['positivos'] ?? 0;
            $neg = $total - $pos;
            $porcentaje = ($total > 0) ? ($pos / $total) * 100 : 0;

            // 3. Actualizar proyecto
            $stmtUpd = $pdo->prepare("UPDATE proyectos SET votos_positivos=?, votos_negativos=?, porcentaje_aprobacion=? WHERE id_proyecto=?");
            $stmtUpd->execute([$pos, $neg, $porcentaje, $id_proyecto]);

            echo json_encode([
                'ok' => true, 
                'msg' => 'Voto registrado correctamente', 
                'stats' => ['pos' => $pos, 'total' => $total]
            ]);

        } catch (PDOException $e) {
            // Error 23000 significa que rompió una regla UNIQUE (ya había votado)
            if ($e->getCode() == '23000') {
                throw new Exception('Ya has votado en este proyecto anteriormente.');
            } else {
                throw new Exception('Error SQL: ' . $e->getMessage());
            }
        }
        exit;
    }

    throw new Exception('Acción inválida');

} catch (Exception $e) {
    // Si algo falla, lo devolvemos como JSON siempre, así JS no dice "Error de conexión"
    http_response_code(500);
    echo json_encode(['ok' => false, 'msg' => $e->getMessage()]);
}