<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-12 col-lg-8">
            <div class="card shadow border-0 rounded-4">
                <div class="card-body p-4 p-md-5">
                    <div class="text-center mb-4">
                        <img src="<?= BASE_URL ?>/assets/img/logos/logo_unefa.png" alt="UNEFA"  width="90" class="mb-3">
                        <h3 class="fw-bold mb-2" style="color: var(--color-principal);">Recuperación de Contraseña</h3>
                        <p class="text-muted mb-0">Verifique su identidad para actualizar la contraseña</p>
                    </div>
                    <form>
                        <div class="mb-3">
                            <label class="form-label fw-semibold">Cédula</label>
                            <input type="text" class="form-control" placeholder="Ingrese su cédula" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-semibold">
                                Pregunta de Seguridad 1
                            </label>
                            <select class="form-select" required>
                                <option value="" selected disabled>Seleccione una pregunta</option>
                                <option>¿Cuál es el nombre de tu primera mascota?</option>
                                <option>¿Cuál es el apellido de soltera de tu madre?</option>
                                <option>¿En qué mes naciste?</option>
                                <option>¿Cuál es tu comida favorita?</option>
                                <option>¿Cuál es el primer nombre de tu abuelo?</option>
                                <option>¿Cuál es el segundo nombre de tu padre?</option>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-semibold">Respuesta 1</label>
                            <input type="text" class="form-control" placeholder="Ingrese su respuesta" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-semibold">Pregunta de Seguridad 2</label>
                            <select class="form-select" required>
                                <option value="" selected disabled>Seleccione una pregunta</option>
                                <option>¿Cuál es el nombre de tu primera mascota?</option>
                                <option>¿Cuál es el apellido de soltera de tu madre?</option>
                                <option>¿En qué mes naciste?</option>
                                <option>¿Cuál es tu comida favorita?</option>
                                <option>¿Cuál es el primer nombre de tu abuelo?</option>
                                <option>¿Cuál es el segundo nombre de tu padre?</option>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-semibold">Respuesta 2</label>
                            <input type="text" class="form-control" placeholder="Ingrese su respuesta" required>
                        </div>
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label fw-semibold">Nueva Contraseña</label>
                                <input type="password" class="form-control" placeholder="Ingrese la nueva contraseña" required>
                            </div>
                            <div class="col-md-6 mb-4">
                                <label class="form-label fw-semibold">Confirmar Contraseña</label>
                                <input type="password" class="form-control" placeholder="Repita la nueva contraseña" required >
                            </div>
                        </div>
                        <div class="d-grid">
                            <button type="submit" class="btn boton-primario-personalizado py-2 fw-bold">Actualizar Contraseña</button>
                        </div>
                    </form>
                    <div class="text-center mt-4">
                        <a href="index.php?route=login" class="text-decoration-none small text-muted">
                            <i class="bi bi-arrow-left"></i>
                            Volver al inicio de sesión
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>