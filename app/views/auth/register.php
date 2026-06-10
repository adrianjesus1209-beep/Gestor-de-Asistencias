<div class="register-wrapper">
    <div class="register-card">
        <div class="register-header">
            <img 
                src="<?= BASE_URL ?>/assets/img/logos/logo_unefa.png" 
                alt="UNEFA" 
                class="register-logo"
            >
            <h3 class="register-title">Registro de Usuario</h3>
            <p class="register-subtitle">Complete los datos solicitados</p>
        </div>
        
        <form id="registerForm">
            <div class="form-grid">
                <div class="form-group">
                    <label class="register-label">Primer Nombre</label>
                    <input type="text" id="first_name" name="first_name" class="register-input" required>
                </div>
                
                <div class="form-group">
                    <label class="register-label">Segundo Nombre</label>
                    <input type="text" id="middle_name" name="middle_name" class="register-input">
                </div>
                
                <div class="form-group">
                    <label class="register-label">Primer Apellido</label>
                    <input type="text" id="last_name" name="last_name" class="register-input" required>
                </div>
                
                <div class="form-group">
                    <label class="register-label">Segundo Apellido</label>
                    <input type="text" id="second_last_name" name="second_last_name" class="register-input">
                </div>
                
                <div class="form-group">
                    <label class="register-label">Cédula</label>
                    <input type="text" id="id_number" name="id_number" class="register-input" required placeholder="V-12345678">
                </div>
                
                <div class="form-group">
                    <label class="register-label">Correo Electrónico</label>
                    <input type="email" id="email" name="email" class="register-input" required>
                </div>
                
                <div class="form-group">
                    <label class="register-label">Carrera</label>
                    <select id="career_id" name="career_id" class="register-input" required>
                        <option value="" selected disabled>Seleccione</option>
                        <option value="1">Ingeniería de Sistemas</option>
                        <option value="2">Telecomunicaciones</option>
                        <option value="3">Ingeniería Mecánica</option>
                    </select>
                </div>
                
                <div class="form-group">
                    <label class="register-label">Contraseña</label>
                    <input type="password" id="password" name="password" class="register-input" required>
                </div>
            </div>
            
            <div class="register-action">
                <button type="submit" class="register-btn">Registrarse</button>
            </div>
        </form>
        
        <div class="register-footer">
            <p class="register-footer-text">
                ¿Ya posee una cuenta? 
                <a href="index.php?login" class="register-link">Iniciar sesión</a>
            </p>
        </div>
        
        <div class="register-footer mt-extra">
            <a href="index.php" class="register-link small">
                <i class="bi bi-arrow-left"></i> Volver al inicio
            </a>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
document.getElementById('registerForm').addEventListener('submit', function(e) {
    e.preventDefault();
    
    const formData = {
        first_name:       document.getElementById('first_name').value,
        middle_name:      document.getElementById('middle_name').value,
        last_name:        document.getElementById('last_name').value,
        second_last_name: document.getElementById('second_last_name').value,
        id_number:        document.getElementById('id_number').value,
        email:            document.getElementById('email').value,
        password:         document.getElementById('password').value,
        career_id:        document.getElementById('career_id').value
    };

    Swal.fire({
        title: 'Procesando registro...',
        didOpen: () => { Swal.showLoading() },
        allowOutsideClick: false
    });

    fetch('index.php?api=register', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify(formData)
    })
    .then(response => response.json())
    .then(data => {
        if (data.status === 'success') {
            Swal.fire('¡Éxito!', data.message, 'success').then(() => {
                window.location.href = 'index.php?login';
            });
        } else {
            Swal.fire('Error', data.message, 'error');
        }
    })
    .catch(err => {
        console.error(err);
        Swal.fire('Error', 'Fallo en la comunicación con el servidor.', 'error');
    });
});
</script>
