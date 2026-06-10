/**
 * Archivo para manejar los mensajes de validación y respuesta del proceso de inicio de sesión
 * Se Utiliza SweetAlert2 para mostrar mensajes de éxito o error al usuario
 * Escucha el evento de envío del formulario, realiza la solicitud AJAX y maneja la respuesta del servidor
 */

document.addEventListener('DOMContentLoaded', () => {
    const loginForm = document.querySelector('form[action="index.php"]');

    if (!loginForm) {
        return;
    }

    loginForm.addEventListener('submit', async (event) => {
        event.preventDefault();

        const submitButton = loginForm.querySelector('button[type="submit"]');
        const originalText = submitButton?.textContent || 'Entrar';

        if (submitButton) {
            submitButton.disabled = true;
            submitButton.textContent = 'Validando...';
        }

        const formData = new FormData(loginForm);

        try {
            const response = await fetch('index.php', {
                method: 'POST',
                body: formData,
                credentials: 'same-origin'
            });

            const result = await response.json();

            if (result.status === 'success') {
                await Swal.fire({
                    icon: 'success',
                    title: 'Inicio de sesión correcto',
                    text: result.message || 'Bienvenido al sistema.',
                    confirmButtonColor: '#0d6efd'
                });

                // Guardar token JWT en localStorage para autenticación en cliente
                if (result.token) {
                    try {
                        localStorage.setItem('jwt', result.token);
                    } catch (e) {
                        console.warn('No se pudo guardar el token en localStorage', e);
                    }
                }

                window.location.href = result.redirect || 'index.php?dashboard=1';
                return;
            }

            await Swal.fire({
                icon: 'error',
                title: 'No se pudo iniciar sesión',
                text: result.message || 'Verifica tus credenciales e inténtalo nuevamente.',
                confirmButtonColor: '#dc3545'
            });
        } catch (error) {
            await Swal.fire({
                icon: 'error',
                title: 'Error de conexión',
                text: 'No se pudo procesar la solicitud. Inténtalo nuevamente.',
                confirmButtonColor: '#dc3545'
            });
        } finally {
            if (submitButton) {
                submitButton.disabled = false;
                submitButton.textContent = originalText;
            }
        }
    });
});
