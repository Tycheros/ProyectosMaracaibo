// js/crear-proyecto.js

document.addEventListener('DOMContentLoaded', () => {
    const form = document.getElementById('form-crear-proyecto');

    if (form) {
        form.addEventListener('submit', async function(e) {
            e.preventDefault();

            // Bloquear el botón para evitar doble clic
            const btn = this.querySelector('button[type="submit"]');
            const btnText = document.getElementById('btn-text');
            const originalText = btnText.innerText;
            
            btnText.innerText = "Publicando...";
            btn.disabled = true;

            const token = localStorage.getItem('token');
            if (!token) {
                alert("Tu sesión ha expirado. Por favor inicia sesión de nuevo.");
                window.location.href = 'login.html';
                return;
            }

            const formData = new FormData(this);

            try {
                // Enviar al PHP
                const response = await fetch('backend/api/proyectos.php?action=crear', {
                    method: 'POST',
                    headers: { 'Authorization': 'Bearer ' + token },
                    body: formData
                });

                const text = await response.text();
                let json;
                try {
                    json = JSON.parse(text);
                } catch (err) {
                    console.error("Respuesta del servidor:", text);
                    throw new Error("El servidor no devolvió JSON válido.");
                }

                if (json.ok) {
                    alert("¡Proyecto creado y guardado con éxito!");
                    // Redirigir a la pestaña de Mis Proyectos
                    window.location.href = 'perfil.html?tab=proyectos';
                } else {
                    alert("Error al publicar: " + json.msg);
                }

            } catch (error) {
                console.error("Error de conexión:", error);
                alert("Ocurrió un error de conexión: " + error.message);
            } finally {
                btnText.innerText = originalText;
                btn.disabled = false;
            }
        });
    }
});