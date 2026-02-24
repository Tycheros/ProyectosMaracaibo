/**
 * app.js - Gestión de Sesión y Registro con datos completos
 */

window.App = {
    config: {
        apiBase: 'backend/api/',
        siteName: 'Conecta Maracaibo',
        debug: true
    },

    state: {
        isAuthenticated: false,
        user: {}
    },

    //* =====================================================
       REGISTRO (CON HABILIDADES)
       ===================================================== */
    register: async function(form) {
        console.log('📝 Iniciando registro...');

        if (form.password.value !== form.password_confirm.value) {
            alert('Las contraseñas no coinciden');
            return false;
        }

        // Recolectar habilidades seleccionadas (checkboxes)
        const skills = [];
        const checkboxes = form.querySelectorAll('input[name="skills"]:checked');
        checkboxes.forEach((cb) => {
            skills.push(cb.value);
        });

        // if (skills.length === 0) { alert("Selecciona al menos una habilidad"); return false; } // Opcional

        const formData = {
            nombre: form.nombre.value,
            cedula: form.cedula.value,
            telefono: form.telefono.value,
            email: form.email.value,
            password: form.password.value,
            avatar: form.avatar_selected ? form.avatar_selected.value : '',
            skills: skills // Enviamos el array
        };

        try {
            const response = await fetch(
                this.config.apiBase + 'registro.php',
                {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify(formData)
                }
            );

            const result = await response.json();

            if (result.ok) {
                alert('✅ Registro exitoso. ¡Bienvenido!');
                window.location.href = 'login.html';
                return true;
            } else {
                alert('❌ ' + result.msg);
                return false;
            }

        } catch (error) {
            console.error(error);
            alert('Error de conexión');
            return false;
        }
    },

    /* =====================================================
       LOGIN
       ===================================================== */
    login: async function(email, password) {
        try {
            const response = await fetch(this.config.apiBase + 'usuarios.php?action=login', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ email, password })
            });

            const result = await response.json();

            if (result.ok) {
                localStorage.setItem('token', result.token);
                localStorage.setItem('userName', result.nombre);
                window.location.href = 'proyectos.html';
            } else {
                return { ok: false, msg: result.msg || 'Credenciales inválidas' };
            }
        } catch (error) {
            return { ok: false, msg: 'Error de conexión' };
        }
    },

    /* =====================================================
       INIT
       ===================================================== */
    init: function() {
        console.log('🚀 App iniciada');
        // Chequeo básico de sesión si es necesario
    }
};

// DOM Ready
document.addEventListener('DOMContentLoaded', function() {
    App.init();
    
    // Manejo del formulario si existe en la página actual
    const formRegistro = document.getElementById('formRegistro');
    if (formRegistro) {
        formRegistro.addEventListener('submit', function(e) {
            e.preventDefault();
            App.register(formRegistro); // Usar la función actualizada
        });
    }
});