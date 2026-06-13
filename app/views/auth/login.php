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
        
        <form action="index.php" method="post" id="loginForm">
            <div class="form-group">
                <label class="login-label">Correo Electrónico</label>
                <div class="login-input-group">
                    <span class="login-icon">
                        <i class="bi bi-envelope"></i>
                    </span>
                    <input 
                        type="email" 
                        name="email"
                        class="login-input" 
                        placeholder="correo@unefa.edu.ve"
                        required
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
                        name="password"
                        class="login-input" 
                        placeholder="Ingrese su contraseña"
                        required
                    >
                </div>
            </div>
            
            <div class="login-action">
                <button type="submit" class="btn boton-primario-personalizado w-100 py-3 fw-bold text-center rounded-pill">
                    Ingresar
                </button>
            </div>
        </form>
        
        <div class="login-footer">
            <p class="login-footer-text">
                ¿No posee una cuenta? 
                <a href="index.php?register" class="login-link">Registrarse</a>
            </p>
            <p class="login-footer-text">
                <a href="index.php?reset_password" class="login-link">¿Olvidó su contraseña?</a>
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
<script src="<?= BASE_URL ?>/assets/js/login.js"></script>