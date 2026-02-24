// js/crear-donacion.js

document.addEventListener('DOMContentLoaded', () => {
    
    // --- PARTE 1: GESTIÓN VISUAL (Cambiar títulos según modo) ---
    const params = new URLSearchParams(window.location.search);
    const modo = params.get('modo') || 'oferta'; // 'oferta' o 'solicitud'

    const title = document.getElementById('page-title');
    const desc = document.getElementById('page-desc');
    const inputModo = document.getElementById('input-modo');
    const btnText = document.getElementById('btn-submit-text');
    const crumbModo = document.getElementById('crumb-modo');

    if (modo === 'solicitud') {
        if(title) title.innerText = "Solicitar Ayuda";
        if(desc) desc.innerText = "Describa la necesidad de su comunidad para encontrar apoyo.";
        if(inputModo) inputModo.value = "solicitud";
        if(btnText) btnText.innerText = "Enviar Solicitud";
        if(crumbModo) crumbModo.innerText = "Solicitar";
    } else {
        if(title) title.innerText = "Publicar Oferta de Donación";
        if(desc) desc.innerText = "Ofrezca recursos para proyectos sociales.";
        if(inputModo) inputModo.value = "oferta";
        if(btnText) btnText.innerText = "Publicar Oferta";
        if(crumbModo) crumbModo.innerText = "Publicar";
    }

    // --- PARTE 2: ENVÍO DEL FORMULARIO ---
    const form = document.getElementById('form-crear'); // ID CORREGIDO

    if (form) {
        form.addEventListener('submit', async function(e) {
            e.preventDefault();

            const btn = this.querySelector('button[type="submit"]');
            const originalText = btnText ? btnText.innerText : "Publicar"; // Guardar texto original
            if(btnText) btnText.innerText = "Enviando...";
            btn.disabled = true;

            const formData = new FormData(this);
            // Asegurar que el modo se envíe (aunque el input hidden ya lo tiene)
            formData.set('modo', modo); 

            const token = localStorage.getItem('token');
            if (!token) {
                alert("Tu sesión ha expirado. Por favor inicia sesión de nuevo.");
                window.location.href = 'login.html';
                return;
            }

            try {
                const response = await fetch('backend/api/donaciones.php?action=crear', {
                    method: 'POST',
                    headers: { 'Authorization': 'Bearer ' + token },
                    body: formData
                });

                const text = await response.text();
                let json;
                try {
                    json = JSON.parse(text);
                } catch (err) {
                    console.error("Respuesta basura:", text);
                    throw new Error("El servidor no respondió correctamente.");
                }

                if (json.ok) {
                    alert("¡Operación exitosa!");
                    window.location.href = 'donaciones.html'; 
                } else {
                    alert("Error: " + json.msg);
                }

            } catch (error) {
                console.error(error);
                alert("Ocurrió un error: " + error.message);
            } finally {
                if(btnText) btnText.innerText = originalText;
                btn.disabled = false;
            }
        });
    }
});