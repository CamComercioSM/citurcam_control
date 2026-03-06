<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Tablero de Citas - Citurcam</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="css/tablero-citas.css">
    <link rel="stylesheet" href="css/modal-historial-citas.css">
    <link rel="icon" href="https://cdnsicam.net/img/ccsm/favicon.ico" type="image/x-icon">
    <script src="js/funciones.js"></script>
</head>
<body>
    <div class="layout">

        <!-- HEADER SUPERIOR -->
        <div class="header">
            <div class="header-titulo">
                Tablero de Citas del Día
            </div>
            <div class="header-derecha">
                <div id="contador">Cargando...</div>
                <div id="ultima-actualizacion">Última actualización: --</div>
            </div>
        </div>

        <!-- BARRA DE FILTROS + RESUMEN -->
        <div class="barra-superior">
            <div class="filtro-bloque">
                <label for="filtroServicio">Filtrar por servicio:</label>
                <select id="filtroServicio">
                    <option value="">Todos los servicios</option>
                    <!-- Se llena dinámicamente -->
                </select>
            </div>
            <div class="filtro-bloque">
                <label for="filtroFecha">Fecha:</label>
                <select id="filtroFecha">
                    <option value="">Cargando...</option>
                </select>
            </div>


            <div class="resumen-estadisticas">
                <div class="chip-resumen">
                    Total: <span id="res-total" class="valor">0</span>
                </div>
                <div class="chip-resumen">
                    Sin asignar: <span id="res-sin-asignar" class="valor">0</span>
                </div>
                <div class="chip-resumen">
                    Pendientes: <span id="res-pendiente" class="valor">0</span>
                </div>
                <div class="chip-resumen">
                    Atendidas: <span id="res-atendido" class="valor">0</span>
                </div>
                <div class="chip-resumen">
                    Canceladas: <span id="res-cancelado" class="valor">0</span>
                </div>
                <div class="chip-resumen">
                    Virtuales: <span id="res-virtual" class="valor">0</span>
                </div>
                <div class="chip-resumen">
                    Presenciales: <span id="res-presencial" class="valor">0</span>
                </div>
                <div class="chip-resumen">
                    Prom. Asig→Atención: <span id="res-prom-asig-atenc" class="valor">—</span>
                </div>
                <div class="chip-resumen">
                    Prom. Atención→Fin: <span id="res-prom-atenc-fin" class="valor">—</span>
                </div>
                <div class="chip-resumen">
                    Prom. Cita→Fin: <span id="res-prom-cita-fin" class="valor">—</span>
                </div>
            </div>
        </div>

        <!-- CONTENIDO PRINCIPAL -->
        <div class="contenido">
            <div id="contenedorTarjetas" class="contenedor-tarjetas">
                <p class="loading-message">Cargando información...</p>
            </div>
            <div id="mensajeError" class="mensaje-error"></div>
        </div>
    </div>

    <!-- Modal Historial de Cita -->
    <div id="modal-historial" class="modal-backdrop modal-historial" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-contenido">
                <button
                    type="button"
                    class="modal-cerrar"
                    aria-label="Cerrar historial">
                    ✕
                </button>

                <h2 class="modal-titulo">Historial de la cita</h2>

                <!-- 👇 ESTE es el elemento que tu JS intenta usar: modal-subtitulo-cita -->
                <div id="modal-subtitulo-cita" class="modal-subtitulo">
                    <!-- aquí se llena con: Nombre · Servicio · Hora -->
                </div>

                <!-- 👇 ESTE es el UL que tu JS llena: timeline-estados -->
                <ul id="timeline-estados" class="timeline-estados">
                    <!-- ítems del timeline generados por JS -->
                </ul>
            </div>
        </div>
    </div>


    <script src="js/tablero-citas.js"></script>
    <script>
        // ================================
        // CONTADOR VISUAL
        // ================================
        setInterval(() => {
            segundosRestantes--;
            if (segundosRestantes < 0) {
                segundosRestantes = 0;
            }
            document.getElementById('contador').textContent =
                'Actualizando en ' + segundosRestantes + ' segundos...';
        }, 1000);


        document.getElementById('filtroServicio').addEventListener('change', () => {
            if (ultimoResultado) {
                renderTablero(ultimoResultado);
            }
        });

        document.getElementById("filtroFecha").addEventListener("change", function() {
            // Reemplaza la fecha global y recarga el tablero
            cargarTableroCitasPorFecha(this.value);
        });
        // Delegación de eventos para todos los botones .btn-historial
        document.addEventListener("click", function(e) {
            const btn = e.target.closest(".btn-historial");
            if (btn) {
                const citaId = btn.getAttribute("data-cita-id");
                if (citaId) {
                    abrirHistorialCita(citaId);
                }
            }

            // Cerrar modal al hacer clic en backdrop
            if (e.target.classList.contains("modal-backdrop")) {
                cerrarHistorialCita();
            }

            // Botón cerrar
            if (e.target.classList.contains("modal-cerrar")) {
                cerrarHistorialCita();
            }
        });

        // Cerrar con ESC
        document.addEventListener("keydown", function(e) {
            if (e.key === "Escape") {
                cerrarHistorialCita();
            }
        });



        // ================================
        // ARRANQUE Y AUTO-REFRESCO
        // ================================
        cargarFechasHabilitadas();
        cargarTableroCitas();
        setInterval(() => cargarTableroCitas(document.getElementById("filtroFecha").value), INTERVALO_SEGUNDOS * 1000);
    </script>

</body>

</html>