<div class="register-wrapper">
    <div class="register-card">
        <div class="register-header">
            <img 
                src="<?= BASE_URL ?>/assets/img/logos/logo_unefa.png" 
                alt="UNEFA" 
                class="register-logo"
            >
            <h3 class="register-title">Registro de Profesores</h3>
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
                    <label class="register-label">Contraseña</label>
                    <input type="password" class="register-input" required>
                </div>
            </div>
            
            <div class="register-action">
                <button type="submit" class="register-btn">Registrar Profesor</button>
            </div>
        </form>
        
        <div class="register-footer mt-extra">
            <a href="index.php?route=dashboard&role=Admin" class="register-link small">
                <i class="bi bi-arrow-left"></i> Volver al dashboard
            </a>
        </div>
    </div>
</div>