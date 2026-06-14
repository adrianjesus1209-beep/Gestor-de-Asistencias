// student-dashboard.js
// Funcionalidad para el dashboard del estudiante (materias, inscripciones, retiros)

(function() {
    // Verificar que exista la variable global currentStudent y allSubjectsData
    if (typeof currentStudent === 'undefined' || typeof allSubjectsData === 'undefined') {
        console.error('Faltan datos: currentStudent o allSubjectsData no definidos.');
        return;
    }

    function getSubjectStatus(subjectId) {
        const enrollments = JSON.parse(localStorage.getItem('enrollments') || '[]');
        if (enrollments.some(e => e.student_id === currentStudent.id && e.subject_id === subjectId)) return 'accepted';
        const requests = JSON.parse(localStorage.getItem('enrollment_requests') || '[]');
        const req = requests.find(r => r.student_id === currentStudent.id && r.subject_id === subjectId);
        return req ? req.status : 'none';
    }

    function renderSubjectActions(subjectId, container) {
        const status = getSubjectStatus(subjectId);
        let html = '';
        switch (status) {
            case 'accepted':
                html = `<div class="d-flex gap-2"><span class="badge bg-success w-100 py-2">Inscrito</span><button class="btn btn-outline-danger w-100 withdraw-btn" data-subject-id="${subjectId}">Retirar</button></div>`;
                break;
            case 'pending':
                html = `<button class="btn btn-secondary w-100" disabled>Esperando Confirmación</button>`;
                break;
            case 'rejected':
                html = `<div class="d-flex gap-2"><span class="badge bg-danger w-100 py-2">Solicitud Rechazada</span><button class="btn btn-outline-warning w-100 retry-btn" data-subject-id="${subjectId}">Reintentar</button></div>`;
                break;
            default:
                html = `<button class="btn btn-primary w-100 enroll-btn" data-subject-id="${subjectId}">Inscribirse</button>`;
        }
        container.innerHTML = html;
        container.querySelector('.enroll-btn')?.addEventListener('click', () => createEnrollmentRequest(subjectId));
        container.querySelector('.withdraw-btn')?.addEventListener('click', () => withdrawSubject(subjectId));
        container.querySelector('.retry-btn')?.addEventListener('click', () => retryEnrollmentRequest(subjectId));
    }

    function createEnrollmentRequest(subjectId) {
        const subject = allSubjectsData.find(s => s.id === subjectId);
        if (!subject) return;
        let requests = JSON.parse(localStorage.getItem('enrollment_requests') || '[]');
        const existing = requests.find(r => r.student_id === currentStudent.id && r.subject_id === subjectId);
        if (existing && (existing.status === 'pending' || existing.status === 'accepted')) {
            alert('Ya has solicitado o estás inscrito en esta materia.');
            return;
        }
        const newRequest = {
            requestId: Date.now(),
            student_id: currentStudent.id,
            studentName: currentStudent.name,
            studentCedula: currentStudent.cedula,
            studentCareer: currentStudent.career,
            studentSemester: currentStudent.semester,
            studentPhoto: currentStudent.photo,
            subject_id: subjectId,
            subjectCode: subject.code,
            subjectTitle: subject.title,
            professor_name: subject.professor_name,
            schedule: subject.schedule,
            status: 'pending',
            createdAt: new Date().toISOString()
        };
        requests.push(newRequest);
        localStorage.setItem('enrollment_requests', JSON.stringify(requests));
        const container = document.querySelector(`.subject-card[data-subject-id="${subjectId}"] .subject-actions`);
        if (container) renderSubjectActions(subjectId, container);
        alert('Solicitud enviada al profesor.');
    }

    function withdrawSubject(subjectId) {
        let enrollments = JSON.parse(localStorage.getItem('enrollments') || '[]');
        enrollments = enrollments.filter(e => !(e.student_id === currentStudent.id && e.subject_id === subjectId));
        localStorage.setItem('enrollments', JSON.stringify(enrollments));
        let requests = JSON.parse(localStorage.getItem('enrollment_requests') || '[]');
        requests = requests.filter(r => !(r.student_id === currentStudent.id && r.subject_id === subjectId));
        localStorage.setItem('enrollment_requests', JSON.stringify(requests));
        const container = document.querySelector(`.subject-card[data-subject-id="${subjectId}"] .subject-actions`);
        if (container) renderSubjectActions(subjectId, container);
        alert('Materia retirada.');
    }

    function retryEnrollmentRequest(subjectId) {
        let requests = JSON.parse(localStorage.getItem('enrollment_requests') || '[]');
        requests = requests.filter(r => !(r.student_id === currentStudent.id && r.subject_id === subjectId));
        localStorage.setItem('enrollment_requests', JSON.stringify(requests));
        createEnrollmentRequest(subjectId);
    }

    // Inicializar todas las tarjetas al cargar la página
    document.addEventListener('DOMContentLoaded', () => {
        document.querySelectorAll('.subject-card').forEach(card => {
            const subjectId = parseInt(card.dataset.subjectId);
            const container = card.querySelector('.subject-actions');
            if (container) renderSubjectActions(subjectId, container);
        });
    });
})();