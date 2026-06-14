// attendance.js - Manejo de escáner QR y sesiones de clase
(function() {
    let currentSubject = null;
    let currentSessionId = null;
    let html5QrCode = null;
    let activeSubjectId = null;
    const modal = new bootstrap.Modal(document.getElementById('qrScannerModal'));
    const resultDiv = document.getElementById('qr-result');

    // Funcion para iniciar nueva sesion de clase
    function startClassSession(subjectId, subjectCode, subjectTitle) {
        let sessions = JSON.parse(localStorage.getItem('class_sessions') || '[]');
        // Cerrar cualquier sesion activa previa del mismo profesor (opcional)
        const activeIndex = sessions.findIndex(s => s.status === 'active');
        if (activeIndex !== -1) {
            sessions[activeIndex].status = 'closed';
            sessions[activeIndex].end_time = new Date().toISOString();
        }
        const sessionId = Date.now();
        const newSession = {
            id: sessionId,
            subject_id: subjectId,
            subject_code: subjectCode,
            subject_title: subjectTitle,
            start_time: new Date().toISOString(),
            end_time: null,
            status: 'active'
        };
        sessions.push(newSession);
        localStorage.setItem('class_sessions', JSON.stringify(sessions));
        return sessionId;
    }

    // Funcion para cerrar la sesion actual
    function closeClassSession(subjectId) {
        if (!currentSessionId) return;
        let sessions = JSON.parse(localStorage.getItem('class_sessions') || '[]');
        const index = sessions.findIndex(s => s.id === currentSessionId);
        if (index !== -1) {
            sessions[index].end_time = new Date().toISOString();
            sessions[index].status = 'closed';
            localStorage.setItem('class_sessions', JSON.stringify(sessions));
        }
        if (html5QrCode && html5QrCode.isScanning) {
            html5QrCode.stop();
        }
        modal.hide();
        alert('Clase cerrada exitosamente.');
        // Restaurar UI
        const cardDiv = document.querySelector(`.col-md-6[data-subject-id="${subjectId}"], .col-lg-4[data-subject-id="${subjectId}"]`);
        if (cardDiv) {
            const startBtn = cardDiv.querySelector('.start-class-btn');
            const endContainer = cardDiv.querySelector('.end-class-container');
            const card = cardDiv.querySelector('.card');
            if (startBtn) startBtn.style.display = 'inline-block';
            if (endContainer) endContainer.style.display = 'none';
            if (card) card.classList.remove('border-danger');
        }
        currentSessionId = null;
        currentSubject = null;
        activeSubjectId = null;
    }

    // Registrar asistencia
    function registerAttendance(studentToken, sessionId) {
        const studentIdMatch = studentToken.match(/\d+$/);
        const studentId = studentIdMatch ? parseInt(studentIdMatch[0]) : null;
        if (!studentId) {
            resultDiv.innerHTML = '<div class="alert alert-danger">Token inválido. No se pudo identificar al estudiante.</div>';
            resultDiv.classList.remove('d-none');
            return false;
        }
        let attendance = JSON.parse(localStorage.getItem('attendance_records') || '[]');
        const already = attendance.some(a => a.session_id === sessionId && a.student_id === studentId);
        if (already) {
            resultDiv.innerHTML = '<div class="alert alert-warning">Este estudiante ya ha registrado asistencia.</div>';
            resultDiv.classList.remove('d-none');
            return false;
        }
        attendance.push({
            id: Date.now(),
            session_id: sessionId,
            student_id: studentId,
            timestamp: new Date().toISOString()
        });
        localStorage.setItem('attendance_records', JSON.stringify(attendance));
        resultDiv.innerHTML = '<div class="alert alert-success">Asistencia registrada correctamente.</div>';
        resultDiv.classList.remove('d-none');
        setTimeout(() => resultDiv.classList.add('d-none'), 2000);
        return true;
    }

    // Iniciar escáner
    function startScanner() {
        if (html5QrCode === null) {
            html5QrCode = new Html5Qrcode("qr-reader");
        }
        const config = { fps: 10, qrbox: { width: 250, height: 250 } };
        html5QrCode.start({ facingMode: "environment" }, config, onScanSuccess, onScanFailure);
    }

    function onScanSuccess(decodedText) {
        if (html5QrCode && html5QrCode.isScanning) html5QrCode.stop();
        registerAttendance(decodedText, currentSessionId);
        setTimeout(() => {
            if (currentSessionId && html5QrCode) {
                html5QrCode.start({ facingMode: "environment" }, { fps: 10, qrbox: { width: 250, height: 250 } }, onScanSuccess, onScanFailure);
            }
        }, 3000);
    }

    function onScanFailure(error) {
        // silencioso
    }

    // Funcion para reanudar el escaneo sin crear nueva sesion
    function resumeScan(subjectId) {
        if (!currentSessionId || activeSubjectId !== subjectId) {
            alert('No hay una clase activa para esta materia.');
            return;
        }
        // Detener el escaner si esta corriendo
        if (html5QrCode && html5QrCode.isScanning) {
            html5QrCode.stop();
        }
        modal.show();
        modal._element.addEventListener('shown.bs.modal', function onShown() {
            startScanner();
            modal._element.removeEventListener('shown.bs.modal', onShown);
        });
    }

    // Event listeners para botones "Comenzar clase"
    document.querySelectorAll('.start-class-btn').forEach(btn => {
        btn.addEventListener('click', (e) => {
            const subjectId = parseInt(btn.dataset.subjectId);
            const subjectCode = btn.dataset.subjectCode;
            const subjectTitle = btn.dataset.subjectTitle;
            // Verificar si ya hay una clase activa
            if (activeSubjectId && activeSubjectId !== subjectId) {
                alert('Ya tienes una clase activa. Debes cerrarla antes de iniciar otra.');
                return;
            }
            currentSubject = { id: subjectId, code: subjectCode, title: subjectTitle };
            currentSessionId = startClassSession(subjectId, subjectCode, subjectTitle);
            activeSubjectId = subjectId;
            // Cambiar UI
            const cardDiv = btn.closest('.col-md-6, .col-lg-4');
            if (cardDiv) {
                const startBtn = cardDiv.querySelector('.start-class-btn');
                const endContainer = cardDiv.querySelector('.end-class-container');
                const card = cardDiv.querySelector('.card');
                if (startBtn) startBtn.style.display = 'none';
                if (endContainer) endContainer.style.display = 'block';
                if (card) card.classList.add('border-danger');
            }
            modal.show();
            modal._element.addEventListener('shown.bs.modal', function onShown() {
                startScanner();
                modal._element.removeEventListener('shown.bs.modal', onShown);
            });
        });
    });

    // Event listener para botones "Reanudar escaneo"
    document.querySelectorAll('.resume-scan-btn').forEach(btn => {
        btn.addEventListener('click', (e) => {
            const subjectId = parseInt(btn.dataset.subjectId);
            resumeScan(subjectId);
        });
    });

    // Event listener para botones "Cerrar clase" (delegación)
    document.addEventListener('click', (e) => {
        const endBtn = e.target.closest('.end-class-btn');
        if (endBtn) {
            e.preventDefault();
            const subjectId = parseInt(endBtn.dataset.subjectId);
            if (confirm('¿Está seguro de cerrar la clase? Los estudiantes no podrán seguir registrando asistencia.')) {
                closeClassSession(subjectId);
            }
        }
    });

    // Al cerrar el modal, detener escaner 
    modal._element.addEventListener('hidden.bs.modal', () => {
        if (html5QrCode && html5QrCode.isScanning) {
            html5QrCode.stop();
        }
    });

    // Restaurar estado UI al cargar la pagina si hay sesiones activas
    function restoreActiveClassUI() {
        const sessions = JSON.parse(localStorage.getItem('class_sessions') || '[]');
        const activeSession = sessions.find(s => s.status === 'active');
        if (activeSession) {
            currentSessionId = activeSession.id;
            activeSubjectId = activeSession.subject_id;
            const subjectId = activeSession.subject_id;
            const cardDiv = document.querySelector(`.col-md-6[data-subject-id="${subjectId}"], .col-lg-4[data-subject-id="${subjectId}"]`);
            if (cardDiv) {
                const startBtn = cardDiv.querySelector('.start-class-btn');
                const endContainer = cardDiv.querySelector('.end-class-container');
                const card = cardDiv.querySelector('.card');
                if (startBtn) startBtn.style.display = 'none';
                if (endContainer) endContainer.style.display = 'block';
                if (card) card.classList.add('border-danger');
            }
        }
    }
    restoreActiveClassUI();
})();