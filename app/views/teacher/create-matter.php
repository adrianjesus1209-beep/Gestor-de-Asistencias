<?php
// create-matter.php - Formulario para que el profesor agregue una materia
// Por ahora es solo maquetación; los datos se enviarán por POST a store-matter
?>
<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-lg-8">
            <div class="card shadow-sm border-0 rounded-4">
                <div class="card-header bg-white fw-bold py-3">
                    <i class="bi bi-plus-circle-fill me-2 text-primary"></i> Agregar Nueva Materia
                </div>
                <div class="card-body p-4">
                    <form id="createSubjectForm" action="index.php?route=store-matter" method="POST">
                        <!-- Datos básicos -->
                        <div class="row g-3">
                            <div class="col-md-4">
                                <label class="form-label fw-semibold">Código de la materia</label>
                                <input type="text" name="code" class="form-control" required placeholder="Ej: SIS-401">
                            </div>
                            <div class="col-md-8">
                                <label class="form-label fw-semibold">Título</label>
                                <input type="text" name="title" class="form-control" required placeholder="Bases de Datos Avanzadas">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Carrera</label>
                                <select name="career_id" class="form-select" required>
                                    <option value="">Seleccione</option>
                                    <option value="1">Ingeniería de Sistemas</option>
                                    <option value="2">Telecomunicaciones</option>
                                    <option value="3">Ingeniería Mecánica</option>
                                    <option value="4">Administración y Gestión Municipal</option>
                                    <option value="5">Ingeniería en Electrónica</option>
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Semestre</label>
                                <select name="semester" class="form-select" required>
                                    <option value="">Seleccione</option>
                                    <?php for ($i = 1; $i <= 9; $i++): ?>
                                        <option value="<?= $i ?>"><?= $i ?>° Semestre</option>
                                    <?php endfor; ?>
                                </select>
                            </div>
                        </div>

                        <!-- Horario: múltiples días -->
                        <div class="mt-4">
                            <label class="form-label fw-semibold">Horario de clases</label>
                            <div id="schedule-container">
                                <div class="row g-2 mb-2 schedule-entry">
                                    <div class="col-md-3">
                                        <select name="schedule[0][day]" class="form-select" required>
                                            <option value="">Día</option>
                                            <option value="Lunes">Lunes</option>
                                            <option value="Martes">Martes</option>
                                            <option value="Miércoles">Miércoles</option>
                                            <option value="Jueves">Jueves</option>
                                            <option value="Viernes">Viernes</option>
                                            <option value="Sábado">Sábado</option>
                                        </select>
                                    </div>
                                    <div class="col-md-3">
                                        <input type="time" name="schedule[0][start]" class="form-control" required>
                                    </div>
                                    <div class="col-md-3">
                                        <input type="time" name="schedule[0][end]" class="form-control" required>
                                    </div>
                                    <div class="col-md-3">
                                        <button type="button" class="btn btn-outline-danger remove-schedule w-100">Eliminar</button>
                                    </div>
                                </div>
                            </div>
                            <button type="button" id="add-schedule" class="btn btn-sm btn-secondary mt-2">
                                <i class="bi bi-plus-lg"></i> Agregar otro día
                            </button>
                        </div>

                        <div class="mt-5 d-flex justify-content-end gap-2">
                            <a href="index.php?route=dashboard" class="btn btn-secondary px-4">Cancelar</a>
                            <button type="submit" class="btn btn-primary px-5">Crear Materia</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="<?= BASE_URL ?>/js/matter.js"></script>