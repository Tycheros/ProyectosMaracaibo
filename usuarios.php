<?php
// backend/api/usuarios.php
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

    // --- LOGIN ---
    if ($action === 'login') {
        $input = json_decode(file_get_contents('php://input'), true);
        $email = trim($input['email'] ?? '');
        $pass  = $input['password'] ?? '';

        if (!$email || !$pass) throw new Exception("Faltan datos");

        $stmt = $pdo->prepare("SELECT id_usuario, nombre, email, password_hash, rol, foto_perfil FROM usuarios WHERE email = ?");
        $stmt->execute([$email]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($user && password_verify($pass, $user['password_hash'])) {
            $tokenPayload = json_encode(['id' => $user['id_usuario'], 'email' => $user['email'], 'rol' => $user['rol'], 'timestamp' => time()]);
            $token = base64_encode($tokenPayload);
            unset($user['password_hash']);
            echo json_encode(['ok' => true, 'msg' => 'Bienvenido', 'token' => $token, 'usuario' => $user]);
        } else {
            echo json_encode(['ok' => false, 'msg' => 'Credenciales incorrectas']);
        }
        exit;
    }

    // --- PERFIL PRIVADO (DUEÑO) ---
    if ($action === 'perfil') {
        $uid = getUserId();
        if (!$uid) throw new Exception("Sesión no válida");

        // JOIN para traer datos de ajustes
        $sql = "SELECT u.id_usuario, u.nombre, u.email, u.cedula, u.telefono, u.comunidad, u.foto_perfil, u.rol,
                       a.bio, a.mostrar_telefono, a.mostrar_comunidad, a.metodo_contacto, a.telegram_user
                FROM usuarios u
                LEFT JOIN ajustes a ON u.id_usuario = a.id_usuario
                WHERE u.id_usuario = ?";
        
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$uid]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if($user) {
            if(!$user['foto_perfil']) $user['foto_perfil'] = 'assets/avatars/avatar1.png';
            // Defaults si no hay ajustes creados aún
            if(is_null($user['mostrar_comunidad'])) $user['mostrar_comunidad'] = 1;
            if(is_null($user['mostrar_telefono'])) $user['mostrar_telefono'] = 0;
            if(is_null($user['metodo_contacto'])) $user['metodo_contacto'] = 'whatsapp';
            
            echo json_encode(['ok' => true, 'data' => $user]);
        } else {
            throw new Exception("Usuario no encontrado");
        }
        exit;
    }

    // --- ACTUALIZAR PERFIL ---
    if ($action === 'actualizar') {
        $uid = getUserId();
        if (!$uid) throw new Exception("No autorizado");

        // 1. Datos USUARIOS
        $nombre = $_POST['nombre'] ?? '';
        $tlf = $_POST['telefono'] ?? '';
        $comunidad = $_POST['comunidad'] ?? '';
        $avatarPath = $_POST['avatar_seleccionado'] ?? null;

        if ($avatarPath) {
            $sqlU = "UPDATE usuarios SET nombre=?, telefono=?, comunidad=?, foto_perfil=? WHERE id_usuario=?";
            $pdo->prepare($sqlU)->execute([$nombre, $tlf, $comunidad, $avatarPath, $uid]);
        } else {
            $sqlU = "UPDATE usuarios SET nombre=?, telefono=?, comunidad=? WHERE id_usuario=?";
            $pdo->prepare($sqlU)->execute([$nombre, $tlf, $comunidad, $uid]);
        }

        // 2. Datos AJUSTES
        $bio = $_POST['bio'] ?? '';
        $verComunidad = isset($_POST['mostrar_comunidad']) ? 1 : 0;
        $verTelefono = isset($_POST['mostrar_telefono']) ? 1 : 0;
        $metodo = $_POST['metodo_contacto'] ?? 'whatsapp';
        $telegram = $_POST['telegram_user'] ?? '';

        $sqlA = "INSERT INTO ajustes (id_usuario, bio, mostrar_telefono, mostrar_comunidad, metodo_contacto, telegram_user) 
                 VALUES (?, ?, ?, ?, ?, ?)
                 ON DUPLICATE KEY UPDATE 
                 bio = VALUES(bio), 
                 mostrar_telefono = VALUES(mostrar_telefono), 
                 mostrar_comunidad = VALUES(mostrar_comunidad), 
                 metodo_contacto = VALUES(metodo_contacto), 
                 telegram_user = VALUES(telegram_user)";
        
        $pdo->prepare($sqlA)->execute([$uid, $bio, $verTelefono, $verComunidad, $metodo, $telegram]);

        // Retornar datos frescos
        $stmt = $pdo->prepare("SELECT id_usuario as id, nombre, email, rol, foto_perfil FROM usuarios WHERE id_usuario = ?");
        $stmt->execute([$uid]);
        $newUser = $stmt->fetch(PDO::FETCH_ASSOC);

        echo json_encode(['ok' => true, 'msg' => 'Guardado', 'usuario' => $newUser]);
        exit;
    }

    // --- PERFIL PÚBLICO (QR) - CORREGIDO ---
    if ($action === 'ver_publico') {
        $id = $_GET['id'] ?? null;
        if (!$id) throw new Exception("ID requerido");

        // AQUI ESTABA EL ERROR: Necesitamos el JOIN con 'ajustes' para leer la bio y la privacidad
        $sql = "SELECT u.id_usuario, u.nombre, u.rol, u.comunidad, u.telefono, u.foto_perfil,
                       a.bio, a.mostrar_comunidad, a.mostrar_telefono
                FROM usuarios u
                LEFT JOIN ajustes a ON u.id_usuario = a.id_usuario
                WHERE u.id_usuario = ?";

        $stmt = $pdo->prepare($sql);
        $stmt->execute([$id]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($user) {
            // APLICAR REGLAS DE PRIVACIDAD
            // Si el valor es nulo (no configurado), asumimos por defecto: Comunidad SI(1), Telefono NO(0)
            $verCom = isset($user['mostrar_comunidad']) ? $user['mostrar_comunidad'] : 1;
            $verTlf = isset($user['mostrar_telefono']) ? $user['mostrar_telefono'] : 0;

            if ($verCom == 0) $user['comunidad'] = null; // Ocultar
            if ($verTlf == 0) $user['telefono'] = null;  // Ocultar

            // Limpieza interna
            unset($user['mostrar_comunidad']);
            unset($user['mostrar_telefono']);

            echo json_encode(['ok' => true, 'usuario' => $user]);
        } else {
            throw new Exception("Usuario no encontrado");
        }
        exit;
    }

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['ok' => false, 'msg' => $e->getMessage()]);
}
?>