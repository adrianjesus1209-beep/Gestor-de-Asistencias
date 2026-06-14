<div class="container mt-5">
    <div class="card shadow">
        <div class="card-header bg-primary text-white">
            <h5 class="mb-0">Configuración de Preguntas de Seguridad</h5>
        </div>
        <div class="card-body">
            <form action="index.php?route=login" method="POST">
                <div class="mb-3">
                    <label for="pregunta1" class="form-label">Primera pregunta de seguridad:</label>
                    <select class="form-select" id="pregunta1" name="pregunta1" required>
                        <option value="" selected disabled>Selecciona una pregunta...</option>
                        <option value="1">¿Cuál es el nombre de tu primera mascota?</option>
                        <option value="2">¿Cuál es el apellido de soltera de tu madre?</option>
                        <option value="3">¿En qué mes naciste?</option>
                        <option value="4">¿Cuál es tu comida favorita?</option>
                        <option value="5">¿Cual es el primer nombre de tu abuelo?</option>
                        <option value="6">¿Cuál es el segundo nombre de tu padre?</option>
                    </select>
                </div>
                <div class="mb-3">
                    <input type="text" class="form-control" name="respuesta1" placeholder="Tu respuesta..." required>
                </div>

                <hr>

                <div class="mb-3">
                    <label for="pregunta2" class="form-label">Segunda pregunta de seguridad:</label>
                    <select class="form-select" id="pregunta2" name="pregunta2" required>
                        <option value="" selected disabled>Selecciona una pregunta...</option>
                        <option value="1">¿Cuál es el nombre de tu primera mascota?</option>
                        <option value="2">¿Cuál es el apellido de soltera de tu madre?</option>
                        <option value="3">¿En qué mes naciste?</option>
                        <option value="4">¿Cuál es tu comida favorita?</option>
                        <option value="5">¿Cual es el primer nombre de tu abuelo?</option>
                        <option value="6">¿Cuál es el segundo nombre de tu padre?</option>
                    </select>
                </div>
                <div class="mb-3">
                    <input type="text" class="form-control" name="respuesta2" placeholder="Tu respuesta..." required>
                </div>

                <div class="d-grid gap-2 mt-4">
                    <a href="index.php?route=login" 
                        class="btn boton-primario-personalizado py-2 fw-bold text-center">
                        Registrar
                    </a>
                </div>
            </form>
        </div>
    </div>
</div>