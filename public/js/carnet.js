// public/js/carnet.js
document.addEventListener("DOMContentLoaded", function() {
    // El contenedor del QR debe existir
    const qrContainer = document.getElementById("qrcode-front");
    if (qrContainer) {
        new QRCode(qrContainer, {
            text: qrContainer.dataset.token, // tomamos el token desde un atributo data
            width: 150,
            height: 150,
            colorDark: "#1e293b",
            colorLight: "#ffffff",
            correctLevel: QRCode.CorrectLevel.H
        });
    }
});

function capturarCarnetCompleto() {
    const front = document.getElementById('carnet-front');
    const back = document.getElementById('carnet-back');
    if (!back || !back.querySelector('img')) {
        return html2canvas(front, { scale: 3, backgroundColor: null });
    }
    return Promise.all([
        html2canvas(front, { scale: 3, backgroundColor: null }),
        html2canvas(back, { scale: 3, backgroundColor: null })
    ]).then(([frontCanvas, backCanvas]) => {
        const finalCanvas = document.createElement('canvas');
        const frontHeight = frontCanvas.height;
        const backHeight = backCanvas.height;
        const width = Math.max(frontCanvas.width, backCanvas.width);
        finalCanvas.width = width;
        finalCanvas.height = frontHeight + backHeight;
        const ctx = finalCanvas.getContext('2d');
        ctx.fillStyle = '#ffffff';
        ctx.fillRect(0, 0, width, finalCanvas.height);
        ctx.drawImage(frontCanvas, (width - frontCanvas.width) / 2, 0);
        ctx.drawImage(backCanvas, (width - backCanvas.width) / 2, frontHeight);
        return finalCanvas;
    });
}

// Asignar eventos despues de que el DOM este listo
document.addEventListener("DOMContentLoaded", function() {
    const btnDescargar = document.getElementById('btnDescargar');
    const btnImprimir = document.getElementById('btnImprimir');
    if (btnDescargar) {
        btnDescargar.addEventListener('click', function() {
            capturarCarnetCompleto().then(canvas => {
                const link = document.createElement('a');
                link.download = 'carnet_unefa.png';
                link.href = canvas.toDataURL();
                link.click();
            }).catch(err => console.error('Error:', err));
        });
    }
    if (btnImprimir) {
        btnImprimir.addEventListener('click', function() {
            capturarCarnetCompleto().then(canvas => {
                const printWindow = window.open('', '_blank');
                printWindow.document.write(`
                    <html>
                    <head>
                        <title>Imprimir Carnet UNEFA</title>
                        <style>
                            body { margin: 0; padding: 20px; background: #eee; text-align: center; }
                            .carnet-page {
                                margin: 0 auto 20px auto;
                                max-width: 100%;
                                page-break-after: always;
                                break-after: page;
                                background: white;
                                box-shadow: 0 0 10px rgba(0,0,0,0.1);
                            }
                            img { width: 100%; height: auto; display: block; }
                            @media print {
                                body { background: white; margin: 0; padding: 0; }
                                .carnet-page { margin: 0; box-shadow: none; page-break-after: always; break-after: page; }
                            }
                        </style>
                    </head>
                    <body>
                        <div class="carnet-page">
                            <img src="${canvas.toDataURL()}" />
                        </div>
                        <script>
                            window.onload = () => { window.print(); window.close(); };
                        <\/script>
                    </body>
                    </html>
                `);
                printWindow.document.close();
            });
        });
    }
});