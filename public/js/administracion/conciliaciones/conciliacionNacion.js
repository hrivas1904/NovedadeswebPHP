(function () {
// =====================================================================
// conciliacionMacro.js -- version con DataTables
// Para Nacion/Francés 986/Francés 1001: duplicar y cambiar SOLO la
// linea de CONCILIACION_BANCO de aca abajo.
// =====================================================================

const CONCILIACION_BANCO = 'NACION';
const SCOPE = '[data-conciliacion-banco="' + CONCILIACION_BANCO + '"] ';

let filasConciliacion = [];
let resumenConciliacion = { saldo_inicio: 0, primer_mes: null };
let tablaConciliacion = null;
let restaurandoScroll = false;

function fmtPesos(v) {
    const n = Number(v || 0);
    const signo = n < 0 ? '-' : '';
    return signo + '$\u202f' + Math.abs(n).toLocaleString('es-AR', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
}

function debounce(fn, wait) {
    let t;
    return function (...args) {
        clearTimeout(t);
        t = setTimeout(() => fn.apply(this, args), wait);
    };
}

// ── Collapse de las cards ────────────────────────────────────────────
$(document).on('click', SCOPE + '.collapsible-header', function () {
    $(this).siblings('.card-body').toggleClass('d-none');
    $(this).find('i').toggleClass('fa-circle-chevron-down fa-circle-chevron-up');
});

// ── Formato del extracto ─────────────────────────────────────────────
const COLUMNAS_POR_FORMATO_CONC = {
    'NACION': {
        PEGADO: 'Fecha | Descripción | Débito | Crédito',
        EXCEL:  'Fecha | — | Concepto | Detalle | Importe | Saldo',
    },
};

let formatoConciliacion = 'PEGADO';

function actualizarHeaderColumnasConc() {
    const cols = (COLUMNAS_POR_FORMATO_CONC[CONCILIACION_BANCO] || {})[formatoConciliacion] || '';
    $('#headerColumnas').text(cols ? 'Columnas esperadas: ' + cols : '');
}

$(document).on('click', SCOPE + '#btnPegado', function () {
    $('#btnPegado, #btnExcel').removeClass('active');
    $(this).addClass('active');
    formatoConciliacion = 'PEGADO';
    actualizarHeaderColumnasConc();
});

$(document).on('click', SCOPE + '#btnExcel', function () {
    $('#btnPegado, #btnExcel').removeClass('active');
    $(this).addClass('active');
    formatoConciliacion = 'EXCEL';
    actualizarHeaderColumnasConc();
});

// ── DataTable ─────────────────────────────────────────────────────────
function renderTablaConciliacion() {
    const filas = filasFiltradas();

    // Estado actual de la tabla
    let paginaActual = 0;
    let pageLengthActual = 50;
    let scrollTopActual = 0;

    // Si la tabla ya existe, guardamos su estado antes de destruirla
    if ($.fn.DataTable.isDataTable('#tbMovimientos')) {
        const tablaVieja = $('#tbMovimientos').DataTable();

        paginaActual = tablaVieja.page();
        pageLengthActual = tablaVieja.page.len();

        // Guardamos la posición actual del scroll interno
        scrollTopActual = $('#tbMovimientos')
            .closest('.dt-scroll-body')
            .scrollTop();

        tablaVieja.destroy();
    }

    // Creamos nuevamente el DataTable
    tablaConciliacion = $('#tbMovimientos').DataTable({
        data: filas,
        language: {
            url: '/js/es-ES.json'
        },

        lengthMenu: [
            10,
            15,
            25,
            50,
            75,
            100,
            { label: 'Todos', value: -1 }
        ],

        pageLength: pageLengthActual,

        scrollY: getScrollY(),
        scrollCollapse: true,

        order: [[1, 'desc']],

        dom: "<'d-flex justify-content-start mb-2'l>t<'d-flex justify-content-between mt-2'ip>",

        buttons: [
            {
                extend: 'excelHtml5',
                text: 'Exportar',
                exportOptions: {
                    columns: [1, 2, 3, 4, 5, 6, 7, 8]
                },
            },
        ],

        columns: [
            {
                data: null, orderable: false, className: 'text-center', width: '130px',
                render: function (row) {
                    const badgeNuevo = row.nuevo_en_conciliacion == 1
                        ? '<span class="badge" style="background:#1A5CA8; font-size:0.65rem;">NUEVO</span> '
                        : '';
                    const estado = row.estado_conciliacion;
                    const badgeEstado = estado === 'FINN'
                        ? '<span class="badge" style="background:#F9A825; font-size:0.65rem;">F</span> '
                        : estado === 'QR'
                        ? '<span class="badge" style="background:#E55A3A; font-size:0.65rem;">QR</span> '
                        : '';
            
                    const tieneComentario = row.comentario && row.comentario.trim() !== '';
                    const comentarioEscapado = (row.comentario || '').replace(/"/g, '&quot;');
                    const btnComentario = '<button type="button" class="btn btn-sm btn-comentario ' +
                        (tieneComentario ? 'text-warning' : 'text-muted') + '" data-id="' + row.id +
                        '" data-comentario="' + comentarioEscapado + '" title="' +
                        (tieneComentario ? 'Ver/editar comentario' : 'Agregar comentario') +
                        '"><i class="fa-solid fa-comment-dots"></i></button>';
            
                    return badgeNuevo + badgeEstado + btnComentario + '<input type="checkbox" class="chk-conc ms-1" data-id="' + row.id + '">';
                }
            },

            {
                data: 'fecha'
            },

            {
                data: 'nro_comprobante',
                orderable: false,

                render: function (val, type, row) {

                    if (type !== 'display') {
                        return val || '';
                    }

                    return `
                        <input
                            type="text"
                            readonly
                            class="form-control form-control-sm input-comprobante"
                            data-id="${row.id}"
                            value="${val || ''}"
                            data-original="${val || ''}"
                        >
                    `;
                }
            },

            {
                data: 'operacion',
                render: (v) => v || ''
            },

            {
                data: 'concepto'
            },

            {
                data: 'subconcepto',
                render: (v) => v || ''
            },

            {
                data: 'detalle'
            },

            {
                data: 'importe',
                className: 'text-end',

                render: function (v) {

                    return `
                        <span class="fw-bold ${v >= 0 ? 'text-success' : 'text-danger'}">
                            ${fmtPesos(v)}
                        </span>
                    `;
                }
            },

            {
                data: 'saldo_acum',
                className: 'text-end',

                render: function (v) {

                    return `
                        <span class="fw-bold">
                            ${fmtPesos(v)}
                        </span>
                    `;
                }
            },
        ],

        rowCallback: function (row, data) {

            const estado = data.estado_conciliacion || '';
            const esNuevo = data.nuevo_en_conciliacion == 1;

            const bg = esNuevo
                ? 'rgba(26, 92, 168, 0.12)'
                : estado === 'FINN'
                    ? 'rgba(249, 168, 37, 0.18)'
                    : estado === 'QR'
                        ? 'rgba(229, 90, 58, 0.18)'
                        : '';

            $(row)
                .find('td')
                .css('background-color', bg);
        },

        initComplete: function () {
            const api = this.api();
            const paginasDisponibles = api.page.info().pages;
        
            if (paginaActual > 0 && paginaActual < paginasDisponibles) {
                restaurandoScroll = true;
                api.page(paginaActual).draw(false);
            }
        
            $('#tbMovimientos').closest('.dt-scroll-body').scrollTop(scrollTopActual);
        
            api.off('draw.dt.scrollreset').on('draw.dt.scrollreset', function () {
                if (restaurandoScroll) {
                    restaurandoScroll = false;
                    return;
                }
                $('#tbMovimientos').closest('.dt-scroll-body').scrollTop(0);
            });
        },
    });
}

// ── Carga de datos ────────────────────────────────────────────────────
function cargarConciliacion() {
    const fechaHasta = $('#inputFechaHasta').val() || new Date().toISOString().slice(0, 10);

    $.get(CONCILIACION_ROUTES.data, { banco: CONCILIACION_BANCO, fechaHasta: fechaHasta }, function (data) {
        resumenConciliacion = data.resumen;
        filasConciliacion = data.movimientos;
        renderResumen();
        renderFiltroPendientes();
        renderTablaConciliacion();
    });
}

function renderResumen() {
    const totalFinn = filasConciliacion.filter(r => r.estado_conciliacion === 'FINN').reduce((a, r) => a + Number(r.importe), 0);
    const totalQR   = filasConciliacion.filter(r => r.estado_conciliacion === 'QR').reduce((a, r) => a + Number(r.importe), 0);
    const nFinn = filasConciliacion.filter(r => r.estado_conciliacion === 'FINN').length;
    const nQR   = filasConciliacion.filter(r => r.estado_conciliacion === 'QR').length;
    const saldoExtracto = filasConciliacion.length
        ? Number(filasConciliacion[filasConciliacion.length - 1].saldo_acum)
        : Number(resumenConciliacion.saldo_inicio);

    $('#importeSaldoInicial').text(fmtPesos(resumenConciliacion.saldo_inicio));
    $('#importeSaldoExtracto').text(fmtPesos(saldoExtracto));
    $('#importePendFinnegans').text(nFinn > 0 ? fmtPesos(totalFinn) + ' (' + nFinn + ')' : '—');
    $('#importePendQr').text(nQR > 0 ? fmtPesos(totalQR) + ' (' + nQR + ')' : '—');
    $('#importeSaldoContable').text(fmtPesos(saldoExtracto - totalFinn - totalQR));
}

let filtroPendientes = ''; // '' | 'NUEVO' | 'FINN' | 'QR' | 'AMBOS'

function renderFiltroPendientes() {
    const nNuevos = filasConciliacion.filter(r => r.nuevo_en_conciliacion == 1).length;
    const nFinn   = filasConciliacion.filter(r => r.estado_conciliacion === 'FINN').length;
    const nQR     = filasConciliacion.filter(r => r.estado_conciliacion === 'QR').length;
    const nAmbos  = nFinn + nQR;

    const opciones = [
        ['', 'Todos'],
        ['NUEVO', 'Nuevos (' + nNuevos + ')'],
        ['FINN', 'F (' + nFinn + ')'],
        ['QR', 'QR (' + nQR + ')'],
        ['AMBOS', 'Ambos (' + nAmbos + ')'],
    ];

    let html = '<span class="text-muted small me-2">Pendientes:</span>';
    opciones.forEach(function (op) {
        const activo = filtroPendientes === op[0];
        html += '<button type="button" class="btn btn-sm ' + (activo ? 'btn-primary' : 'btn-outline-secondary') + ' btn-filtro-pendiente me-1" data-valor="' + op[0] + '">' + op[1] + '</button>';
    });

    $('#filtroPendientesWrapper').html(html);
}

$(document).on('click', SCOPE + '.btn-filtro-pendiente', function () {
    filtroPendientes = $(this).data('valor');
    renderFiltroPendientes();
    renderTablaConciliacion();
});

function filasFiltradas() {
    const concepto = $('#selectorConcepto').val();
    const operacion = $('#selectorOperaciones').val();
    const sub = ($('#inputSubconceptos').val() || '').toLowerCase();
    const texto = ($('#inputBuscador').val() || '').toLowerCase();
    const desde = $('#inputFechaDesde').val();
 
    return filasConciliacion.filter(function (r) {
        if (concepto && r.concepto !== concepto) return false;
        if (operacion && r.operacion !== operacion) return false;
        if (sub && !(r.subconcepto || '').toLowerCase().includes(sub)) return false;
        if (texto) {
            const importeCrudo = String(r.importe);
            const importeFormateado = fmtPesos(r.importe).toLowerCase();
            const coincideTexto =
                (r.detalle || '').toLowerCase().includes(texto) ||
                (r.nro_comprobante || '').toLowerCase().includes(texto) ||
                importeCrudo.includes(texto) ||
                importeFormateado.includes(texto);
            if (!coincideTexto) return false;
        }
        if (desde && r.fecha < desde) return false;
        if (filtroPendientes === 'NUEVO' && !(r.nuevo_en_conciliacion == 1)) return false;
        if (filtroPendientes === 'FINN' && r.estado_conciliacion !== 'FINN') return false;
        if (filtroPendientes === 'QR' && r.estado_conciliacion !== 'QR') return false;
        if (filtroPendientes === 'AMBOS' && !(r.estado_conciliacion === 'FINN' || r.estado_conciliacion === 'QR')) return false;
        return true;
    });
}

// ── Selects de concepto/operacion ────────────────────────────────────
function poblarSelectsConciliacion() {
    let optsConceptos = '<option value="">Conceptos</option>';
    CONCEPTOS_CATALOGO.forEach(function (c) {
        optsConceptos += '<option value="' + c + '">' + c + '</option>';
    });
    $('#selectorConcepto').html(optsConceptos);

    const operaciones = ['INGRESOS', 'TRANSFERENCIAS', 'CHEQUES', 'EFECTIVO'];
    let optsOp = '<option value="">Operaciones</option>';
    operaciones.forEach(function (op) {
        optsOp += '<option value="' + op + '">' + op + '</option>';
    });
    $('#selectorOperaciones').html(optsOp);
}

$(document).on('change', SCOPE + '#selectorConcepto' + ', ' + SCOPE + '#selectorOperaciones' + ', ' + SCOPE + '#inputFechaDesde', renderTablaConciliacion);
$(document).on('input', SCOPE + '#inputSubconceptos' + ', ' + SCOPE + '#inputBuscador', debounce(renderTablaConciliacion, 300));
$(document).on('change', SCOPE + '#inputFechaHasta', cargarConciliacion);

// ── Marcar F / QR ─────────────────────────────────────────────────────
// ── Comprobante editable ──────────────────────────────────────────────
$(document).on('blur', SCOPE + '.input-comprobante', function () {
    const $input = $(this);
    const id = $input.data('id');
    const valor = $input.val().trim();
    if (valor === String($input.data('original'))) return;

    $.post(CONCILIACION_ROUTES.comprobante.replace(':id', id), { nroComprobante: valor }, function () {
        const fila = filasConciliacion.find(r => r.id === id);
        if (fila) fila.nro_comprobante = valor;
        $input.data('original', valor);
    });
});

// ── Seleccion multiple (checkbox), suma de seleccionados, seleccionar todos ──
// Usa la API de DataTables (no jQuery plano) para que "seleccionar todos"
// funcione sobre TODAS las filas filtradas, no solo las de la pagina actual.
function idsSeleccionados() {
    let ids = [];
    tablaConciliacion.rows({ search: 'applied' }).nodes().to$().find('.chk-conc:checked').each(function () {
        ids.push($(this).data('id'));
    });
    return ids;
}

function actualizarSumaSeleccionados() {
    const ids = idsSeleccionados();
    const suma = filasConciliacion
        .filter(r => ids.includes(r.id))
        .reduce((a, r) => a + Number(r.importe), 0);
    $('#sumaSeleccionados').text(ids.length + ' seleccionados · ' + fmtPesos(suma));
}

$(document).on('change', SCOPE + '.chk-conc', actualizarSumaSeleccionados);

$(document).on('change', SCOPE + '#chkSeleccionarTodos', function () {
    const checked = $(this).is(':checked');
    tablaConciliacion.rows({ search: 'applied' }).nodes().to$().find('.chk-conc').prop('checked', checked);
    actualizarSumaSeleccionados();
});

// Agregar junto a los demas handlers de Conciliacion (por ejemplo, cerca
// del de .input-comprobante)

$(document).on('click', SCOPE + '.btn-comentario', function () {
    const $btn = $(this);
    const id = $btn.data('id');
    const comentarioActual = $btn.data('comentario') || '';

    Swal.fire({
        title: 'Comentario',
        input: 'textarea',
        inputValue: comentarioActual,
        inputPlaceholder: 'Escribí un comentario para este movimiento...',
        inputAttributes: { maxlength: 500 },
        showCancelButton: true,
        confirmButtonText: 'Guardar',
        cancelButtonText: 'Cancelar',
        buttonsStyling: false,
        customClass: {
            confirmButton: 'btn btn-primary me-2',
            cancelButton: 'btn btn-secondary',
        },
    }).then(function (result) {
        if (!result.isConfirmed) return;

        const nuevoComentario = (result.value || '').trim();

        $.post(CONCILIACION_ROUTES.comentario.replace(':id', id), { comentario: nuevoComentario }, function () {
            const fila = filasConciliacion.find(r => r.id === id);
            if (fila) fila.comentario = nuevoComentario;

            $btn.data('comentario', nuevoComentario);
            const tieneComentario = nuevoComentario !== '';
            $btn.toggleClass('text-warning', tieneComentario).toggleClass('text-muted', !tieneComentario);
            $btn.attr('title', tieneComentario ? 'Ver/editar comentario' : 'Agregar comentario');
        });
    });
});

// ── Acciones masivas ──────────────────────────────────────────────────

function aplicarEstadoMasivo(estado) {
    const ids = idsSeleccionados();
    if (!ids.length) {
        alert('Seleccioná al menos un movimiento.');
        return;
    }

    $.post(CONCILIACION_ROUTES.estadoMasivo, { ids: ids, estado: estado }, function () {
        ids.forEach(function (id) {
            const fila = filasConciliacion.find(r => r.id === id);
            if (fila) {
                fila.estado_conciliacion = estado || null;
                fila.nuevo_en_conciliacion = 0;
            }
        });
        renderResumen();
        renderFiltroPendientes();
        renderTablaConciliacion();
        $('#chkSeleccionarTodos').prop('checked', false);
        actualizarSumaSeleccionados(); // vuelve a 0, la tabla se redibujo y perdio la seleccion
    });
}

$(document).on('click', SCOPE + '#btnMarcarFinnMasivo', function () { aplicarEstadoMasivo('FINN'); });
$(document).on('click', SCOPE + '#btnMarcarQrMasivo', function () { aplicarEstadoMasivo('QR'); });
$(document).on('click', SCOPE + '#btnLimpiarEstadoMasivo', function () { aplicarEstadoMasivo(''); });

// Boton de exportar propio (con tu estilo) -- dispara el mecanismo de
// exportacion de DataTables sin mostrar el boton automatico duplicado.
$(document).on('click', SCOPE + '#btnExportarExcel', function () {
    tablaConciliacion.button(0).trigger();
});

$(document).on('subvista:cargada', function () {
    if (!$('[data-conciliacion-banco="' + CONCILIACION_BANCO + '"]').length) return; // no es la sub-vista de este banco especifico

    poblarSelectsConciliacion();
    actualizarHeaderColumnasConc();
    $('#inputFechaHasta').val(new Date().toISOString().slice(0, 10));
    cargarConciliacion();
});

// =====================================================================
// EXTRACTO -- reutiliza los mismos endpoints de Importacion > Bancos
// (mismo motor de clasificacion, mismo aprendizaje de reglas al confirmar)
// =====================================================================
let filasExtracto = [];

function renderPreviewExtracto() {
    if (!filasExtracto.length) {
        $('#previewExtractoWrapper').hide();
        return;
    }

    let opts = '';
    CONCEPTOS_CATALOGO.forEach(function (c) {
        opts += '<option value="' + c + '">' + c + '</option>';
    });

    let html = '';
    function optsSubconcepto(concepto, actual) {
        const lista = SUBCONCEPTOS_POR_CONCEPTO[concepto] || [];
        let out = '';
        lista.forEach(function (s) {
            out += '<option value="' + s + '"' + (s === actual ? ' selected' : '') + '>' + s + '</option>';
        });
        return out;
    }

    filasExtracto.forEach(function (r, i) {
        let optsFila = opts.replace('value="' + r.concepto + '"', 'value="' + r.concepto + '" selected');
        html += '<tr>' +
            '<td>' + r.fecha + '</td>' +
            '<td>' + (r.nro_comprobante || '') + '</td>' +
            '<td><select class="form-select form-select-sm select-concepto-extracto" data-idx="' + i + '">' + optsFila + '</select></td>' +
            '<td><select class="form-select form-select-sm select-subconcepto-extracto" data-idx="' + i + '">' + optsSubconcepto(r.concepto, r.subconcepto) + '</select></td>' +
            '<td><input type="text" class="form-control form-control-sm input-detalle-extracto" data-idx="' + i + '" value="' + (r.detalle || '').replace(/"/g, '&quot;') + '"></td>' +
            '<td class="text-end fw-bold ' + (r.importe >= 0 ? 'text-success' : 'text-danger') + '">' + fmtPesos(r.importe) + '</td>' +
            '</tr>';
    });

    $('#previewExtractoBody').html(html);
    $('#cantidadExtracto, #cantidadExtracto2').text(filasExtracto.length);
    $('#previewExtractoWrapper').show();
}

function procesarExtracto() {
    const contenido = $('#textAreaArchivo').val();
    if (!contenido.trim()) {
        filasExtracto = [];
        renderPreviewExtracto();
        $('#msgExtracto').text('');
        return;
    }

    $.post(CONCILIACION_ROUTES.extractoPreview, {
        contenido: contenido,
        banco: CONCILIACION_BANCO,
        formato: formatoConciliacion,
    }, function (data) {
        filasExtracto = data.rows;
        $('#msgExtracto').text(data.mensaje).css('color', filasExtracto.length ? 'green' : 'inherit');
        renderPreviewExtracto();
    });
}

$(document).on('input', SCOPE + '#textAreaArchivo', debounce(procesarExtracto, 400));

$(document).on('change', SCOPE + '#inputArchivo', function () {
    if (!this.files || !this.files[0]) return;
    const formData = new FormData();
    formData.append('archivo', this.files[0]);
    formData.append('banco', CONCILIACION_BANCO);
    formData.append('formato', formatoConciliacion);

    $.ajax({
        url: CONCILIACION_ROUTES.extractoPreview,
        type: 'POST', data: formData, processData: false, contentType: false,
        success: function (data) {
            filasExtracto = data.rows;
            $('#msgExtracto').text(data.mensaje).css('color', filasExtracto.length ? 'green' : 'inherit');
            renderPreviewExtracto();
        },
    });
});

$(document).on('change', SCOPE + '.select-concepto-extracto', function () {
    const idx = $(this).data('idx');
    const nuevoConcepto = $(this).val();
    filasExtracto[idx].concepto = nuevoConcepto;

    // Al cambiar el concepto, el subconcepto ya no es valido -- se repuebla
    // el select con la lista del concepto nuevo, sin preseleccionar nada
    // (fuerza a elegir uno coherente en vez de arrastrar el anterior).
    const lista = SUBCONCEPTOS_POR_CONCEPTO[nuevoConcepto] || [];
    let opts = '';
    lista.forEach(function (s) { opts += '<option value="' + s + '">' + s + '</option>'; });
    const $selectSub = $('.select-subconcepto-extracto[data-idx="' + idx + '"]');
    $selectSub.html(opts);
    filasExtracto[idx].subconcepto = lista[0] || '';
});

$(document).on('change', SCOPE + '.select-subconcepto-extracto', function () {
    filasExtracto[$(this).data('idx')].subconcepto = $(this).val();
});

$(document).on('input', SCOPE + '.input-detalle-extracto', function () {
    filasExtracto[$(this).data('idx')].detalle = $(this).val();
});

$(document).on('click', SCOPE + '#btnConfirmarExtracto', function () {
    if (!filasExtracto.length) return;

    $.post(CONCILIACION_ROUTES.extractoConfirmar, { rows: filasExtracto, origenConciliacion: 1 }, function (data) {
        $('#msgExtracto').text('✓ ' + data.insertados + ' movimientos importados.').css('color', 'green');
        $('#textAreaArchivo').val('');
        filasExtracto = [];
        renderPreviewExtracto();
        cargarConciliacion(); // refresca la tabla de abajo con los movimientos recien importados
    });
});

})();