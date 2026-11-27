$(function () {

    const ESCALA     = EDD.escala;
    const EDITA_AUTO = EDD.modo === 'AUTO';
    const EDITA_SUP  = EDD.modo === 'SUPERIOR';
    const CSRF       = $('meta[name="csrf-token"]').attr('content');

    let respuestas = [];
    let hayCambios = false;

    /* ============================ HELPERS ============================ */

    function esc(texto) {
        return $('<div>').text(texto ?? '').html();
    }

    function formatear(n) {
        return n === null ? '-' : n.toFixed(2).replace('.', ',');
    }

    // Hasta X,50 → X · desde X,51 → X+1 (misma regla que App\Support\Edd)
    function valoracion(prom) {
        if (prom === null) return null;
        return Math.max(1, Math.min(4, Math.ceil(Math.round(prom * 100) / 100 - 0.5)));
    }

    function mostrarError(xhr) {
        const msg = (xhr.responseJSON && xhr.responseJSON.message) || 'Ocurrió un error inesperado.';
        Swal.fire({ icon: 'error', title: 'No se pudo completar', text: msg });
    }

    function postJson(url, payload) {
        return $.ajax({
            url: url,
            method: 'POST',
            data: JSON.stringify(payload),
            contentType: 'application/json',
            headers: { 'X-CSRF-TOKEN': CSRF },
        });
    }

    /* ============================ RENDER CELDAS ============================ */

    function selectPuntaje(clase, id, valor) {
        let html = '<select class="form-select form-select-sm ' + clase + '" data-id="' + id + '">'
                 + '<option value="">Seleccione</option>';
        [4, 3, 2, 1].forEach(function (n) {
            html += '<option value="' + n + '" title="' + esc(ESCALA[n].concepto) + '"'
                  + (Number(valor) === n ? ' selected' : '') + '>'
                  + n + ' · ' + esc(ESCALA[n].label) + '</option>';
        });
        return html + '</select>';
    }

    function textareaComentario(clase, id, valor) {
        return '<textarea class="form-control form-control-sm ' + clase + '" data-id="' + id
             + '" rows="2" maxlength="2000">' + esc(valor) + '</textarea>';
    }

    function textoPuntaje(v) {
        if (v === null || v === undefined || v === '') return '<span class="text-muted">-</span>';
        return '<span class="fw-bold">' + v + '</span> <small class="text-muted">'
             + esc(ESCALA[v] ? ESCALA[v].label : '') + '</small>';
    }

    function textoComentario(v) {
        return v
            ? '<div class="small" style="white-space: pre-line;">' + esc(v) + '</div>'
            : '<span class="text-muted">-</span>';
    }

    const COLUMNAS = [
        { data: null, className: 'text-center', width: '40px',
          render: function (d, t, r, meta) { return meta.row + 1; } },
        { data: 'competencia',
          render: function (d, t, r) {
              return '<div class="fw-bold">' + esc(d) + '</div><small class="text-muted">' + esc(r.descripcion) + '</small>';
          } },
        { data: 'puntaje_auto', width: '210px',
          render: function (d, t, r) { return EDITA_AUTO ? selectPuntaje('sel-auto', r.id, d) : textoPuntaje(d); } },
        { data: 'comentario_evaluado',
          render: function (d, t, r) { return EDITA_AUTO ? textareaComentario('txt-auto', r.id, d) : textoComentario(d); } },
        { data: 'puntaje_superior', width: '210px',
          render: function (d, t, r) { return EDITA_SUP ? selectPuntaje('sel-sup', r.id, d) : textoPuntaje(d); } },
        { data: 'comentario_evaluador',
          render: function (d, t, r) { return EDITA_SUP ? textareaComentario('txt-sup', r.id, d) : textoComentario(d); } },
    ];

    function crearTabla(selector, data) {
        return $(selector).DataTable({
            data: data,
            columns: COLUMNAS,
            paging: false,
            searching: false,
            ordering: false,
            info: false,
            autoWidth: false,
            language: { emptyTable: 'Sin competencias' },
        });
    }

    /* ============================ CARGA ============================ */

    function cargarRespuestas() {
        $.get(EDD.routes.respuestas)
            .done(function (res) {
                respuestas = res.data;
                crearTabla('#tbCompetenciasOrg',  respuestas.filter(function (r) { return r.tipo === 'ORGANIZACIONAL'; }));
                crearTabla('#tbCompetenciasFunc', respuestas.filter(function (r) { return r.tipo === 'FUNCIONAL'; }));
                calcularPromedios();
            })
            .fail(mostrarError);
    }

    /* ============================ PROMEDIOS ============================ */

    // valor actual: del select si esa parte se está editando, si no del dato
    function valorDe(r, parte) {
        if (parte === 'AUTO' && EDITA_AUTO) return $('.sel-auto[data-id="' + r.id + '"]').val();
        if (parte === 'SUPERIOR' && EDITA_SUP) return $('.sel-sup[data-id="' + r.id + '"]').val();
        return parte === 'AUTO' ? r.puntaje_auto : r.puntaje_superior;
    }

    // promedio solo si están todas calificadas (si no, '-')
    function promedio(lista, parte) {
        if (!lista.length) return null;
        const vals = lista.map(function (r) { return valorDe(r, parte); })
                          .filter(function (v) { return v !== null && v !== undefined && v !== ''; })
                          .map(Number);
        if (vals.length < lista.length) return null;
        const suma = vals.reduce(function (a, b) { return a + b; }, 0);
        return Math.round((suma / vals.length) * 100) / 100;
    }

    function calcularPromedios() {
        const org  = respuestas.filter(function (r) { return r.tipo === 'ORGANIZACIONAL'; });
        const func = respuestas.filter(function (r) { return r.tipo === 'FUNCIONAL'; });

        $('#promAutoOrg').text(formatear(promedio(org, 'AUTO')));
        $('#promAutoFunc').text(formatear(promedio(func, 'AUTO')));
        $('#promAutoGral').text(formatear(promedio(respuestas, 'AUTO')));

        const supGral = promedio(respuestas, 'SUPERIOR');
        $('#promSupOrg').text(formatear(promedio(org, 'SUPERIOR')));
        $('#promSupFunc').text(formatear(promedio(func, 'SUPERIOR')));
        $('#promSupGral').text(formatear(supGral));

        const v = valoracion(supGral);
        $('#lblEvaluacionFinal').text(formatear(supGral));
        $('#lblEvaluacionFinalConcepto').text(v ? ESCALA[v].label : '');
    }

    $(document).on('change', '.sel-auto, .sel-sup', calcularPromedios);

    /* ============================ GUARDAR / ENVIAR ============================ */

    function recolectar() {
        const sel = EDITA_AUTO ? '.sel-auto' : '.sel-sup';
        const txt = EDITA_AUTO ? '.txt-auto' : '.txt-sup';

        const payload = {
            parte: EDITA_AUTO ? 'AUTO' : 'SUPERIOR',
            respuestas: respuestas.map(function (r) {
                return {
                    id:         r.id,
                    puntaje:    $(sel + '[data-id="' + r.id + '"]').val() || null,
                    comentario: $(txt + '[data-id="' + r.id + '"]').val() || null,
                };
            }),
        };

        if (EDITA_SUP) payload.retroalimentacion = $('#inputRetroalimentacion').val() || null;
        return payload;
    }

    function guardar($btn) {
        $btn.prop('disabled', true);
        postJson(EDD.routes.guardar, recolectar())
            .done(function (res) {
                hayCambios = false;
                Swal.fire({ icon: 'success', title: res.message, timer: 1500, showConfirmButton: false });
            })
            .fail(mostrarError)
            .always(function () { $btn.prop('disabled', false); });
    }

    function enviar($btn) {
        const payload = recolectar();
        const faltan  = payload.respuestas.filter(function (r) { return !r.puntaje; }).length;

        if (faltan > 0) {
            Swal.fire({ icon: 'warning', title: 'Evaluación incompleta',
                        text: 'Falta calificar ' + faltan + (faltan === 1 ? ' competencia.' : ' competencias.') });
            return;
        }

        const texto = EDITA_AUTO
            ? 'Una vez enviada no vas a poder modificar tu autoevaluación.'
            : 'Al enviar, la evaluación queda firmada por vos y el colaborador podrá verla. No se puede modificar después.';

        Swal.fire({
            icon: 'question',
            title: EDITA_AUTO ? '¿Enviar autoevaluación?' : '¿Enviar y firmar la evaluación?',
            text: texto,
            showCancelButton: true,
            confirmButtonText: 'Enviar',
            cancelButtonText: 'Cancelar',
        }).then(function (r) {
            if (!r.isConfirmed) return;

            $btn.prop('disabled', true);
            postJson(EDD.routes.enviar, payload)
                .done(function (res) {
                    hayCambios = false;
                    Swal.fire({ icon: 'success', title: res.message, timer: 1500, showConfirmButton: false })
                        .then(function () { location.reload(); });
                })
                .fail(function (xhr) { mostrarError(xhr); $btn.prop('disabled', false); });
        });
    }

    $('#btnGuardarAuto, #btnGuardarSuperior').on('click', function () { guardar($(this)); });
    $('#btnEnviarAuto, #btnEnviarSuperior').on('click', function () { enviar($(this)); });

    /* ============================ FIRMA DEL EVALUADO ============================ */

    $('#btnFirmarEvaluado').on('click', function () {
        const $btn = $(this);

        Swal.fire({
            icon: 'question',
            title: '¿Firmar la toma de conocimiento?',
            text: 'Confirmás que leíste la evaluación. La firma no implica conformidad: podés dejar tu observación.',
            showCancelButton: true,
            confirmButtonText: 'Firmar',
            cancelButtonText: 'Cancelar',
        }).then(function (r) {
            if (!r.isConfirmed) return;

            $btn.prop('disabled', true);
            postJson(EDD.routes.firmar, { observacion: $('#inputObservacionEvaluado').val() || null })
                .done(function (res) {
                    Swal.fire({ icon: 'success', title: res.message, timer: 1500, showConfirmButton: false })
                        .then(function () { location.reload(); });
                })
                .fail(function (xhr) { mostrarError(xhr); $btn.prop('disabled', false); });
        });
    });

    /* ============================ CAMBIOS SIN GUARDAR ============================ */

    $(document).on('change input', '.sel-auto, .sel-sup, .txt-auto, .txt-sup, #inputRetroalimentacion', function () {
        hayCambios = true;
    });

    $(window).on('beforeunload', function () {
        if (hayCambios) return 'Hay cambios sin guardar.';
    });

    cargarRespuestas();
});