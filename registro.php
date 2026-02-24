<?php
// backend/api/registro.php
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');
header('Content-Type: application/json; charset=utf-8');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') exit;

require '../config.php';

$data = json_decode(file_get_contents('php://input'), true) ?? [];

try {
    // Validar obligatorios básicos
    if (empty($data['nombre']) || empty($data['email']) || empty($data['password'])) {
        throw new Exception('Faltan datos obligatorios');
    }

    // 1. Recibir datos básicos
    $nombre = trim($data['nombre']);
    $email  = trim($data['email']);
    $pass   = $data['password'];
    $cedula = trim($data['cedula'] ?? '');
    $telefono = trim($data['telefono'] ?? '');
    
    // 2. Recibir Dirección y Parroquia
    $direccion = trim($data['direccion'] ?? '');
    // Guardaremos la "parroquia" en el campo "comunidad" de la BD
    $parroquia = trim($data['parroquia'] ?? ''); 
    
    $avatar = trim($data['avatar'] ?? '');
    
    // 3. Procesar Habilidades
    $habilidades = '';
    if (isset($data['skills']) && is_array($data['skills'])) {
        $habilidades = implode(',', $data['skills']);
    }

    // 4. Verificar duplicados
    $stmt = $pdo->prepare("SELECT id_usuario FROM usuarios WHERE email = ?");
    $stmt->execute([$email]);
    if ($stmt->fetch()) {
        throw new Exception('El correo ya está registrado');
    }

    // 5. Encriptar e Insertar
    $hash = password_hash($pass, PASSWORD_DEFAULT);

    // SQL Actualizado con direccion y comunidad (parroquia)
    $sql = "INSERT INTO usuarios (
                nombre, email, password_hash, cedula, telefono, 
                direccion, comunidad, 
                foto_perfil, habilidades, rol, fecha_registro, estado
            ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, 'usuario', NOW(), 'activo')";
            
    $stmt = $pdo->prepare($sql);
    $stmt->execute([
        $nombre, 
        $email, 
        $hash, 
        $cedula, 
        $telefono, 
        $direccion, 
        $parroquia, // Se guarda en la columna 'comunidad'
        $avatar, 
        $habilidades
    ]);

    echo json_encode(['ok' => true, 'msg' => 'Registro exitoso']);

} catch (Exception $e) {
    echo json_encode(['ok' => false, 'msg' => $e->getMessage()]);
}