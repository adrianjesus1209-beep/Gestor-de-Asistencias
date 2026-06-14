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
        
        <form>
            <div class="form-grid">
                <div class="form-group">
                    <label class="register-label">Primer Nombre</label>
                    <input type="text" class="register-input" required>
                </div>
                
                <div class="form-group">
                    <label class="register-label">Segundo Nombre</label>
                    <input type="text" class="register-input" required>
                </div>
                
                <div class="form-group">
                    <label class="register-label">Apellido Paterno</label>
                    <input type="text" class="register-input" required>
                </div>
                
                <div class="form-group">
                    <label class="register-label">Apellido Materno</label>
                    <input type="text" class="register-input" required>
                </div>
                
                <div class="form-group">
                    <label class="register-label">Correo Electrónico</label>
                    <input type="email" class="register-input" required>
                </div>
                
                <div class="form-group">
                    <label class="register-label">Carrera</label>
                    <select class="register-input" required>
                        <option value="" selected disabled>Seleccione</option>
                        <option value="opcion1">Ingeniería de Sistemas</option>
                        <option value="opcion2">Telecomunicaciones</option>
                        <option value="opcion3">Ingeniería Mecánica</option>
                        <option value="opcion4">Administración y Gestión Municipal</option>
                        <option value="opcion5">Ingeniería en Electrónica</option>
                    </select>
                </div>
                
                <div class="form-group">
                    <label class="register-label">Semestre</label>
                    <select class="register-input" required>
                        <option value="" selected disabled>Seleccione</option>
                        <option value="opcion1">1er Semestre</option>
                        <option value="opcion2">2do Semestre</option>
                        <option value="opcion3">3er Semestre</option>
                        <option value="opcion4">4to Semestre</option>
                        <option value="opcion5">5to Semestre</option>
                        <option value="opcion6">6to Semestre</option>
                        <option value="opcion7">7mo Semestre</option>
                        <option value="opcion8">8vo Semestre</option>
                        <option value="opcion9">9no Semestre</option>
                    </select>
                </div>
                
                <div class="form-group">
                    <label class="register-label">Contraseña</label>
                    <input type="password" class="register-input" required>
                </div>
            </div>
            
            <div class="register-action">
                <a href="index.php?route=security-questions" 
                    class="btn boton-primario-personalizado py-2 fw-bold text-center">
                    Continuar
                </a>
            </div>
        </form>
        
        <div class="register-footer">
            <p class="register-footer-text">
                ¿Ya posee una cuenta? 
                <a href="index.php?route=login" class="register-link">Iniciar sesión</a>
            </p>
        </div>
        
        <div class="register-footer mt-extra">
            <a href="index.php" class="register-link small">
                <i class="bi bi-arrow-left"></i> Volver al inicio
            </a>
        </div>
    </div>
</div>