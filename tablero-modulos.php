<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Monitoreo de Módulos de Atención - Citurcam</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <style>
        :root {
            --bg-body: #f4f4f9;
            --bg-header: #ffffff;
            --bg-barra: #ffffff;
            --bg-tarjeta: #ffffff;
            --border-tarjeta: #dee2e6;
            --texto-principal: #212529;
            --texto-secundario: #6c757d;
            --estado-atendiendo: #28a745;
            --estado-ocupado: #fd7e14;
            --estado-disponible: #0d6efd;
            --estado-inactivo: #6c757d;
            --chip-asesoria: #0d6efd;
            --chip-ventanilla: #6610f2;
            --chip-estudio: #20c997;
            --chip-otros: #6c757d;
        }

        * {
            box-sizing: border-box;
        }

        html, body {
            margin: 0;
            padding: 0;
            height: 100vh;
            width: 100vw;
            overflow: hidden;
            font-family: Arial, sans-serif;
            background: var(--bg-body);
            color: var(--texto-principal);
        }

        .layout {
            display: flex;
            flex-direction: column;
            height: 100vh;
        }

        /* HEADER SUPERIOR */
        .header {
            padding: 10px 20px;
            background: linear-gradient(90deg, #ffffff, #e7f1ff);
            border-bottom: 1px solid #dde2eb;
            display: flex;
            align-items: center;
            justify-content: space-between;
        }

        .header-titulo {
            font-size: 1.4rem;
            font-weight: bold;
            color: #004085;
        }

        .header-subtitulo {
            font-size: 0.9rem;
            color: var(--texto-secundario);
        }

        .header-izquierda {
            display: flex;
            flex-direction: column;
            gap: 3px;
        }

        .header-derecha {
            display: flex;
            flex-direction: column;
            align-items: flex-end;
            gap: 2px;
            font-size: 0.85rem;
            color: var(--texto-secundario);
        }

        #contador-recarga-mensaje {
            font-weight: bold;
            color: #0056b3;
        }

        /* BARRA SUPERIOR: FILTRO + KPIs */
        .barra-superior {
            padding: 10px 20px;
            background: var(--bg-barra);
            border-bottom: 1px solid #dde2eb;
            display: flex;
            flex-wrap: wrap;
            align-items: center;
            gap: 15px;
        }

        .filtro-bloque {
            display: flex;
            align-items: center;
            gap: 8px;
            font-size: 0.9rem;
            color: var(--texto-secundario);
        }

        .filtro-bloque select {
            padding: 5px 10px;
            border-radius: 4px;
            border: 1px solid #ced4da;
            background: #ffffff;
            color: var(--texto-principal);
            font-size: 0.9rem;
        }

        .resumen-estadisticas {
            display: flex;
            flex-wrap: wrap;
            gap: 8px;
            margin-left: auto;
        }

        .chip-resumen {
            background: #ffffff;
            border-radius: 12px;
            padding: 4px 10px;
            font-size: 0.8rem;
            display: flex;
            align-items: center;
            gap: 4px;
            border: 1px solid #dee2e6;
            white-space: nowrap;
        }

        .chip-resumen .etiqueta {
            color: var(--texto-secundario);
        }

        .chip-resumen .valor {
            font-weight: bold;
        }

        .chip-resumen.ocupacion .valor {
            color: #0d6efd;
        }

        .chip-resumen.atendiendo .valor {
            color: var(--estado-atendiendo);
        }

        /* CONTENIDO PRINCIPAL */
        .contenido {
            flex: 1;
            overflow-y: auto;
            padding: 10px 20px 18px;
        }

        .categoria-bloque {
            margin-bottom: 18px;
        }

        .categoria-titulo {
            font-size: 1rem;
            font-weight: bold;
            margin: 0 0 8px;
            color: #343a40;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .categoria-titulo span.badge {
            font-size: 0.7rem;
            border-radius: 999px;
            padding: 2px 8px;
            color: #fff;
        }

        .badge-asesoria {
            background: var(--chip-asesoria);
        }
        .badge-ventanilla {
            background: var(--chip-ventanilla);
        }
        .badge-estudio {
            background: var(--chip-estudio);
        }
        .badge-otros {
            background: var(--chip-otros);
        }

        .contenedor-tarjetas {
            display: flex;
            flex-wrap: wrap;
            gap: 12px;
            justify-content: flex-start;
        }

        .tarjeta {
            background-color: var(--bg-tarjeta);
            border-radius: 10px;
            border: 1px solid var(--border-tarjeta);
            box-shadow: 0 2px 4px rgba(0,0,0,0.05);
            padding: 10px 12px;
            width: 260px;
            display: flex;
            flex-direction: column;
            gap: 6px;
            transition: transform 0.2s ease, box-shadow 0.2s ease;
        }

        .tarjeta:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 8px rgba(0,0,0,0.08);
        }

        .tarjeta-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 6px;
        }

        .titulo-modulo {
            margin: 0;
            font-size: 0.95rem;
            font-weight: bold;
            color: #212529;
        }

        .codigo-modulo {
            font-size: 0.7rem;
            color: var(--texto-secundario);
        }

        .estado-chip {
            font-size: 0.75rem;
            font-weight: bold;
            padding: 2px 8px;
            border-radius: 999px;
            border: 1px solid transparent;
        }

        .estado-atendiendo-chip {
            color: var(--estado-atendiendo);
            border-color: var(--estado-atendiendo);
            background: #e8f8ee;
        }

        .estado-ocupado-chip {
            color: var(--estado-ocupado);
            border-color: var(--estado-ocupado);
            background: #fff4e6;
        }

        .estado-disponible-chip {
            color: var(--estado-disponible);
            border-color: var(--estado-disponible);
            background: #e7f1ff;
        }

        .estado-inactivo-chip {
            color: var(--estado-inactivo);
            border-color: var(--estado-inactivo);
            background: #f1f3f5;
        }

        .turno-actual {
            font-size: 1.6rem;
            font-weight: bold;
            text-align: center;
            padding: 6px 0 2px;
        }

        .turno-actual.atendiendo {
            color: var(--estado-atendiendo);
        }

        .turno-actual.ocupado {
            color: var(--estado-ocupado);
        }

        .turno-actual.disponible {
            color: var(--estado-disponible);
            font-size: 1.2rem;
        }

        .turno-actual.inactivo {
            color: var(--estado-inactivo);
            font-size: 1.2rem;
        }

        .texto-secundario {
            font-size: 0.8rem;
            color: var(--texto-secundario);
        }

        .fila-detalle {
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 8px;
            font-size: 0.75rem;
        }

        .cronometro {
            font-family: monospace;
            font-size: 0.9rem;
            font-weight: bold;
            color: #212529;
        }

        .label-crono {
            font-size: 0.7rem;
            color: var(--texto-secundario);
        }

        .info-ubicacion {
            font-size: 0.75rem;
            color: var(--texto-secundario);
            line-height: 1.3;
        }

        .chip-categoria {
            display: inline-flex;
            align-items: center;
            gap: 4px;
            font-size: 0.7rem;
            border-radius: 999px;
            padding: 2px 7px;
            color: #fff;
        }

        .chip-categoria.asesoria {
            background: var(--chip-asesoria);
        }
        .chip-categoria.ventanilla {
            background: var(--chip-ventanilla);
        }
        .chip-categoria.estudio {
            background: var(--chip-estudio);
        }
        .chip-categoria.otros {
            background: var(--chip-otros);
        }

        .loading-message {
            text-align: center;
            font-size: 1rem;
            color: #0d6efd;
            margin-top: 20px;
        }

        .mensaje-error {
            text-align: center;
            color: #dc3545;
            margin-top: 10px;
            font-size: 0.9rem;
        }

        @media (max-width: 768px) {
            .tarjeta {
                width: 100%;
            }
        }
    </style>
</head>
<body>

<div class="layout">
    <!-- HEADER -->
    <div class="header">
        <div class="header-izquierda">
            <div class="header-titulo">Estado de Módulos de Atención</div>
            <div class="header-subtitulo" id="info-sede">Sede: -- · Puesto de trabajo: --</div>
        </div>
        <div class="header-derecha">
            <span id="contador-recarga-mensaje">Actualizando...</span>
            <span id="ultima-actualizacion">Última actualización: --</span>
        </div>
    </div>

    <!-- BARRA SUPERIOR: FILTRO + KPIs -->
    <div class="barra-superior">
        <div class="filtro-bloque">
            <label for="filtroCategoria">Ver categoría:</label>
            <select id="filtroCategoria">
                <option value="">Todas</option>
                <option value="ASESORIA">ASESORÍA</option>
                <option value="VENTANILLA">VENTANILLA</option>
                <option value="ESTUDIO">ESTUDIO PREVIO</option>
                <option value="OTROS">OTROS</option>
            </select>
        </div>

        <div class="resumen-estadisticas">
            <div class="chip-resumen">
                <span class="etiqueta">Módulos:</span>
                <span id="kpi-total" class="valor">0</span>
            </div>
            <div class="chip-resumen atendiendo">
                <span class="etiqueta">Atendiendo:</span>
                <span id="kpi-atendiendo" class="valor">0</span>
            </div>
            <div class="chip-resumen">
                <span class="etiqueta">Ocupados:</span>
                <span id="kpi-ocupado" class="valor">0</span>
            </div>
            <div class="chip-resumen">
                <span class="etiqueta">Disponibles:</span>
                <span id="kpi-disponible" class="valor">0</span>
            </div>
            <div class="chip-resumen">
                <span class="etiqueta">Inactivos:</span>
                <span id="kpi-inactivo" class="valor">0</span>
            </div>
            <div class="chip-resumen ocupacion">
                <span class="etiqueta">% Ocupación:</span>
                <span id="kpi-ocupacion" class="valor">0%</span>
            </div>
            <div class="chip-resumen">
                <span class="etiqueta">Prom. atención:</span>
                <span id="kpi-promedio" class="valor">--:--</span>
            </div>
        </div>
    </div>

    <!-- CONTENIDO PRINCIPAL -->
    <div class="contenido">
        <div id="contenedorCategorias"></div>
        <div id="mensajeError" class="mensaje-error"></div>
    </div>
</div>

<script>
    // ================================
    // CONFIGURACIÓN
    // ================================
    const API_URL = 'https://api.citurcam.com/atendiendoPorModulosPorZonasAtencion';
    const PUESTO_TRABAJO_ID = 74;        // se envía como datos[] por POST
    const INTERVALO_MS = 30000;          // 30 segundos
    const INTERVALO_SEGS = INTERVALO_MS / 1000;

    let segundosRestantes = INTERVALO_SEGS;
    let ultimoMapaModulos = {};          // { moduloAtencionID: { estado, turno } }
    let audioContext = null;
    let ultimoDatosModulos = [];         // para cronómetros

    // ================================
    // CONTADOR DE RECARGA (cada 1s)
    // ================================
    function actualizarMensajeContador() {
        const contadorSpan = document.getElementById('contador-recarga-mensaje');
        if (segundosRestantes > 0) {
            contadorSpan.textContent = `Actualizando en ${segundosRestantes} segundos...`;
        } else {
            contadorSpan.textContent = `Actualizando...`;
        }
    }

    setInterval(() => {
        segundosRestantes--;
        if (segundosRestantes < 0) {
            segundosRestantes = 0;
        }
        actualizarMensajeContador();
        actualizarCronometrosEnPantalla();
    }, 1000);

    // ================================
    // OBTENER DATOS DEL API
    // ================================
    async function obtenerDatosModulos() {
        const contCategorias = document.getElementById('contenedorCategorias');
        const mensajeError = document.getElementById('mensajeError');

        segundosRestantes = INTERVALO_SEGS;
        mensajeError.textContent = '';

        if (!contCategorias.innerHTML.trim()) {
            contCategorias.innerHTML = '<p class="loading-message">Cargando datos de módulos...</p>';
        }

        try {
            const datosFormulario = new URLSearchParams();
            datosFormulario.append('datos[]', PUESTO_TRABAJO_ID);

            const response = await fetch(API_URL, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded'
                },
                body: datosFormulario.toString()
            });

            if (!response.ok) {
                throw new Error(`Error en la petición: ${response.status} ${response.statusText}`);
            }

            const data = await response.json();

            if (data.RESPUESTA === 'EXITO' && Array.isArray(data.DATOS)) {
                ultimoDatosModulos = data.DATOS || [];
                actualizarHeaderInfo(ultimoDatosModulos);
                detectarCambiosEstados(ultimoDatosModulos);
                renderizarCategorias(ultimoDatosModulos);
                actualizarKPIs(ultimoDatosModulos);
                actualizarHoraUltimaActualizacion();
            } else {
                contCategorias.innerHTML = '<p class="loading-message" style="color:#dc3545;">Respuesta no exitosa o sin datos.</p>';
                console.error('Respuesta de API:', data.MENSAJE || 'Respuesta inesperada');
            }
        } catch (error) {
            console.error('Error al obtener los datos:', error);
            mensajeError.textContent = `Error de conexión: ${error.message}. Reintentando...`;
        }
    }

    function actualizarHoraUltimaActualizacion() {
        const ahora = new Date();
        const strHora = ahora.toLocaleTimeString('es-CO', {
            hour: '2-digit',
            minute: '2-digit',
            second: '2-digit'
        });
        document.getElementById('ultima-actualizacion').textContent =
            'Última actualización: ' + strHora;
    }

    function actualizarHeaderInfo(modulos) {
        if (!modulos || !modulos.length) return;
        const m0 = modulos[0];
        const sede = m0.sedeTITULO || '--';
        const puesto = m0.puestoTrabajoTITULO || '--';
        document.getElementById('info-sede').textContent =
            `Sede: ${sede} · Puesto de trabajo: ${puesto}`;
    }

    // ================================
    // CLASIFICACIÓN ESTADO / CATEGORÍA
    // ================================
    function obtenerEstadoModulo(modulo) {
        // Heurística:
        // - Sin turno: DISPONIBLE
        // - Con turno y turnoFECHAREGISTRO: ATENDIENDO
        // - Con turno sin fecha: OCUPADO
        // - Futuro: otros criterios podrían marcar INACTIVO
        const tieneTurno = !!(modulo.turnoID || modulo.turnoCODIGO || modulo.turnoCODIGOCORTO);
        if (!tieneTurno) {
            return 'DISPONIBLE';
        }
        if (modulo.turnoFECHAREGISTRO) {
            return 'ATENDIENDO';
        }
        return 'OCUPADO';
    }

    function obtenerCategoriaModulo(modulo) {
        const cod = (modulo.moduloAtencionCODIGO || '').toUpperCase();
        const tit = (modulo.moduloAtencionTITULO || '').toUpperCase();

        if (cod.includes('ASESOR') || tit.includes('ASESOR')) {
            return 'ASESORIA';
        }
        if (cod.includes('VENTANILLA') || tit.includes('VENTANILLA')) {
            return 'VENTANILLA';
        }
        if (cod.includes('ESTUDIO') || tit.includes('ESTUDIO')) {
            return 'ESTUDIO';
        }
        return 'OTROS';
    }

    function obtenerOrdenEstado(estado) {
        switch (estado) {
            case 'ATENDIENDO': return 1;
            case 'OCUPADO': return 2;
            case 'DISPONIBLE': return 3;
            case 'INACTIVO': return 4;
            default: return 5;
        }
    }

    // ================================
    // DETECTOR CAMBIOS ESTADO (BEEP)
    // ================================
    function detectarCambiosEstados(modulos) {
        let huboCambios = false;
        const nuevoMapa = {};

        modulos.forEach(m => {
            const estado = obtenerEstadoModulo(m);
            const turno = m.turnoCODIGOCORTO || m.turnoCODIGO || '';
            const id = m.moduloAtencionID;

            nuevoMapa[id] = { estado, turno };

            const anterior = ultimoMapaModulos[id];
            if (anterior && (anterior.estado !== estado || anterior.turno !== turno)) {
                huboCambios = true;
            }
        });

        if (huboCambios) {
            reproducirBeep();
        }

        ultimoMapaModulos = nuevoMapa;
    }

    function reproducirBeep() {
        try {
            if (!audioContext) {
                audioContext = new (window.AudioContext || window.webkitAudioContext)();
            }
            const duracion = 0.20;
            const frecuencia = 880;

            const oscillator = audioContext.createOscillator();
            const gainNode = audioContext.createGain();

            oscillator.type = 'sine';
            oscillator.frequency.setValueAtTime(frecuencia, audioContext.currentTime);
            gainNode.gain.setValueAtTime(0.18, audioContext.currentTime);

            oscillator.connect(gainNode);
            gainNode.connect(audioContext.destination);

            oscillator.start();
            oscillator.stop(audioContext.currentTime + duracion);
        } catch (e) {
            console.warn('No se pudo reproducir el beep:', e);
        }
    }

    // ================================
    // RENDER CATEGORÍAS + TARJETAS
    // ================================
    function renderizarCategorias(modulos) {
        const contCategorias = document.getElementById('contenedorCategorias');
        contCategorias.innerHTML = '';

        const filtroCategoria = document.getElementById('filtroCategoria').value;

        if (!modulos || !modulos.length) {
            contCategorias.innerHTML = '<p class="loading-message">No hay módulos configurados.</p>';
            return;
        }

        // Agrupar por categoría
        const mapaCategorias = {}; // { categoria: [modulos] }
        modulos.forEach(m => {
            const cat = obtenerCategoriaModulo(m);
            if (!mapaCategorias[cat]) mapaCategorias[cat] = [];
            mapaCategorias[cat].push(m);
        });

        const ordenCategorias = ['ASESORIA', 'VENTANILLA', 'ESTUDIO', 'OTROS'];

        ordenCategorias.forEach(cat => {
            const lista = mapaCategorias[cat] || [];
            if (!lista.length) return;

            if (filtroCategoria && filtroCategoria !== cat) {
                return;
            }

            // Orden por estado y luego por nombre de módulo
            lista.sort((a, b) => {
                const ea = obtenerEstadoModulo(a);
                const eb = obtenerEstadoModulo(b);
                const oa = obtenerOrdenEstado(ea);
                const ob = obtenerOrdenEstado(eb);
                if (oa !== ob) return oa - ob;
                const ta = (a.moduloAtencionTITULO || '').toUpperCase();
                const tb = (b.moduloAtencionTITULO || '').toUpperCase();
                if (ta < tb) return -1;
                if (ta > tb) return 1;
                return 0;
            });

            const bloqueCat = document.createElement('div');
            bloqueCat.classList.add('categoria-bloque');

            const tituloCat = document.createElement('div');
            tituloCat.classList.add('categoria-titulo');

            const nombreCat = {
                'ASESORIA': 'Módulos de Asesoría',
                'VENTANILLA': 'Módulos de Ventanilla',
                'ESTUDIO': 'Módulos de Estudio Previo',
                'OTROS': 'Otros módulos'
            }[cat] || cat;

            const badge = document.createElement('span');
            badge.classList.add('badge');
            switch (cat) {
                case 'ASESORIA': badge.classList.add('badge-asesoria'); break;
                case 'VENTANILLA': badge.classList.add('badge-ventanilla'); break;
                case 'ESTUDIO': badge.classList.add('badge-estudio'); break;
                default: badge.classList.add('badge-otros'); break;
            }
            badge.textContent = `${lista.length}`;

            tituloCat.textContent = nombreCat + ' ';
            tituloCat.appendChild(badge);

            const contTarjetas = document.createElement('div');
            contTarjetas.classList.add('contenedor-tarjetas');

            lista.forEach(m => {
                contTarjetas.appendChild(crearTarjetaModulo(m, cat));
            });

            bloqueCat.appendChild(tituloCat);
            bloqueCat.appendChild(contTarjetas);
            contCategorias.appendChild(bloqueCat);
        });
    }

    function crearTarjetaModulo(modulo, categoria) {
        const tarjeta = document.createElement('div');
        tarjeta.classList.add('tarjeta');

        const estado = obtenerEstadoModulo(modulo);
        const turnoCodigo = modulo.turnoCODIGOCORTO || modulo.turnoCODIGO || '--';
        const persona = modulo.personaRAZONSOCIAL ||
            [modulo.personaNOMBRES, modulo.personaAPELLIDOS].filter(Boolean).join(' ') ||
            'Sin persona asignada';

        // HEADER
        const header = document.createElement('div');
        header.classList.add('tarjeta-header');

        const bloqueTitulo = document.createElement('div');
        const titulo = document.createElement('p');
        titulo.classList.add('titulo-modulo');
        titulo.textContent = modulo.moduloAtencionTITULO || 'Módulo sin nombre';

        const codigo = document.createElement('div');
        codigo.classList.add('codigo-modulo');
        codigo.textContent = modulo.moduloAtencionCODIGO || '';

        bloqueTitulo.appendChild(titulo);
        bloqueTitulo.appendChild(codigo);

        const estadoChip = document.createElement('span');
        estadoChip.classList.add('estado-chip');

        let textoEstado = '';
        switch (estado) {
            case 'ATENDIENDO':
                textoEstado = 'Atendiendo';
                estadoChip.classList.add('estado-atendiendo-chip');
                break;
            case 'OCUPADO':
                textoEstado = 'Ocupado';
                estadoChip.classList.add('estado-ocupado-chip');
                break;
            case 'DISPONIBLE':
                textoEstado = 'Disponible';
                estadoChip.classList.add('estado-disponible-chip');
                break;
            case 'INACTIVO':
                textoEstado = 'Inactivo';
                estadoChip.classList.add('estado-inactivo-chip');
                break;
            default:
                textoEstado = estado;
                estadoChip.classList.add('estado-disponible-chip');
        }

        estadoChip.textContent = textoEstado;

        header.appendChild(bloqueTitulo);
        header.appendChild(estadoChip);

        // TURNO
        const turnoDiv = document.createElement('div');
        turnoDiv.classList.add('turno-actual');

        if (estado === 'ATENDIENDO') {
            turnoDiv.classList.add('atendiendo');
            turnoDiv.textContent = turnoCodigo !== '--' ? turnoCodigo : 'ATENDIENDO';
        } else if (estado === 'OCUPADO') {
            turnoDiv.classList.add('ocupado');
            turnoDiv.textContent = turnoCodigo !== '--' ? turnoCodigo : 'OCUPADO';
        } else if (estado === 'DISPONIBLE') {
            turnoDiv.classList.add('disponible');
            turnoDiv.textContent = 'DISPONIBLE';
        } else {
            turnoDiv.classList.add('inactivo');
            turnoDiv.textContent = 'INACTIVO';
        }

        // FILA DETALLE: persona + categoría + cronómetro
        const filaDetalle = document.createElement('div');
        filaDetalle.classList.add('fila-detalle');

        const infoPersona = document.createElement('div');
        infoPersona.classList.add('texto-secundario');
        infoPersona.textContent = persona.length > 32 ? persona.slice(0, 29) + '...' : persona;

        const chipCat = document.createElement('span');
        chipCat.classList.add('chip-categoria');
        switch (categoria) {
            case 'ASESORIA': chipCat.classList.add('asesoria'); chipCat.textContent = 'ASESORÍA'; break;
            case 'VENTANILLA': chipCat.classList.add('ventanilla'); chipCat.textContent = 'VENTANILLA'; break;
            case 'ESTUDIO': chipCat.classList.add('estudio'); chipCat.textContent = 'ESTUDIO'; break;
            default: chipCat.classList.add('otros'); chipCat.textContent = 'OTROS'; break;
        }

        const bloqueCrono = document.createElement('div');
        bloqueCrono.style.textAlign = 'right';

        const labelCrono = document.createElement('div');
        labelCrono.classList.add('label-crono');
        labelCrono.textContent = 'Tiempo atención';

        const spanCrono = document.createElement('div');
        spanCrono.classList.add('cronometro', 'timer-cronometro');
        spanCrono.dataset.inicio = modulo.turnoFECHAREGISTRO || '';
        spanCrono.textContent = (estado === 'ATENDIENDO' && modulo.turnoFECHAREGISTRO)
            ? formatearDiferencia(modulo.turnoFECHAREGISTRO)
            : '--:--';

        bloqueCrono.appendChild(labelCrono);
        bloqueCrono.appendChild(spanCrono);

        filaDetalle.appendChild(infoPersona);
        filaDetalle.appendChild(chipCat);
        filaDetalle.appendChild(bloqueCrono);

        // UBICACIÓN
        const ubicacion = document.createElement('div');
        ubicacion.classList.add('info-ubicacion');
        const sede = modulo.sedeTITULO || '';
        const puesto = modulo.puestoTrabajoTITULO || '';
        ubicacion.textContent = `${sede} · ${puesto}`;

        tarjeta.appendChild(header);
        tarjeta.appendChild(turnoDiv);
        tarjeta.appendChild(filaDetalle);
        tarjeta.appendChild(ubicacion);

        return tarjeta;
    }

    // ================================
    // KPIs
    // ================================
    function actualizarKPIs(modulos) {
        const total = modulos.length;
        let atendiendo = 0;
        let ocupado = 0;
        let disponible = 0;
        let inactivo = 0;

        const tiemposAtencion = [];

        modulos.forEach(m => {
            const estado = obtenerEstadoModulo(m);
            if (estado === 'ATENDIENDO') {
                atendiendo++;
                if (m.turnoFECHAREGISTRO) {
                    const diffSeg = diffSegundosDesde(m.turnoFECHAREGISTRO);
                    if (!isNaN(diffSeg) && diffSeg >= 0) {
                        tiemposAtencion.push(diffSeg);
                    }
                }
            } else if (estado === 'OCUPADO') {
                ocupado++;
            } else if (estado === 'DISPONIBLE') {
                disponible++;
            } else {
                inactivo++;
            }
        });

        const ocupadosTotales = atendiendo + ocupado;
        const kpiOcupacion = total > 0 ? Math.round((ocupadosTotales / total) * 100) : 0;

        document.getElementById('kpi-total').textContent = total;
        document.getElementById('kpi-atendiendo').textContent = atendiendo;
        document.getElementById('kpi-ocupado').textContent = ocupado;
        document.getElementById('kpi-disponible').textContent = disponible;
        document.getElementById('kpi-inactivo').textContent = inactivo;
        document.getElementById('kpi-ocupacion').textContent = kpiOcupacion + '%';

        const promedioSeg = tiemposAtencion.length
            ? Math.round(tiemposAtencion.reduce((a, b) => a + b, 0) / tiemposAtencion.length)
            : null;

        document.getElementById('kpi-promedio').textContent =
            promedioSeg !== null ? formatearSegundos(promedioSeg) : '--:--';
    }

    // ================================
    // CRONÓMETROS
    // ================================
    function diffSegundosDesde(fechaHoraStr) {
        if (!fechaHoraStr) return NaN;
        // "2025-12-05 08:14:41" → "2025-12-05T08:14:41"
        const normalizado = fechaHoraStr.replace(' ', 'T');
        const inicio = new Date(normalizado);
        if (isNaN(inicio.getTime())) return NaN;
        const ahora = new Date();
        return Math.floor((ahora - inicio) / 1000);
    }

    function formatearSegundos(segundos) {
        const mins = Math.floor(segundos / 60);
        const secs = segundos % 60;
        const mm = String(mins).padStart(2, '0');
        const ss = String(secs).padStart(2, '0');
        return `${mm}:${ss}`;
    }

    function formatearDiferencia(fechaHoraStr) {
        const s = diffSegundosDesde(fechaHoraStr);
        if (isNaN(s) || s < 0) return '--:--';
        return formatearSegundos(s);
    }

    function actualizarCronometrosEnPantalla() {
        const nodos = document.querySelectorAll('.timer-cronometro');
        nodos.forEach(n => {
            const inicio = n.dataset.inicio;
            if (!inicio) {
                n.textContent = '--:--';
                return;
            }
            n.textContent = formatearDiferencia(inicio);
        });
    }

    // ================================
    // EVENTOS
    // ================================
    document.getElementById('filtroCategoria').addEventListener('change', () => {
        if (ultimoDatosModulos && ultimoDatosModulos.length) {
            renderizarCategorias(ultimoDatosModulos);
        }
    });

    // ================================
    // ARRANQUE
    // ================================
    obtenerDatosModulos();
    setInterval(obtenerDatosModulos, INTERVALO_MS);
</script>

</body>
</html>
