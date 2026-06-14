// requests.js
// Manejo de solicitudes de inscripción (vista del profesor)
function loadRequests() {
    const requests = JSON.parse(localStorage.getItem('enrollment_requests') || '[]');
    const pending = requests.filter(r => r.status === 'pending');
    const container = document.getElementById('requests-container');

    if (pending.length === 0) {
        container.innerHTML = '<div class="col-12 text-center text-muted">No hay solicitudes pendientes.</div>';
        return;
    }

    container.innerHTML = pending.map(req => `
        <div class="col-md-6 col-lg-4" data-request-id="${req.requestId}">
            <div class="card h-100 shadow-sm">
                <div class="card-body">
                    <div class="d-flex align-items-center gap-3 mb-3">
                        <img src="${req.studentPhoto}" class="rounded-circle" width="60" height="60" style="object-fit: cover;">
                        <div>
                            <h5 class="mb-0">${escapeHtml(req.studentName)}</h5>
                            <small class="text-muted">${escapeHtml(req.studentCedula)}</small>
                        </div>
                    </div>
                    <p><strong>Materia:</strong> ${escapeHtml(req.subjectCode)} - ${escapeHtml(req.subjectTitle)}</p>
                    <p><strong>Carrera:</strong> ${escapeHtml(req.studentCareer)} | <strong>Semestre:</strong> ${req.studentSemester}°</p>
                    <p><strong>Profesor:</strong> ${escapeHtml(req.professor_name)}</p>
                    <div class="d-flex gap-2 mt-3">
                        <button class="btn btn-success accept-request">Aceptar</button>
                        <button class="btn btn-danger reject-request">Rechazar</button>
                    </div>
                </div>
            </div>
        </div>
    `).join('');

    document.querySelectorAll('.accept-request').forEach(btn => {
        btn.addEventListener('click', (e) => {
            const card = btn.closest('[data-request-id]');
            const requestId = parseInt(card.dataset.requestId);
            updateRequestStatus(requestId, 'accepted');
        });
    });
    document.querySelectorAll('.reject-request').forEach(btn => {
        btn.addEventListener('click', (e) => {
            const card = btn.closest('[data-request-id]');
            const requestId = parseInt(card.dataset.requestId);
            updateRequestStatus(requestId, 'rejected');
        });
    });
}

function updateRequestStatus(requestId, newStatus) {
    let requests = JSON.parse(localStorage.getItem('enrollment_requests') || '[]');
    let enrollments = JSON.parse(localStorage.getItem('enrollments') || '[]');
    const index = requests.findIndex(r => r.requestId === requestId);
    if (index !== -1) {
        requests[index].status = newStatus;
        if (newStatus === 'accepted') {
            const req = requests[index];
            enrollments.push({
                enrollment_id: Date.now(),
                student_id: req.student_id,
                subject_id: req.subject_id,
                subject_code: req.subjectCode,
                subject_title: req.subjectTitle,
                professor_name: req.professor_name,
                schedule: req.schedule
            });
        }
        localStorage.setItem('enrollment_requests', JSON.stringify(requests));
        localStorage.setItem('enrollments', JSON.stringify(enrollments));
        loadRequests(); // recargar lista
        alert(`Solicitud ${newStatus === 'accepted' ? 'aceptada' : 'rechazada'}.`);
    }
}

function escapeHtml(str) {
    if (!str) return '';
    return str.replace(/[&<>]/g, function(m) {
        if (m === '&') return '&amp;';
        if (m === '<') return '&lt;';
        if (m === '>') return '&gt;';
        return m;
    });
}

// Inicializar al cargar la página
document.addEventListener('DOMContentLoaded', () => {
    loadRequests();
    const refreshBtn = document.getElementById('refreshRequestsBtn');
    if (refreshBtn) refreshBtn.addEventListener('click', loadRequests);
});