// ================================
// CONFIGURACIÓN BÁSICA
// ================================
const SEDE_ID = "10";
const FECHA_CITA = new Date().toISOString().split('T')[0]; // AAAA-MM-DD
const OPERACION_ENDPOINT = "calendarioCitasFecha"; // lo dejamos igual a tu versión funcional
const INTERVALO_SEGUNDOS = 300; // 5 minutos

let segundosRestantes = INTERVALO_SEGUNDOS;
let ultimoResultado = null;
let ultimoMapaEstados = {}; // { citaID: "ESTADO" }
let audioContext = null;

const citasPorId = new Map(); // citaID -> objeto cita
// ================================
// CONEXIÓN AL ENDPOINT
// (mantenemos exactamente tu formato)
// ================================
if (typeof window.mostrarModalDeCarga !== 'function') {
    window.mostrarModalDeCarga = function () { };
}

window.conectarseEndPoint = async function (operacion, params = {}) {
    const api = 'https://api.citurcam.com/' + operacion;

    if (typeof params !== 'object' || params === null) {
        params = params.toString();
    }
    mostrarModalDeCarga();
    const response = await fetch(api, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded'
        },
        body: JSON.stringify(params)
    });
    if (!response.ok) {
        throw new Error('Error en la petición: ' + response.status);
    }
    return await response.json();
};

// ================================
// UTILIDADES DE FECHAS Y TIEMPOS
// ================================
function parseDateTime(str) {
    if (!str || typeof str !== 'string') return null;
    // Espera formato "YYYY-MM-DD HH:MM:SS"
    const normalizado = str.replace(' ', 'T');
    const d = new Date(normalizado);
    if (isNaN(d.getTime())) return null;
    return d;
}

function diffMs(inicioStr, finStr) {
    const ini = parseDateTime(inicioStr);
    const fin = parseDateTime(finStr);
    if (!ini || !fin) return null;
    const ms = fin.getTime() - ini.getTime();
    if (!isFinite(ms) || ms < 0) return null;
    return ms;
}

// Formato C: minutos + HH:MM:SS
function formatearDuracionMs(ms) {
    if (ms == null) return '—';
    const totalSeg = Math.round(ms / 1000);
    const min = Math.round(ms / 60000);

    let resto = totalSeg;
    const h = Math.floor(resto / 3600);
    resto = resto % 3600;
    const m = Math.floor(resto / 60);
    const s = resto % 60;

    const hh = String(h).padStart(2, '0');
    const mm = String(m).padStart(2, '0');
    const ss = String(s).padStart(2, '0');

    return `${min} min (${hh}:${mm}:${ss})`;
}

function formatearDuracionPromedio(sumMs, count) {
    if (!count || !sumMs) return '—';
    const promedio = sumMs / count;
    return formatearDuracionMs(promedio);
}

function soloHora(str) {
    if (!str || typeof str !== 'string') return '--:--';
    return str.substring(11, 16);
}


// ================================
// FUNCIÓN PRINCIPAL DE CARGA
// ================================
async function cargarTableroCitas(fechaSeleccionada = FECHA_CITA) {

    segundosRestantes = INTERVALO_SEGUNDOS;
    const contenedor = document.getElementById('contenedorTarjetas');
    const mensajeError = document.getElementById('mensajeError');
    mensajeError.textContent = '';
    contenedor.innerHTML = '<p class="loading-message">Actualizando información...</p>';

    try {
        const params = {
            sedeID: SEDE_ID,
            citaFCHCITA: fechaSeleccionada
        };

        const data = await window.conectarseEndPoint(OPERACION_ENDPOINT, params);

        // Guardamos último resultado
        ultimoResultado = data;

        // Detector de cambios de estado
        detectarCambiosEstados(data);

        // Actualizamos filtros y tablero
        poblarFiltroServicios(data);
        renderTablero(data);

        // Actualizar hora de última actualización
        const ahora = new Date();
        const strHora = ahora.toLocaleTimeString('es-CO', {
            hour: '2-digit',
            minute: '2-digit',
            second: '2-digit'
        });
        document.getElementById('ultima-actualizacion').textContent =
            'Última actualización: ' + strHora;

    } catch (error) {
        console.error(error);
        contenedor.innerHTML = '';
        mensajeError.textContent = 'Error al cargar datos de citas: ' + error.message;
    }
}

// ================================
// SONIDO CUANDO CAMBIA CUALQUIER ESTADO
// ================================
function detectarCambiosEstados(data) {
    if (!data || !data.datos) return;

    const nuevosEstados = {};
    let huboCambios = false;

    data.datos.forEach(bloque => {
        (bloque.citas || []).forEach(cita => {
            const id = cita.citaID;
            const estado = (cita.citaESTADOCITA || '').toUpperCase();
            nuevosEstados[id] = estado;
            if (ultimoMapaEstados[id] && ultimoMapaEstados[id] !== estado) {
                huboCambios = true;
            }
        });
    });

    if (huboCambios) {
        reproducirBeep();
    }

    ultimoMapaEstados = nuevosEstados;
}

function reproducirBeep() {
    try {
        if (!audioContext) {
            audioContext = new (window.AudioContext || window.webkitAudioContext)();
        }
        const duracion = 0.25;
        const frecuencia = 880;

        const oscillator = audioContext.createOscillator();
        const gainNode = audioContext.createGain();

        oscillator.type = 'sine';
        oscillator.frequency.setValueAtTime(frecuencia, audioContext.currentTime);
        gainNode.gain.setValueAtTime(0.2, audioContext.currentTime);

        oscillator.connect(gainNode);
        gainNode.connect(audioContext.destination);

        oscillator.start();
        oscillator.stop(audioContext.currentTime + duracion);
    } catch (e) {
        console.warn('No se pudo reproducir el beep:', e);
    }
}

// ================================
// FILTRO DE SERVICIOS
// ================================
function poblarFiltroServicios(data) {
    const select = document.getElementById('filtroServicio');
    const valorAnterior = select.value || '';

    // Limpiar (dejando opción "todos")
    select.innerHTML = '<option value="">Todos los servicios</option>';

    if (data && data.TiposServicios) {
        Object.entries(data.TiposServicios).forEach(([codigo, info]) => {
            const opt = document.createElement('option');
            opt.value = codigo;
            opt.textContent = info.titulo + ' (' + codigo + ')';
            select.appendChild(opt);
        });
    }

    // Restaurar selección si sigue existiendo
    if (valorAnterior) {
        const opcion = Array.from(select.options).find(o => o.value === valorAnterior);
        if (opcion) select.value = valorAnterior;
    }
}


// ================================
// RENDERIZAR TABLERO (TARJETAS)
// ================================
function renderTablero(data) {
    const contenedor = document.getElementById('contenedorTarjetas');
    contenedor.innerHTML = '';
    const filtroServicio = document.getElementById('filtroServicio').value;

    if (!data || !data.datos || !data.modulos) {
        contenedor.innerHTML = '<p class="loading-message">No hay datos para mostrar.</p>';
        actualizarResumen([]);
        return;
    }

    // Mapa de citas por módulo
    const mapaCitasPorModulo = {};
    const todasLasCitas = [];


    // 🔹 LIMPIAR mapa global de citas y volver a llenarlo
    citasPorId.clear();

    data.datos.forEach(bloque => {
        (bloque.citas || []).forEach(cita => {
            todasLasCitas.push(cita);

            // 👉 Guardamos la cita en el mapa global, clave = ID numérico
            citasPorId.set(Number(cita.citaID), cita); // línea crítica: sin esto el historial nunca encuentra la cita

            if (!mapaCitasPorModulo[cita.moduloAtencionID]) {
                mapaCitasPorModulo[cita.moduloAtencionID] = [];
            }
            mapaCitasPorModulo[cita.moduloAtencionID].push(cita);
        });
    });

    // Actualizar resumen general
    actualizarResumen(todasLasCitas);

    // Tarjetas por módulo
    data.modulos.forEach(modulo => {
        const citasModulo = (mapaCitasPorModulo[modulo.moduloAtencionID] || []).slice();

        // Aplicar filtro de servicio si está seleccionado
        const citasFiltradas = citasModulo.filter(cita => {
            if (!filtroServicio) return true;
            return cita.turnoTipoServicioCODIGO === filtroServicio;
        });

        const tarjeta = document.createElement('div');
        tarjeta.classList.add('tarjeta-modulo');

        const colorBorde = colorPorServicio(modulo.turnoTipoServicioCODIGO);
        tarjeta.style.borderLeftColor = colorBorde;

        const titulo = document.createElement('div');
        titulo.classList.add('titulo-modulo');
        titulo.textContent = modulo.moduloAtencionTITULO || modulo.moduloAtencionDESCRIPCION;

        const subtitulo = document.createElement('div');
        subtitulo.classList.add('subtitulo-modulo');
        subtitulo.textContent = (modulo.moduloAtencionMODO || '') + ' · ' + (modulo.turnoTipoServicioTITULO || '');

        // KPIs por módulo (conteos por estado)
        const kpis = document.createElement('div');
        kpis.classList.add('kpis-modulo');
        const totalModulo = citasModulo.length;
        let sinAsignarM = 0,
            pendienteM = 0,
            atendidoM = 0,
            canceladoM = 0;

        citasModulo.forEach(c => {
            const est = (c.citaESTADOCITA || '').toUpperCase();
            if (est === 'SIN ASIGNAR') sinAsignarM++;
            else if (est === 'PENDIENTE') pendienteM++;
            else if (est === 'ATENDIDO') atendidoM++;
            else if (est === 'CANCELADO' || est === 'CANCELADA') canceladoM++;
        });

        kpis.innerHTML = `
            <span>Total: ${totalModulo}</span>
            <span>Sin asignar: ${sinAsignarM}</span>
            <span>Pend.: ${pendienteM}</span>
            <span>Atend.: ${atendidoM}</span>
            <span>Cancel.: ${canceladoM}</span>
        `;

        const listaCitas = document.createElement('div');
        listaCitas.classList.add('lista-citas');

        if (citasFiltradas.length === 0) {
            const msg = document.createElement('div');
            msg.classList.add('mensaje-sin-citas');
            msg.textContent = 'Sin citas para este servicio / filtro.';
            listaCitas.appendChild(msg);
        } else {
            // Ordenar por fecha/hora
            citasFiltradas.sort((a, b) =>
                new Date(a.citaFCHCITA) - new Date(b.citaFCHCITA)
            );

            citasFiltradas.forEach(cita => {
                const bloque = document.createElement('div');
                bloque.classList.add('bloque-cita');

                const detalles = document.createElement('div');
                detalles.classList.add('bloque-cita-detalles');

                // ============= Fila estado + servicio =============
                const filaEstadoServ = document.createElement('div');
                filaEstadoServ.classList.add('fila-estado-servicio');

                // Determinar estado real de la cita (prioriza cancelación)
                let estadoTexto = (cita.citaESTADOCITA || '').toUpperCase();
                if (cita.citaFCHCANCELACION && cita.citaFCHCANCELACION !== "null") {
                    estadoTexto = "CANCELADO";
                }

                const spanEstado = document.createElement('span');
                spanEstado.classList.add('estado-cita');
                spanEstado.textContent = estadoTexto || 'SIN ESTADO';

                switch (estadoTexto) {
                    case 'SIN ASIGNAR':
                        spanEstado.classList.add('estado-sin-asignar');
                        break;
                    case 'PENDIENTE':
                        spanEstado.classList.add('estado-pendiente');
                        break;
                    case 'ATENDIDO':
                        spanEstado.classList.add('estado-atendido');
                        break;
                    case 'CANCELADO':
                    case 'CANCELADA':
                        spanEstado.classList.add('estado-cancelado');
                        break;
                    default:
                        spanEstado.classList.add('estado-sin-asignar');
                }

                // Chip de servicio
                const chip = document.createElement('span');
                chip.classList.add('chip-servicio');
                const codServ = cita.turnoTipoServicioCODIGO || 'DEFAULT';
                chip.classList.add('servicio-' + (codServ in {
                    CAE: 1,
                    CAJA: 1,
                    RNT: 1,
                    REVI: 1
                } ? codServ : 'DEFAULT'));
                chip.textContent = codServ;

                // 🔹 NUEVO: chip con la HORA
                const chipHora = document.createElement('span');
                chipHora.classList.add('chip-hora');
                chipHora.innerHTML = `<span class="icono-reloj">🕒</span> ${soloHora(cita.citaFCHCITA)}`;


                filaEstadoServ.appendChild(spanEstado);
                filaEstadoServ.appendChild(chip);
                filaEstadoServ.appendChild(chipHora);

                // ============= NUEVO: Bloque con nombres =============
                const infoNombres = document.createElement('div');
                infoNombres.classList.add('persona-colaborador');

                // Construir nombre de la persona (NOMBRES + APELLIDOS, o RAZONSOCIAL, o texto por defecto)
                const nombresPersona = (cita.personaNOMBRES || '').trim();
                const apellidosPersona = (cita.personaAPELLIDOS || '').trim();
                let etiquetaPersona = `${nombresPersona} ${apellidosPersona}`.trim(); // nombres + apellidos

                if (!etiquetaPersona) {
                    etiquetaPersona = (cita.personaRAZONSOCIAL || '').trim(); // respaldo en razón social
                }
                if (!etiquetaPersona) {
                    etiquetaPersona = 'Sin persona asignada'; // si viene completamente vacío
                }

                // Construir nombre del colaborador (puede venir vacío)
                let etiquetaColaborador = (cita.colaboradorNOMBRECOMPLETO || '').trim();
                if (!etiquetaColaborador) {
                    etiquetaColaborador = 'Sin colaborador asignado'; // mensaje claro cuando no hay colaborador
                }

                infoNombres.innerHTML = `
                    <div class="nombre-persona">👤 ${etiquetaPersona}</div>
                    <div class="nombre-colaborador">🙋‍♀️ ${etiquetaColaborador}</div>
                `;

                // ============= IDs (cita, persona, colaborador) =============
                const detalleIds = document.createElement('div');
                detalleIds.classList.add('detalle-ids');
                detalleIds.innerHTML = `
                    <span>#Cita: ${cita.citaID}</span>
                    <span>PersonaID: ${cita.personaID ?? '—'}</span>
                    <span>ColabID: ${cita.colaboradorID ?? '—'}</span>
                `;

                // ============= Tiempos (formato C) =============
                const msAsigAtenc = diffMs(cita.citaFCHASIGNACION, cita.citaFCHATENCION);
                const msAtencFin = diffMs(cita.citaFCHATENCION, cita.citaFCHAATENDIDO);
                const msCitaFin = diffMs(cita.citaFCHCITA, cita.citaFCHAATENDIDO);

                const tiempos = document.createElement('div');
                tiempos.classList.add('tiempos-cita');
                tiempos.innerHTML = `
                    <div><strong>Asig → Atención:</strong> ${formatearDuracionMs(msAsigAtenc)}</div>
                    <div><strong>Atención → Fin:</strong> ${formatearDuracionMs(msAtencFin)}</div>
                    <div><strong>Cita → Fin:</strong> ${formatearDuracionMs(msCitaFin)}</div>
                `;

                // ============= Fechas crudas (solo horas de trazas) =============
                const fechasCrudas = document.createElement('div');
                fechasCrudas.classList.add('fechas-crudas');
                fechasCrudas.innerHTML = `
                    Prog: ${soloHora(cita.citaFCHCITA)} · 
                    Asig: ${soloHora(cita.citaFCHASIGNACION)} · 
                    Atenc: ${soloHora(cita.citaFCHATENCION)} · 
                    Fin: ${soloHora(cita.citaFCHAATENDIDO)} · 
                    Canc: ${soloHora(cita.citaFCHCANCELACION)}
                `;

                // 🔹 NUEVO: botón para ver historial / timeline
                const filaHistorial = document.createElement('div');
                filaHistorial.classList.add('fila-historial');

                const btnHist = document.createElement('button');
                btnHist.type = 'button';
                btnHist.classList.add('btn-link', 'btn-historial');
                btnHist.textContent = 'Ver historial de estados';
                btnHist.dataset.citaId = cita.citaID;

                // Conectar al modal de timeline
                btnHist.addEventListener('click', () => {
                    abrirHistorialCita(cita.citaID);
                });

                filaHistorial.appendChild(btnHist);


                // Observaciones (si tiene)
                if (cita.citaOBSERVACIONES && cita.citaOBSERVACIONES.trim() !== '') {
                    const obs = document.createElement('div');
                    obs.classList.add('observaciones-cita');
                    obs.textContent = 'Obs: ' + cita.citaOBSERVACIONES.trim();
                    detalles.appendChild(obs);
                }

                // Links Calendar / Meet
                if (cita.citaENLACECALENDAR || cita.citaENLACEMEET) {
                    const links = document.createElement('div');
                    links.classList.add('links-cita');
                    if (cita.citaENLACECALENDAR) {
                        const aCal = document.createElement('a');
                        aCal.href = cita.citaENLACECALENDAR;
                        aCal.target = '_blank';
                        aCal.textContent = '📅 Calendar';
                        links.appendChild(aCal);
                    }
                    if (cita.citaENLACECALENDAR && cita.citaENLACEMEET) {
                        links.appendChild(document.createTextNode(' · '));
                    }
                    if (cita.citaENLACEMEET) {
                        const aMeet = document.createElement('a');
                        aMeet.href = cita.citaENLACEMEET;
                        aMeet.target = '_blank';
                        aMeet.textContent = '🎥 Meet';
                        links.appendChild(aMeet);
                    }
                    detalles.appendChild(links);
                }

                // Orden de presentación en el bloque de detalles
                detalles.appendChild(filaEstadoServ);   // Estado + servicio
                detalles.appendChild(infoNombres);      // NUEVO: nombres persona y colaborador
                detalles.appendChild(detalleIds);       // IDs internos
                detalles.appendChild(tiempos);          // Tiempos
                detalles.appendChild(fechasCrudas);     // Horas crudas
                detalles.appendChild(filaHistorial);    // 🔹 Botón historial

                bloque.appendChild(detalles);

                // Asignar color según el estado de la cita
                const estadoNorm = estadoTexto.toLowerCase();

                if (estadoNorm === "sin asignar") {
                    bloque.classList.add("cita-bg-sin-asignar");
                } else if (estadoNorm === "pendiente") {
                    bloque.classList.add("cita-bg-pendiente");
                } else if (estadoNorm === "atendiendo") {
                    bloque.classList.add("cita-bg-atendiendo");
                } else if (estadoNorm === "atendido") {
                    bloque.classList.add("cita-bg-atendido");
                } else if (estadoNorm === "cancelado" || estadoNorm === "cancelada") {
                    bloque.classList.add("cita-bg-cancelado");
                } else {
                    bloque.classList.add("cita-bg-default");
                }

                listaCitas.appendChild(bloque);
            });
        }

        tarjeta.appendChild(titulo);
        tarjeta.appendChild(subtitulo);
        tarjeta.appendChild(kpis);
        tarjeta.appendChild(listaCitas);

        contenedor.appendChild(tarjeta);
    });
}

function crearTarjetaCita(cita) {
    const div = document.createElement("div");
    div.className = "tarjeta-cita";
    div.innerHTML = `
        <div class="bloque-cita-detalle">
            <div class="fila-estado-servicio">
                <span class="chip chip-estado estado-${cita.citaESTADOCITA}">
                    ${cita.citaESTADOCITA}
                </span>
                <span class="chip chip-servicio">
                    ${cita.turnoTipoServicioTITULO || ""}
                </span>
                <span class="chip chip-hora">
                    <span class="icono-reloj">🕒</span>
                    ${formatearHoraCorta(cita.citaFCHCITA)}
                </span>
            </div>
            <div class="fila-persona">
                <strong>${cita.personaRAZONSOCIAL || "Sin asignar"}</strong>
                ${cita.personaIDENTIFICACION ? `
                    <span class="identificacion">ID ${cita.personaIDENTIFICACION}</span>
                ` : ""}
            </div>
            <div class="fila-colaborador">
                ${cita.colaboradorNOMBRECOMPLETO
            ? `<span>Asesor(a): ${cita.colaboradorNOMBRECOMPLETO}</span>`
            : `<span>Sin asesor asignado</span>`}
            </div>
            <div class="fila-acciones">
                <button
                    class="btn-link btn-historial"
                    type="button"
                    data-cita-id="${cita.citaID}">
                    Ver historial de estados
                </button>
            </div>
        </div>
    `;
    return div;
}

// ================================
// RESUMEN SUPERIOR
// ================================
function actualizarResumen(citas) {
    let total = citas.length;
    let sinAsignar = 0;
    let pendiente = 0;
    let atendido = 0;
    let cancelado = 0;
    let virtual = 0;
    let presencial = 0;

    let sumAsigAtenc = 0,
        countAsigAtenc = 0;
    let sumAtencFin = 0,
        countAtencFin = 0;
    let sumCitaFin = 0,
        countCitaFin = 0;

    citas.forEach(cita => {
        const est = (cita.citaESTADOCITA || '').toUpperCase();
        if (est === 'SIN ASIGNAR') sinAsignar++;
        else if (est === 'PENDIENTE') pendiente++;
        else if (est === 'ATENDIDO') atendido++;
        else if (est === 'CANCELADO' || est === 'CANCELADA') cancelado++;

        const tipo = (cita.citaTIPO || '').toUpperCase();
        if (tipo === 'VIRTUAL') virtual++;
        else if (tipo === 'PRESENCIAL') presencial++;

        const msAA = diffMs(cita.citaFCHASIGNACION, cita.citaFCHATENCION);
        if (msAA != null) {
            sumAsigAtenc += msAA;
            countAsigAtenc++;
        }

        const msAF = diffMs(cita.citaFCHATENCION, cita.citaFCHAATENDIDO);
        if (msAF != null) {
            sumAtencFin += msAF;
            countAtencFin++;
        }

        const msCF = diffMs(cita.citaFCHCITA, cita.citaFCHAATENDIDO);
        if (msCF != null) {
            sumCitaFin += msCF;
            countCitaFin++;
        }
    });

    document.getElementById('res-total').textContent = total;
    document.getElementById('res-sin-asignar').textContent = sinAsignar;
    document.getElementById('res-pendiente').textContent = pendiente;
    document.getElementById('res-atendido').textContent = atendido;
    document.getElementById('res-cancelado').textContent = cancelado;
    document.getElementById('res-virtual').textContent = virtual;
    document.getElementById('res-presencial').textContent = presencial;

    document.getElementById('res-prom-asig-atenc').textContent =
        formatearDuracionPromedio(sumAsigAtenc, countAsigAtenc);
    document.getElementById('res-prom-atenc-fin').textContent =
        formatearDuracionPromedio(sumAtencFin, countAtencFin);
    document.getElementById('res-prom-cita-fin').textContent =
        formatearDuracionPromedio(sumCitaFin, countCitaFin);
}

// ================================
// COLOR POR SERVICIO (BORDER TARJETAS)
// ================================
function colorPorServicio(codigo) {
    switch (codigo) {
        case 'CAE':
            return '#007bff';
        case 'CAJA':
            return '#28a745';
        case 'RNT':
            return '#6f42c1';
        case 'REVI':
            return '#fd7e14';
        default:
            return '#17a2b8';
    }
}

async function cargarFechasHabilitadas() {
    try {
        const params = {
            sedeID: SEDE_ID
        };
        const data = await window.conectarseEndPoint("buscarFechasCitasHabilitasPorSede", params);

        const select = document.getElementById("filtroFecha");
        select.innerHTML = "";

        if (data.FechasHabilitadas && data.FechasHabilitadas.length > 0) {
            data.FechasHabilitadas.forEach(item => {
                const op = document.createElement("option");
                op.value = item.fecha;
                op.textContent = item.fecha;
                select.appendChild(op);
            });
        }

        // Seleccionar por defecto la fecha actual si existe
        if (select.querySelector(`option[value="${FECHA_CITA}"]`)) {
            select.value = FECHA_CITA;
        } else {
            select.selectedIndex = 0;
        }

    } catch (e) {
        console.error("Error cargando fechas habilitadas:", e);
    }
}

function cargarTableroCitasPorFecha(fecha) {
    // Cambia la fecha y recarga el tablero
    cargarTableroCitas(fecha);
}


function construirEventosCita(cita) {
    const eventos = [];

    if (cita.citaFCHCREADO) {
        eventos.push({
            etiqueta: "Creación de la cita",
            fecha: formatearFechaHoraLarga(cita.citaFCHCREADO),
            detalle: `Creada por usuario ${cita.citaUSRCREADO ?? ""}`.trim()
        });
    }

    if (cita.citaFCHASIGNACION) {
        eventos.push({
            etiqueta: "Asignación",
            fecha: formatearFechaHoraLarga(cita.citaFCHASIGNACION),
            detalle: `Asignada al módulo: ${cita.moduloAtencionDESCRIPCION || "-"}`
        });
    }

    if (cita.citaFCHACTIVACION) {
        eventos.push({
            etiqueta: "Activación",
            fecha: formatearFechaHoraLarga(cita.citaFCHACTIVACION),
            detalle: "La cita fue marcada como ACTIVA."
        });
    }

    if (cita.citaFCHATENCION) {
        eventos.push({
            etiqueta: "Inicio de atención",
            fecha: formatearFechaHoraLarga(cita.citaFCHATENCION),
            detalle: "El asesor inició la atención."
        });
    }

    if (cita.citaFCHAATENDIDO) {
        eventos.push({
            etiqueta: "Atendida",
            fecha: formatearFechaHoraLarga(cita.citaFCHAATENDIDO),
            detalle: "La cita fue marcada como ATENDIDA."
        });
    }

    if (cita.citaFCHINASISTENCIA) {
        eventos.push({
            etiqueta: "Inasistencia",
            fecha: formatearFechaHoraLarga(cita.citaFCHINASISTENCIA),
            detalle: "Se registró INASISTENCIA del usuario."
        });
    }

    if (cita.citaFCHCANCELACION) {
        eventos.push({
            etiqueta: "Cancelación",
            fecha: formatearFechaHoraLarga(cita.citaFCHCANCELACION),
            detalle: `Cancelada por usuario ${cita.citaUSRCANCELA ?? ""}`.trim()
        });
    }

    if (cita.citaFCHMODIFICO && cita.citaFCHMODIFICO !== cita.citaFCHCREADO) {
        eventos.push({
            etiqueta: "Última modificación",
            fecha: formatearFechaHoraLarga(cita.citaFCHMODIFICO),
            detalle: `Modificada por usuario ${cita.citaUSRMODIFICO ?? ""}`.trim()
        });
    }

    return eventos;
}

// 🔹 Versión más robusta de abrirHistorialCita (evita romper si falta algo en el DOM)
function abrirHistorialCita(citaId) {
    const cita = citasPorId.get(Number(citaId));
    if (!cita) {
        console.error("abrirHistorialCita: no se encontró la cita en citasPorId", citaId);
        return;
    }

    const modal = document.getElementById("modal-historial");
    const lista = document.getElementById("timeline-estados");
    const subtitulo = document.getElementById("modal-subtitulo-cita");

    // 👉 Si falta alguna pieza del modal, no intentes seguir (evita el TypeError)
    if (!modal || !lista || !subtitulo) {
        console.error("abrirHistorialCita: falta estructura HTML del modal (modal-historial / timeline-estados / modal-subtitulo-cita)");
        return;
    }

    const nombre = cita.personaRAZONSOCIAL || "Sin asignar";
    const servicio = cita.turnoTipoServicioTITULO || "-";
    const horaCita = formatearHoraCorta(cita.citaFCHCITA);

    subtitulo.textContent = `${nombre} · ${servicio} · ${horaCita}`;

    lista.innerHTML = "";
    const eventos = construirEventosCita(cita);

    if (eventos.length === 0) {
        const li = document.createElement("li");
        li.innerHTML = `
            <span class="timeline-punto"></span>
            <span class="timeline-etiqueta">Sin movimientos registrados</span>
        `;
        lista.appendChild(li);
    } else {
        eventos.forEach(ev => {
            const li = document.createElement("li");
            li.innerHTML = `
                <span class="timeline-punto"></span>
                <div>
                    <span class="timeline-etiqueta">${ev.etiqueta}</span>
                    <span class="timeline-fecha">${ev.fecha}</span>
                    ${ev.detalle ? `<div class="timeline-detalle">${ev.detalle}</div>` : ""}
                </div>
            `;
            lista.appendChild(li);
        });
    }

    modal.classList.add("activo");
    modal.setAttribute("aria-hidden", "false");
}



function cerrarHistorialCita() {
    const modal = document.getElementById("modal-historial");
    modal.classList.remove("activo");
    modal.setAttribute("aria-hidden", "true");
}

