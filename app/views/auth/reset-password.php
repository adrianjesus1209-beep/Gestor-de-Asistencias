<div class="login-wrapper">
    <div class="login-card">
        <div class="login-header">
            <img 
                src="<?= BASE_URL ?>/assets/img/logos/logo_unefa.png" 
                alt="UNEFA" 
                class="login-logo"
            >
            <h3 class="login-title">Recuperar Contraseña</h3>
            <p class="login-subtitle">Ingrese su correo y cédula para restablecer la contraseña.</p>
        </div>
        
        <form id="resetPasswordForm">
            <div class="form-group">
                <label class="login-label">Correo Electrónico</label>
                <div class="login-input-group">
                    <span class="login-icon">
                        <i class="bi bi-envelope"></i>
                    </span>
                    <input 
                        type="email" 
                        id="email" 
                        name="email"
                        class="login-input" 
                        placeholder="correo@unefa.edu.ve"
                        required
                    >
                </div>
            </div>

            <div class="form-group">
                <label class="login-label">Cédula</label>
                <div class="login-input-group">
                    <span class="login-icon">
                        <i class="bi bi-person-badge"></i>
                    </span>
                    <input 
                        type="text" 
                        id="id_number" 
                        name="id_number"
                        class="login-input" 
                        placeholder="V-12345678"
                        required
                    >
                </div>
            </div>

            <!-- Contenedor donde se cargan las preguntas de seguridad tras verificar identidad -->
            <div id="securityQuestionsContainer" style="display: none;">
                <div id="securityQuestionsList"></div>
            </div>

            <!-- Campos de nueva contraseña solo visibles luego de responder preguntas -->
            <div id="passwordFields" style="display: none;">
                <div class="form-group">
                    <label class="login-label">Nueva Contraseña</label>
                    <div class="login-input-group">
                        <span class="login-icon">
                            <i class="bi bi-lock"></i>
                        </span>
                        <input 
                            type="password" 
                            id="new_password" 
                            name="new_password"
                            class="login-input" 
                            placeholder="Ingrese nueva contraseña"
                        >
                    </div>
                </div>

                <div class="form-group">
                    <label class="login-label">Confirmar Contraseña</label>
                    <div class="login-input-group">
                        <span class="login-icon">
                            <i class="bi bi-lock-fill"></i>
                        </span>
                        <input 
                            type="password" 
                            id="confirm_password" 
                            name="confirm_password"
                            class="login-input" 
                            placeholder="Repita la contraseña"
                        >
                    </div>
                </div>
            </div>

            <div class="login-action">
                <button type="submit" id="resetPasswordButton" class="btn boton-primario-personalizado w-100 py-3 fw-bold text-center rounded-pill">
                    Verificar identidad
                </button>
            </div>
        </form>

        <div class="login-footer">
            <p class="login-footer-text">
                ¿Recordó su contraseña? 
                <a href="index.php?login" class="login-link">Iniciar sesión</a>
            </p>
        </div>

        <div class="login-footer mt-extra">
            <a href="index.php" class="login-link small">
                <i class="bi bi-arrow-left"></i> Volver al inicio
            </a>
        </div>
    </div>
</div>

<script src="<?= BASE_URL ?>/SweetAlert2/sweetalert2.all.min.js"></script>
<script>
// Formulario de restablecimiento de contraseña con flujo en dos pasos:
// 1. Validar email y cédula para cargar preguntas de seguridad.
// 2. Responder preguntas y establecer una nueva contraseña.
const resetForm = document.getElementById('resetPasswordForm');
const securityQuestionsContainer = document.getElementById('securityQuestionsContainer');
const securityQuestionsList = document.getElementById('securityQuestionsList');
const passwordFields = document.getElementById('passwordFields');
const resetPasswordButton = document.getElementById('resetPasswordButton');
let currentQuestions = [];

resetForm.addEventListener('submit', async function(e) {
    e.preventDefault();

    const email = document.getElementById('email').value.trim();
    const idNumber = document.getElementById('id_number').value.trim();

    if (!email || !idNumber) {
        await Swal.fire('Error', 'Por favor, ingrese correo y cédula.', 'error');
        return;
    }

    // Si aún no se han cargado las preguntas, solicitamos al servidor las preguntas de seguridad.
    if (passwordFields.style.display === 'none') {
        await loadSecurityQuestions(email, idNumber);
        return;
    }

    // Construir el array de respuestas que enviaremos al backend.
    const answers = currentQuestions.map(q => ({
        question_id: q.question_id,
        answer: document.getElementById('answer_' + q.question_id).value.trim()
    }));

    const newPassword = document.getElementById('new_password').value;
    const confirmPassword = document.getElementById('confirm_password').value;

    if (!newPassword || !confirmPassword) {
        await Swal.fire('Error', 'Por favor, ingrese la nueva contraseña y su confirmación.', 'error');
        return;
    }

    if (newPassword !== confirmPassword) {
        await Swal.fire('Error', 'Las contraseñas no coinciden.', 'error');
        return;
    }

    if (newPassword.length < 8) {
        await Swal.fire('Error', 'La contraseña debe tener al menos 8 caracteres.', 'error');
        return;
    }

    if (answers.some(a => !a.answer)) {
        await Swal.fire('Error', 'Responda las dos preguntas de seguridad.', 'error');
        return;
    }

    resetPasswordButton.disabled = true;
    resetPasswordButton.textContent = 'Actualizando...';

    const payload = {
        email,
        id_number: idNumber,
        new_password: newPassword,
        confirm_password: confirmPassword,
        security_answers: answers
    };

    try {
        const response = await fetch('index.php?api=reset_password', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify(payload)
        });
        const result = await response.json();

        if (result.status === 'success') {
            await Swal.fire('¡Éxito!', result.message, 'success');
            window.location.href = 'index.php?login';
        } else {
            await Swal.fire('Error', result.message, 'error');
        }
    } catch (error) {
        await Swal.fire('Error', 'Ocurrió un error al comunicarse con el servidor.', 'error');
    } finally {
        resetPasswordButton.disabled = false;
        resetPasswordButton.textContent = 'Restablecer contraseña';
    }
});

async function loadSecurityQuestions(email, idNumber) {
    // Pedimos al backend las preguntas de seguridad asociadas al usuario.
    resetPasswordButton.disabled = true;
    resetPasswordButton.textContent = 'Cargando...';

    try {
        const response = await fetch('index.php?api=security_questions', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ email, id_number: idNumber })
        });
        const result = await response.json();

        if (result.status === 'success' && Array.isArray(result.questions)) {
            currentQuestions = result.questions;
            renderSecurityQuestions(result.questions);
            securityQuestionsContainer.style.display = 'block';
            passwordFields.style.display = 'block';
            resetPasswordButton.textContent = 'Restablecer contraseña';
        } else {
            await Swal.fire('Error', result.message || 'No se pudieron cargar las preguntas de seguridad.', 'error');
            resetPasswordButton.textContent = 'Verificar identidad';
        }
    } catch (error) {
        await Swal.fire('Error', 'Ocurrió un error al comunicarse con el servidor.', 'error');
        resetPasswordButton.textContent = 'Verificar identidad';
    } finally {
        resetPasswordButton.disabled = false;
    }
}

function renderSecurityQuestions(questions) {
    // Renderiza las preguntas recibidas desde el servidor como campos de texto.
    securityQuestionsList.innerHTML = questions.map(q => `
        <div class="form-group">
            <label class="login-label">${q.question_text}</label>
            <div class="login-input-group">
                <span class="login-icon">
                    <i class="bi bi-question-circle"></i>
                </span>
                <input 
                    type="text"
                    id="answer_${q.question_id}"
                    class="login-input"
                    placeholder="Escriba su respuesta"
                    required
                />
            </div>
        </div>
    `).join('');
}
</script>
