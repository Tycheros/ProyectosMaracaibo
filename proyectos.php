<?php
// backend/api/proyectos.php
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

    function getUserId() {
        $auth = $_SERVER['HTTP_AUTHORIZATION'] ?? '';
        if (preg_match('/^Bearer\s+(.*)$/i', $auth, $m)) {
            $decoded = @json_decode(base64_decode($m[1]), true);
            return $decoded['id'] ?? null;
        }
        return null;
    }

    // --- 1. LISTAR TODOS ---
    if ($action === 'listar') {
        $sql = "SELECT p.*, 
                       u.nombre as nombre_proponente, 
                       u.foto_perfil,
                       (SELECT COUNT(*) FROM votos WHERE id_proyecto = p.id_proyecto AND tipo_voto = 'positivo') as votos_positivos,
                       (SELECT COUNT(*) FROM votos WHERE id_proyecto = p.id_proyecto AND tipo_voto = 'negativo') as votos_negativos,
                       (SELECT COUNT(*) FROM participaciones WHERE id_proyecto = p.id_proyecto) as voluntarios_count
                FROM proyectos p
                LEFT JOIN usuarios u ON p.id_usuario = u.id_usuario
                ORDER BY p.fecha_creacion DESC";
        
        $stmt = $pdo->query($sql);
        $proyectos = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // Cálculos
        foreach ($proyectos as &$p) {
            $pos = intval($p['votos_positivos']);
            $neg = intval($p['votos_negativos']);
            $totalVotos = $pos + $neg;
            $p['porcentaje_aprobacion'] = ($totalVotos > 0) ? round(($pos / $totalVotos) * 100) : 0;
            
            $p['porcentaje_recursos'] = 0; 
            if (!empty($p['materiales'])) {
                $mats = json_decode($p['materiales'], true);
                if (is_array($mats)) {
                    $totalItems = 0; $totalRecibidos = 0;
                    foreach ($mats as $m) {
                        $totalItems += intval($m['cantidad'] ?? 0);
                        $totalRecibidos += intval($m['recibido'] ?? 0);
                    }
                    if ($totalItems > 0) $p['porcentaje_recursos'] = round(($totalRecibidos / $totalItems) * 100);
                }
            }
        }
        echo json_encode(['ok' => true, 'data' => $proyectos]);
        exit;
    }

    // --- 2. LISTAR USUARIO (Mis Proyectos) ---
    if ($action === 'listar_usuario') {
        $id = $_GET['id'] ?? null;
        $sql = "SELECT p.*,
                       (SELECT COUNT(*) FROM votos WHERE id_proyecto = p.id_proyecto AND tipo_voto = 'positivo') as votos_positivos,
                       (SELECT COUNT(*) FROM votos WHERE id_proyecto = p.id_proyecto AND tipo_voto = 'negativo') as votos_negativos,
                       (SELECT COUNT(*) FROM participaciones WHERE id_proyecto = p.id_proyecto) as voluntarios_count
                FROM proyectos p 
                WHERE id_usuario = ? 
                ORDER BY fecha_creacion DESC";
                
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$id]);
        $proyectos = $stmt->fetchAll(PDO::FETCH_ASSOC);

        foreach ($proyectos as &$p) {
            $pos = intval($p['votos_positivos']);
            $neg = intval($p['votos_negativos']);
            $totalVotos = $pos + $neg;
            $p['porcentaje_aprobacion'] = ($totalVotos > 0) ? round(($pos / $totalVotos) * 100) : 0;
            
            $p['porcentaje_recursos'] = 0; 
            if (!empty($p['materiales'])) {
                $mats = json_decode($p['materiales'], true);
                if (is_array($mats)) {
                    $totalItems = 0; $totalRecibidos = 0;
                    foreach ($mats as $m) {
                        $totalItems += intval($m['cantidad'] ?? 0);
                        $totalRecibidos += intval($m['recibido'] ?? 0);
                    }
                    if ($totalItems > 0) $p['porcentaje_recursos'] = round(($totalRecibidos / $totalItems) * 100);
                }
            }
        }

        echo json_encode(['ok' => true, 'data' => $proyectos]);
        exit;
    }

    // --- 3. VER DETALLE ---
    if ($action === 'ver') {
        $id = $_GET['id'] ?? null;
        $uid = getUserId(); 
        
        $sql = "SELECT p.*, u.nombre as nombre_proponente, u.foto_perfil, u.rol as rol_autor,
                (SELECT COUNT(*) FROM votos WHERE id_proyecto = p.id_proyecto AND tipo_voto = 'positivo') as votos_positivos,
                (SELECT COUNT(*) FROM votos WHERE id_proyecto = p.id_proyecto AND tipo_voto = 'negativo') as votos_negativos,
                (SELECT COUNT(*) FROM participaciones WHERE id_proyecto = p.id_proyecto) as voluntarios_count,
                (SELECT tipo_voto FROM votos WHERE id_proyecto = p.id_proyecto AND id_usuario = ?) as mi_voto,
                (SELECT COUNT(*) FROM participaciones WHERE id_proyecto = p.id_proyecto AND id_usuario = ?) as es_voluntario
                FROM proyectos p 
                LEFT JOIN usuarios u ON p.id_usuario = u.id_usuario WHERE p.id_proyecto = ?";
        
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$uid ?? 0, $uid ?? 0, $id]);
        $p = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($p) {
            $total = $p['votos_positivos'] + $p['votos_negativos'];
            $p['porcentaje_aprobacion'] = ($total > 0) ? round(($p['votos_positivos'] / $total) * 100) : 0;
            
            $p['porcentaje_recursos'] = 0;
            if (!empty($p['materiales'])) {
                $mats = json_decode($p['materiales'], true);
                if (is_array($mats)) {
                    $totalItems = 0; $totalRecibidos = 0;
                    foreach ($mats as $m) {
                        $totalItems += intval($m['cantidad'] ?? 0);
                        $totalRecibidos += intval($m['recibido'] ?? 0);
                    }
                    if ($totalItems > 0) $p['porcentaje_recursos'] = round(($totalRecibidos / $totalItems) * 100);
                }
            }

            echo json_encode(['ok'=>true, 'proyecto'=>$p]);
        } else { throw new Exception("No encontrado"); }
        exit;
    }

    // --- 4. CREAR, VOTAR, UNIRSE, ETC... (SE MANTIENEN IGUAL) ---
    // Copia el resto del archivo anterior si lo necesitas, pero lo crítico eran las funciones de arriba.
    // Aquí te dejo el resto por si acaso:

    if ($action === 'crear') {
        $uid = getUserId();
        if (!$uid) throw new Exception("Inicia sesión");
        // ... (código de crear proyecto igual al anterior) ...
        // Para abreviar, el resto no cambia la lógica de conteo.
        // Si necesitas el bloque 'crear' completo dímelo, pero está en la respuesta anterior.
        $titulo = $_POST['titulo'] ?? '';
        $desc = $_POST['descripcion'] ?? '';
        $cat = $_POST['categoria'] ?? 'infraestructura';
        $ubica = $_POST['ubicacion'] ?? ''; 
        $dirExacta = $_POST['direccion_exacta'] ?? ''; 
        $materiales = $_POST['materiales'] ?? '';
        $metaVotos = $_POST['meta_votos'] ?? 50;
        $metaVol = $_POST['meta_voluntarios'] ?? 10;
        $lat = $_POST['latitud'] ?? null;
        $lon = $_POST['longitud'] ?? null;
        $img = null;
        if (isset($_FILES['imagen']) && $_FILES['imagen']['error'] == 0) {
            $name = time() . '_' . $_FILES['imagen']['name'];
            if (@move_uploaded_file($_FILES['imagen']['tmp_name'], '../../assets/uploads/' . $name)) {
                $img = 'assets/uploads/' . $name;
            }
        }
        $galeriaPaths = [];
        if (isset($_FILES['galeria'])) {
            $totalFiles = count($_FILES['galeria']['name']);
            for ($i = 0; $i < $totalFiles; $i++) {
                if ($_FILES['galeria']['error'][$i] == 0) {
                    $gName = time() . '_' . $i . '_' . $_FILES['galeria']['name'][$i];
                    if (@move_uploaded_file($_FILES['galeria']['tmp_name'][$i], '../../assets/uploads/' . $gName)) {
                        $galeriaPaths[] = 'assets/uploads/' . $gName;
                    }
                }
            }
        }
        $galeriaJson = !empty($galeriaPaths) ? json_encode($galeriaPaths) : null;
        $sql = "INSERT INTO proyectos (id_usuario, titulo, descripcion, categoria, ubicacion, direccion_exacta, materiales, imagen, galeria, latitud, longitud, estado, fecha_creacion, meta_votos, meta_voluntarios) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'recaudacion', NOW(), ?, ?)";
        $pdo->prepare($sql)->execute([$uid, $titulo, $desc, $cat, $ubica, $dirExacta, $materiales, $img, $galeriaJson, $lat, $lon, $metaVotos, $metaVol]);
        echo json_encode(['ok' => true, 'msg' => 'Proyecto creado correctamente']);
        exit;
    }

    if ($action === 'votar') {
        $uid = getUserId();
        if (!$uid) throw new Exception("Inicia sesión");
        $input = json_decode(file_get_contents('php://input'), true);
        $id_p = $input['id_proyecto'];
        $tipo = $input['tipo']; 
        $stmt = $pdo->prepare("SELECT id_voto, tipo_voto FROM votos WHERE id_usuario = ? AND id_proyecto = ?");
        $stmt->execute([$uid, $id_p]);
        $votoExistente = $stmt->fetch(PDO::FETCH_ASSOC);
        if ($votoExistente) {
            if ($votoExistente['tipo_voto'] === $tipo) {
                $pdo->prepare("DELETE FROM votos WHERE id_voto = ?")->execute([$votoExistente['id_voto']]);
            } else {
                $pdo->prepare("UPDATE votos SET tipo_voto = ?, tipo = ?, fecha_voto = NOW() WHERE id_voto = ?")->execute([$tipo, $tipo, $votoExistente['id_voto']]);
            }
        } else {
            $sql = "INSERT INTO votos (id_usuario, id_proyecto, tipo_voto, tipo, fecha_voto) VALUES (?, ?, ?, ?, NOW())";
            $pdo->prepare($sql)->execute([$uid, $id_p, $tipo, $tipo]);
        }
        echo json_encode(['ok' => true]); exit;
    }

    if ($action === 'unirme') {
        $uid = getUserId();
        if (!$uid) throw new Exception("Inicia sesión");
        $input = json_decode(file_get_contents('php://input'), true);
        $idProyecto = $input['id_proyecto'];
        $check = $pdo->prepare("SELECT id_participacion FROM participaciones WHERE id_usuario = ? AND id_proyecto = ?");
        $check->execute([$uid, $idProyecto]);
        if ($check->rowCount() > 0) { echo json_encode(['ok'=>true, 'msg'=>'Ya eres voluntario']); exit; }
        $pdo->prepare("INSERT INTO participaciones (id_usuario, id_proyecto) VALUES (?, ?)")->execute([$uid, $idProyecto]);
        echo json_encode(['ok' => true]); exit;
    }

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['ok' => false, 'msg' => $e->getMessage()]);
}
?>