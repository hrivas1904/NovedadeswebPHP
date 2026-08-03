(function () {
// =====================================================================
// conciliacionMacro.js -- version con DataTables
// Para Nacion/Francés 986/Francés 1001: duplicar y cambiar SOLO la
// linea de CONCILIACION_BANCO de aca abajo.
// =====================================================================

const CONCILIACION_BANCO = 'MACRO';
const SCOPE = '[data-conciliacion-banco="' + CONCILIACION_BANCO + '"] ';

let filasConciliacion = [];
let resumenConciliacion = { saldo_inicio: 0, primer_mes: null };
let tablaConciliacion = null;

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
    MACRO: {
        PEGADO: 'Fecha | Nro | CódOp | Descripción | Importe | Saldo',
        EXCEL:  'Fecha | Operación | Concepto | Detalle | Nro | Importe | Saldo',
    },
    NACION: {
        PEGADO: 'Fecha | Descripción | Crédito | Débito',
        EXCEL:  'Fecha | — | Concepto | Detalle | Importe | Saldo',
    },
    'FRANCES (986)': {
        PEGADO: 'Fecha | Descripción | CódOp | Crédito | Débito | Saldo',
        EXCEL:  'Fecha | — | Concepto | Detalle | Importe (con signo) | Saldo',
    },
    'FRANCES (1001)': {
        PEGADO: 'Fecha | Descripción | CódOp | Crédito | Débito | Saldo',
        EXCEL:  'Fecha | — | Concepto | Detalle | Importe (con signo) | Saldo',
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
// En vez de mantener la tabla "viva" y solo actualizarle las filas,
// la destruimos y reconstruimos entera cada vez que hay datos nuevos.
// Es mas defensivo: evita cualquier desfasaje entre el objeto guardado
// en tablaConciliacion y la tabla real que existe en el DOM en ese momento.
function renderTablaConciliacion() {
    const filas = filasFiltradas();

    if ($.fn.DataTable.isDataTable('#tbMovimientos')) {
        $('#tbMovimientos').DataTable().destroy();
    }

    tablaConciliacion = $('#tbMovimientos').DataTable({
        data: filas,
        language: { url: '/js/es-ES.json' },
        lengthMenu: [10, 15, 25, 50, 75, 100, { label: 'Todos', value: -1 }],
        pageLength: 10,
        order: [[1, 'desc']],
        dom: "<'d-flex justify-content-start mb-2'l>t<'d-flex justify-content-between mt-2'ip>",
        buttons: [
            {
                extend: 'excelHtml5',
                text: 'Exportar',
                exportOptions: { columns: [1, 2, 3, 4, 5, 6, 7, 8] }, // sin la columna de checkbox/estado
            },
        ],
        columns: [
            {
                data: null, orderable: false, className: 'text-center', width: '110px',
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
                    return badgeNuevo + badgeEstado + '<input type="checkbox" class="chk-conc" data-id="' + row.id + '">';
                }
            },
            { data: 'fecha' },
            {
                data: 'nro_comprobante', orderable: false,
                render: function (val, type, row) {
                    if (type !== 'display') return val || '';
                    return '<input type="text" class="form-control form-control-sm input-comprobante" data-id="' + row.id + '" value="' + (val || '') + '" data-original="' + (val || '') + '">';
                }
            },
            { data: 'operacion', render: (v) => v || '' },
            { data: 'concepto' },
            { data: 'subconcepto', render: (v) => v || '' },
            { data: 'detalle' },
            {
                data: 'importe', className: 'text-end',
                render: function (v) {
                    return '<span class="fw-bold ' + (v >= 0 ? 'text-success' : 'text-danger') + '">' + fmtPesos(v) + '</span>';
                }
            },
            {
                data: 'saldo_acum', className: 'text-end',
                render: function (v) {
                    return '<span class="fw-bold">' + fmtPesos(v) + '</span>';
                }
            },
        ],
        rowCallback: function (row, data) {
            const estado = data.estado_conciliacion || '';
            const esNuevo = data.nuevo_en_conciliacion == 1;
            // Mismo color que el badge de F/QR, pero con transparencia para
            // que el texto de la fila siga siendo legible.
            const bg = esNuevo ? 'rgba(26, 92, 168, 0.12)'
                : estado === 'FINN' ? 'rgba(249, 168, 37, 0.18)'
                : estado === 'QR' ? 'rgba(229, 90, 58, 0.18)'
                : '';
            $(row).find('td').css('background-color', bg); // pinta las celdas, no el <tr> -- table-striped/hover pintan las celdas encima
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
        if (texto && !((r.detalle || '').toLowerCase().includes(texto) || (r.nro_comprobante || '').toLowerCase().includes(texto))) return false;
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
    filasExtracto.forEach(function (r, i) {
        let optsFila = opts.replace('value="' + r.concepto + '"', 'value="' + r.concepto + '" selected');
        html += '<tr>' +
            '<td>' + r.fecha + '</td>' +
            '<td><select class="form-select form-select-sm select-concepto-extracto" data-idx="' + i + '">' + optsFila + '</select></td>' +
            '<td><input type="text" class="form-control form-control-sm input-subconcepto-extracto" data-idx="' + i + '" value="' + (r.subconcepto || '') + '"></td>' +
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
    filasExtracto[$(this).data('idx')].concepto = $(this).val();
});

$(document).on('input', SCOPE + '.input-subconcepto-extracto', function () {
    filasExtracto[$(this).data('idx')].subconcepto = $(this).val();
});

$(document).on('input', SCOPE + '.input-detalle-extracto', function () {
    filasExtracto[$(this).data('idx')].detalle = $(this).val();
});

$(document).on('click', SCOPE + '#btnConfirmarExtracto', function () {
    if (!filasExtracto.length) return;

    $.post(CONCILIACION_ROUTES.extractoConfirmar, { rows: filasExtracto, origenConciliacion: true }, function (data) {
        $('#msgExtracto').text('✓ ' + data.insertados + ' movimientos importados.').css('color', 'green');
        $('#textAreaArchivo').val('');
        filasExtracto = [];
        renderPreviewExtracto();
        cargarConciliacion(); // refresca la tabla de abajo con los movimientos recien importados
    });
});

// =====================================================================
// PAGOS A PROVEEDORES (solo MACRO)
// =====================================================================
let resultadosPagos = [];

function renderPreviewPagos() {
    if (!resultadosPagos.length) {
        $('#previewPagosWrapper').hide();
        return;
    }

    let html = '';
    resultadosPagos.forEach(function (res, i) {
        const hayMatch = res.matches.length > 0;
        const match = res.matches[0];
        html += '<tr>' +
            '<td class="text-center"><input type="checkbox" class="chk-pago" data-idx="' + i + '" ' + (res.confirmado ? 'checked' : '') + ' ' + (hayMatch ? '' : 'disabled') + '></td>' +
            '<td>' + res.pago.nombre + '</td>' +
            '<td>' + (res.pago.fechaPago || '') + '</td>' +
            '<td class="text-end text-danger fw-bold">' + fmtPesos(-res.pago.importe) + '</td>' +
            '<td>' + (hayMatch ? '<span class="text-success">✓ ' + match.detalle + ' (' + match.fecha + ')</span>' : '<span class="text-warning">Sin match</span>') + '</td>' +
            '<td>' + (hayMatch ? match.ejecucion : '—') + '</td>' +
            '</tr>';
    });

    $('#previewPagosBody').html(html);
    const nConfirmados = resultadosPagos.filter(r => r.confirmado).length;
    $('#resumenPagos').text(resultadosPagos.length + ' pagos · ' + nConfirmados + ' a confirmar');
    $('#previewPagosWrapper').show();
}

function procesarPagos() {
    const contenido = $('#textAreaArchivoProv').val();
    if (!contenido.trim()) {
        resultadosPagos = [];
        renderPreviewPagos();
        $('#msgPagos').text('');
        return;
    }

    $.post(CONCILIACION_ROUTES.pagosPreview, { contenido: contenido }, function (data) {
        resultadosPagos = data.resultados;
        $('#msgPagos').text(data.mensaje).css('color', 'inherit');
        renderPreviewPagos();
    });
}

$(document).on('input', SCOPE + '#textAreaArchivoProv', debounce(procesarPagos, 400));

$(document).on('change', SCOPE + '#inputArchivoPagoProv', function () {
    if (!this.files || !this.files[0]) return;
    const reader = new FileReader();
    reader.onload = function (ev) {
        $('#textAreaArchivoProv').val(ev.target.result);
        procesarPagos();
    };
    reader.readAsText(this.files[0], 'UTF-8');
});

$(document).on('change', SCOPE + '.chk-pago', function () {
    resultadosPagos[$(this).data('idx')].confirmado = $(this).is(':checked');
    renderPreviewPagos();
});

$(document).on('click', SCOPE + '#btnConfirmarPagos', function () {
    let ids = [];
    resultadosPagos.forEach(function (r) {
        if (r.confirmado) {
            r.matches.forEach(function (m) { ids.push(m.id); });
        }
    });

    if (!ids.length) return;

    $.post(CONCILIACION_ROUTES.pagosConfirmar, { ids: ids }, function (data) {
        $('#msgPagos').text('✓ ' + data.actualizados + ' presupuesto(s) marcado(s) como CUMPLIDO.').css('color', 'green');
        $('#textAreaArchivoProv').val('');
        resultadosPagos = [];
        renderPreviewPagos();
        cargarConciliacion(); // refresca, por si alguno de esos movimientos aparece en la tabla
    });
});
})();