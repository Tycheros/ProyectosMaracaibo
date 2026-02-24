<?php
// backend/api/donaciones.php
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
    else throw new Exception("Config no encontrado");

    $action = $_GET['action'] ?? '';

    function getUserId() {
        $auth = $_SERVER['HTTP_AUTHORIZATION'] ?? '';
        if (preg_match('/^Bearer\s+(.*)$/i', $auth, $m)) {
            $decoded = @json_decode(base64_decode($m[1]), true);
            return $decoded['id'] ?? null;
        }
        return null;
    }

    // --- 1. LISTAR PÚBLICAS (Muro de donaciones.html) ---
    // Filtramos para que NO salgan las que tienen id_proyecto (son mensajes privados)
    if ($action === 'listar') {
        $sql = "SELECT d.*, u.nombre as nombre_usuario, u.foto_perfil, u.comunidad 
                FROM donaciones d 
                LEFT JOIN usuarios u ON d.id_usuario = u.id_usuario
                WHERE d.id_proyecto IS NULL OR d.id_proyecto = 0
                ORDER BY d.fecha_oferta DESC";
        $stmt = $pdo->query($sql);
        echo json_encode(['ok' => true, 'data' => $stmt->fetchAll(PDO::FETCH_ASSOC)]);
        exit;
    }

    // --- 2. LISTAR PROPIAS (Para Mi Perfil) ---
    // Aquí sí mostramos TODO lo que el usuario ha hecho (público o privado)
    if ($action === 'listar_propias') {
        $uid = getUserId();
        if (!$uid) throw new Exception("Sesión requerida");

        $sql = "SELECT d.*, p.titulo as nombre_proyecto 
                FROM donaciones d 
                LEFT JOIN proyectos p ON d.id_proyecto = p.id_proyecto
                WHERE d.id_usuario = ? 
                ORDER BY d.fecha_oferta DESC";
        
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$uid]);
        echo json_encode(['ok' => true, 'data' => $stmt->fetchAll(PDO::FETCH_ASSOC)]);
        exit;
    }

    /// --- 3. VER DETALLE (Con Privacidad desde Tabla Ajustes) ---
    if ($action === 'ver') {
        $id = $_GET['id'] ?? null;
        if (!$id) throw new Exception("Falta ID");

        $sql = "SELECT d.*, 
                       u.nombre as nombre_donante, u.email as email_donante, u.foto_perfil as foto_donante, u.telefono as tlf_donante,
                       a.metodo_contacto, a.telegram_user, a.mostrar_telefono,
                       p.titulo as nombre_proyecto, p.imagen as imagen_proyecto, p.id_usuario as id_dueno_proyecto
                FROM donaciones d
                LEFT JOIN usuarios u ON d.id_usuario = u.id_usuario
                LEFT JOIN ajustes a ON d.id_usuario = a.id_usuario  -- JOIN NUEVO
                LEFT JOIN proyectos p ON d.id_proyecto = p.id_proyecto
                WHERE d.id_donacion = ?";
        
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$id]);
        $data = $stmt->fetch(PDO::FETCH_ASSOC);

        // Lógica de privacidad en el Backend: Si mostrar_telefono es 0, borramos el teléfono antes de enviarlo
        if ($data) {
            // Si no tiene registro en ajustes, asumimos privacidad por defecto (0)
            $mostrarTlf = isset($data['mostrar_telefono']) ? $data['mostrar_telefono'] : 0;
            
            if ($mostrarTlf == 0) {
                $data['tlf_donante'] = null; // Ocultar teléfono
            }
            
            echo json_encode(['ok' => true, 'data' => $data]);
        } else {
            echo json_encode(['ok' => false, 'msg' => 'No encontrado']);
        }
        exit;
    }

    // --- 4. CREAR OFERTA / DONACIÓN ---
    if ($action === 'crear') {
        $uid = getUserId();
        if (!$uid) throw new Exception("Inicia sesión");

        $idProyecto = !empty($_POST['id_proyecto']) ? $_POST['id_proyecto'] : null;
        $titulo = $_POST['titulo'] ?? 'Oferta';
        $desc = $_POST['descripcion'] ?? '';
        $cat = 'materiales';
        $ubica = 'Maracaibo'; 
        
        // Formatear items del modal
        if (isset($_POST['items_donados']) && $idProyecto) {
            $items = json_decode($_POST['items_donados'], true);
            $listaStr = "";
            if (is_array($items)) {
                foreach ($items as $item) {
                    $listaStr .= "• " . ($item['cantidad'] ?? '1') . " x " . ($item['nombre'] ?? 'Item') . "\n";
                }
            }
            $desc = "Te han ofrecido:\n\n" . $listaStr . "\nNota: " . ($_POST['mensaje_extra'] ?? '');
        }

        $sql = "INSERT INTO donaciones (id_usuario, id_proyecto, titulo, descripcion, categoria, ubicacion, modo, tipo, fecha_oferta) 
                VALUES (?, ?, ?, ?, ?, ?, 'oferta', 'oferta', NOW())";
        
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$uid, $idProyecto, $titulo, $desc, $cat, $ubica]);
        
        // Obtener el ID para el enlace
        $idDonacion = $pdo->lastInsertId();

        // Notificación al dueño del proyecto
        if ($idProyecto) {
            $stmtProy = $pdo->prepare("SELECT id_usuario, titulo FROM proyectos WHERE id_proyecto = ?");
            $stmtProy->execute([$idProyecto]);
            $proy = $stmtProy->fetch(PDO::FETCH_ASSOC);

            if ($proy && $proy['id_usuario'] != $uid) {
                $msgNoti = "¡Oferta recibida! Tienes materiales para: " . $proy['titulo'];
                $linkNoti = "obtener.html?id=" . $idDonacion;
                $pdo->prepare("INSERT INTO notificaciones (id_usuario, tipo, mensaje, enlace, leido, fecha_creacion) VALUES (?, 'donacion', ?, ?, 0, NOW())")->execute([$proy['id_usuario'], $msgNoti, $linkNoti]);
            }
        }
        echo json_encode(['ok' => true]);
        exit;
    }

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['ok' => false, 'msg' => $e->getMessage()]);
}
?>