    <footer class="pie-pagina-moderno text-center">
        <div class="container pt-5">
            <div class="mb-5">
                <img src="<?= BASE_URL ?>/assets/img/logos/logo_unefa.png" class="rounded-circle mb-4 logo-footer">
                <h4 class="fw-bold text-white mb-2">Universidad Nacional Experimental Politecnica <br class="d-none d-md-block">de la Fuerza Armada Nacional Bolivariana</h4>
                <p class="text-white-50 fs-5">RIF: G-20006297-5</p>
            </div>
            
            <div class="d-flex justify-content-center gap-4 mb-5">
                <a href="https://x.com/Unefa_VEN?t=FhK2uslLRmCrIa9sjQIEEA&s=09" target="_blank" class="icono-footer-red">
                    <img src="<?= BASE_URL ?>/assets/img/redes/X-Twitter.webp" alt="X" class="icono-red-footer">
                </a>
                <a href="https://www.instagram.com/unefa_ve?igsh=MXJvcjFkMXJ5Z3NzMg%3D%3D" target="_blank" class="icono-footer-red">
                    <img src="<?= BASE_URL ?>/assets/img/redes/Instagram.webp" alt="Instagram" class="icono-red-footer">
                </a>
                <a href="https://www.facebook.com/share/1BKuAut1dg/" target="_blank" class="icono-footer-red">
                    <img src="<?= BASE_URL ?>/assets/img/redes/Facebook.webp" alt="Facebook" class="icono-red-footer">
                </a>
                <a href="https://www.youtube.com/channel/UCU1YFZgV-ENQkfHRspsK9nA" target="_blank" class="icono-footer-red">
                    <img src="<?= BASE_URL ?>/assets/img/redes/Youtube.webp" alt="YouTube" class="icono-red-footer">
                </a>
                <a href="https://www.tiktok.com/@unefa_ve?_t=8iwcWCLFEAA&_r=1" target="_blank" class="icono-footer-red">
                    <img src="<?= BASE_URL ?>/assets/img/redes/Tiktok.webp" alt="TikTok" class="icono-red-footer">
                </a>
            </div>
            
            <hr class="border-light opacity-10 my-4 w-75 mx-auto">
            <p class="small text-white-50 mb-0 px-3">&copy; 2026 UNEFA. Excelencia Educativa Abierta al Pueblo.</p>
        </div>
    </footer>

    <script>
        document.querySelectorAll('input[name="playlist"]').forEach(radio => {
            radio.addEventListener('change', () => {
                document.querySelectorAll('video').forEach(vid => {
                    vid.pause();
                });
            });
        });

        // ==========================================
        // SISTEMA DE DESTRUCCIÓN ACTIVA DE SESIÓN Y CACHÉ
        // ==========================================

        // 1. Forzar recarga si se intenta entrar dándole "Atrás" desde una página en caché
        window.addEventListener("pageshow", function(event) {
            if (event.persisted) {
                window.location.reload(); 
            }
        });

        // 2. Temporizador de Auto-Cierre por inactividad
        let inactivityTimer;
        // 15 minutos en milisegundos (900,000)
        const TIMEOUT_LIMIT = 900000; 
        
        function resetTimer() {
            clearTimeout(inactivityTimer);
            inactivityTimer = setTimeout(() => {
                // Para evitar loop en login, comprobar si estamos logeados 
                // Un indicaba es si existe el botón "Cerrar Sesión" en la barra de nav.
                if (document.querySelector('a[href="index.php?logout"]')) {
                    window.location.href = 'index.php?logout=true&inactivity=1';
                }
            }, TIMEOUT_LIMIT);
        }

        ['mousedown', 'mousemove', 'keypress', 'scroll', 'touchstart'].forEach(evt => 
            document.addEventListener(evt, resetTimer, true)
        );
        resetTimer();
    </script>
    <script src="<?= BASE_URL ?>/bootstrap/js/bootstrap.bundle.min.js"></script>
</body>
</html>
