/**
 * login.js - Versión Corregida para InfinityFree
 */

document.addEventListener('DOMContentLoaded', function() {
    console.log('🔐 Login page loaded');
    
    // 1. Verificar si ya hay sesión activa
    const rawToken = localStorage.getItem('token');
    if (rawToken) {
        try {
            // Decodificar token base64 simple
            const json = atob(rawToken);
            const data = JSON.parse(json);
            // Si existe el token, asumimos que es válido y redirigimos
            // (La validación real la hará el backend al pedir datos)
            if (data && data.id) {
                console.log('✅ Sesión encontrada, redirigiendo...');
                window.location.href = 'index.html'; 
                return;
            }
        } catch (e) {
            localStorage.removeItem('token');
        }
    }
    
    // 2. Configurar el ojo de la contraseña
    const toggleBtn = document.getElementById('toggle-password');
    const passwordInput = document.getElementById('password');
    
    if (toggleBtn && passwordInput) {
        toggleBtn.addEventListener('click', function() {
            const type = passwordInput.getAttribute('type') === 'password' ? 'text' : 'password';
            passwordInput.setAttribute('type', type);
            const icon = toggleBtn.querySelector('span');
            if (icon) icon.textContent = type === 'password' ? 'visibility' : 'visibility_off';
        });
    }
    
    // 3. Manejar el Formulario de Login
    const loginForm = document.getElementById('form-login');
    if (loginForm) {
        loginForm.addEventListener('submit', async function(e) {
            e.preventDefault();
            
            const email = document.getElementById('email').value;
            const password = document.getElementById('password').value;
            const btn = document.getElementById('btn-login');
            
            if (!email || !password) {
                alert('Por favor completa todos los campos');
                return;
            }

            // Bloquear botón
            const originalText = btn.innerHTML;
            btn.innerHTML = 'Verificando...';
            btn.disabled = true;
            
            try {
                // --- CORRECCIÓN IMPORTANTE: RUTA RELATIVA ---
                // Usamos "backend/api/..." directo, sin carpetas extrañas
                const response = await fetch('backend/api/usuarios.php?action=login', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ email: email, password: password })
                });

                // Leemos como texto para ver si el servidor mandó error HTML o PHP
                const responseText = await response.text();
                
                let json;
                try {
                    json = JSON.parse(responseText);
                } catch (err) {
                    console.error("El servidor respondió basura:", responseText);
                    throw new Error("Respuesta inválida del servidor. Revisa la consola.");
                }

                if (json.ok) {
                    // Guardar sesión
                    localStorage.setItem('token', json.token);
                    localStorage.setItem('usuario', JSON.stringify(json.usuario));
                    
                    // Redirigir según rol
                    if (json.usuario.rol === 'administrador') {
                        window.location.href = 'admin.html';
                    } else {
                        window.location.href = 'index.html';
                    }
                } else {
                    alert("Error: " + json.msg);
                }

            } catch (error) {
                console.error(error);
                alert("Ocurrió un error: " + error.message);
            } finally {
                btn.innerHTML = originalText;
                btn.disabled = false;
            }
        });
    }
});