document.addEventListener('DOMContentLoaded', function() {
        let scheduleIndex = 1;
        const container = document.getElementById('schedule-container');
        const addBtn = document.getElementById('add-schedule');

        function attachRemoveEvent(btn) {
            btn.addEventListener('click', function() {
                this.closest('.schedule-entry').remove();
            });
        }

        addBtn.addEventListener('click', function() {
            const newEntry = document.createElement('div');
            newEntry.className = 'row g-2 mb-2 schedule-entry';
            newEntry.innerHTML = `
                <div class="col-md-3">
                    <select name="schedule[${scheduleIndex}][day]" class="form-select" required>
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
                    <input type="time" name="schedule[${scheduleIndex}][start]" class="form-control" required>
                </div>
                <div class="col-md-3">
                    <input type="time" name="schedule[${scheduleIndex}][end]" class="form-control" required>
                </div>
                <div class="col-md-3">
                    <button type="button" class="btn btn-outline-danger remove-schedule w-100">Eliminar</button>
                </div>
            `;
            container.appendChild(newEntry);
            attachRemoveEvent(newEntry.querySelector('.remove-schedule'));
            scheduleIndex++;
        });

        document.querySelectorAll('.remove-schedule').forEach(btn => attachRemoveEvent(btn));
    });