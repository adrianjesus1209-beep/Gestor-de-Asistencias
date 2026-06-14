<div class="login-wrapper">
    <div class="login-card">
        <div class="login-header">
            <img 
                src="<?= BASE_URL ?>/assets/img/logos/logo_unefa.png" 
                alt="UNEFA" 
                class="login-logo"
            >
            <h3 class="login-title">Iniciar Sesión</h3>
            <p class="login-subtitle">Sistema de Gestión de Asistencia</p>
        </div>
        
        <form>
            <div class="form-group">
                <label class="login-label">Correo Electrónico</label>
                <div class="login-input-group">
                    <span class="login-icon">
                        <i class="bi bi-envelope"></i>
                    </span>
                    <input 
                        type="email" 
                        class="login-input" 
                        placeholder="correo@gmail.com"
                    >
                </div>
            </div>
            
            <div class="form-group">
                <label class="login-label">Contraseña</label>
                <div class="login-input-group">
                    <span class="login-icon">
                        <i class="bi bi-lock"></i>
                    </span>
                    <input 
                        type="password" 
                        class="login-input" 
                        placeholder="Ingrese su contraseña"
                    >
                </div>
            </div>
            
            <div class="login-action">
                <a href="index.php?route=dashboard" 
                    class="btn boton-primario-personalizado py-2 fw-bold text-center">
                    Ingresar
                </a>
            </div>
        </form>
        
        <div class="login-footer">
            <p class="login-footer-text">
                ¿No posee una cuenta? 
                <a href="index.php?route=register" class="login-link">Registrarse</a>
            </p>
            <hr>
            <p class="login-footer-text">
                ¿Olvidaste tu contraseña? 
                <a href="index.php?route=change-password" class="login-link">Cambiar contraseña</a>
            </p>
        </div>
        
        <div class="login-footer mt-extra">
            <a href="index.php?route=home" class="login-link small">
                <i class="bi bi-arrow-left"></i> Volver al inicio
            </a>
        </div>
    </div>
</div>