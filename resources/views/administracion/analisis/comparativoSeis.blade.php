<style>
    .celda-detalle-comparativo:hover {
        background: #e8f4fd;
    }
</style>

@php
$fmt = fn($n, $dec = 0) => ($n < 0 ? '-' : '' ) . '$ ' . number_format(abs($n), $dec, ',' , '.' );
    $col=fn($n)=> $n < 0 ? 'text-danger' : '' ;
        @endphp

        <div class="card p-0" style="overflow:hidden;">
        <div style="overflow-x:auto;">
            <table class="table table-sm mb-0" style="border-collapse: collapse; min-width:600px;">

                <thead>
                    <tr style="background-color: var(--color-default);">
                        <th class="fw-semibold" style="padding:8px 10px; min-width:180px;">CONCEPTO</th>
                        @foreach($periodoLabels as $label)
                        <th class="fw-semibold text-end" style="padding:8px 10px; min-width:110px;">{{ $label }}</th>
                        @endforeach
                    </tr>
                </thead>

                <tbody>

                    {{-- SALDO DE INICIO --}}
                    <tr style="background:#F0F7FF; border-bottom:1px solid #dee2e6;">
                        <td class="fw-semibold" style="padding:6px 10px; color:#1B2A4A;">SALDO DE INICIO</td>
                        @foreach($resumen as $r)
                        <td class="text-end fw-bold" style="padding:6px 10px; color:#1B2A4A;">{{ $fmt($r['saldo_inicio']) }}</td>
                        @endforeach
                    </tr>

                    {{-- SECCION: INGRESOS --}}
                    <tr style="background: var(--bs-light, #f8f9fa); border-top:2px solid #17a2b8;">
                        <td colspan="{{ count($periodos) + 1 }}" class="fw-bold text-uppercase" style="padding:6px 10px; color:#1B2A4A; letter-spacing:0.06em;">
                            Ingresos
                        </td>
                    </tr>
                    @forelse($detalle->get('INGRESOS', []) as $fila)
                    <tr style="border-bottom:1px solid #dee2e6;">
                        <td style="padding:5px 10px 5px 20px;">{{ $fila['label'] }}</td>
                        @foreach($fila['valores'] as $v)
                        @php $per = $periodos->get($loop->index); @endphp
                        <td class="text-end {{ $col($v) }} {{ $v != 0 ? 'celda-detalle-comparativo' : '' }}"
                            style="padding:5px 10px;{{ $v != 0 ? ' cursor:pointer;' : '' }}"
                            data-concepto="{{ $fila['concepto'] }}"
                            data-label="{{ $fila['label'] }}"
                            data-periodo="{{ $per }}">{{ $fmt(abs($v)) }}</td>
                        @endforeach
                    </tr>
                    @empty
                    <tr>
                        <td colspan="{{ count($periodos)+1 }}" class="text-muted" style="padding-left:20px;">Sin datos.</td>
                    </tr>
                    @endforelse
                    <tr style="border-top:2px solid #28a745;">
                        <td class="fw-bold" style="padding:7px 10px; color:#28a745;">TOTAL INGRESOS</td>
                        @foreach($resumen as $r)
                        <td class="text-end fw-bold" style="padding:7px 10px; color:#28a745;">{{ $fmt($r['total_ingresos']) }}</td>
                        @endforeach
                    </tr>

                    {{-- SECCION: PAGOS OPERATIVOS --}}
                    <tr style="background: var(--bs-light, #f8f9fa); border-top:2px solid #17a2b8;">
                        <td colspan="{{ count($periodos) + 1 }}" class="fw-bold text-uppercase" style="padding:6px 10px; color:#1B2A4A; letter-spacing:0.06em;">
                            Pagos Operativos
                        </td>
                    </tr>
                    @forelse($detalle->get('PAGOS_OPERATIVOS', []) as $fila)
                    <tr style="border-bottom:1px solid #dee2e6;">
                        <td style="padding:5px 10px 5px 20px;">{{ $fila['label'] }}</td>
                        @foreach($fila['valores'] as $v)
                        @php $per = $periodos->get($loop->index); @endphp
                        <td class="text-end {{ $col($v) }} {{ $v != 0 ? 'celda-detalle-comparativo' : '' }}"
                            style="padding:5px 10px;{{ $v != 0 ? ' cursor:pointer;' : '' }}"
                            data-concepto="{{ $fila['concepto'] }}"
                            data-label="{{ $fila['label'] }}"
                            data-periodo="{{ $per }}"> {{ $fmt($v) }}</td>
                        @endforeach
                    </tr>
                    @empty
                    <tr>
                        <td colspan="{{ count($periodos)+1 }}" class="text-muted" style="padding-left:20px;">Sin datos.</td>
                    </tr>
                    @endforelse
                    <tr style="border-top:2px solid #dc3545;">
                        <td class="fw-bold" style="padding:7px 10px; color:#dc3545;">TOTAL PAGOS OPERATIVOS</td>
                        @foreach($resumen as $r)
                        <td class="text-end fw-bold {{ $col($r['total_pagos_operativos']) }}" style="padding:7px 10px;">{{ $fmt($r['total_pagos_operativos']) }}</td>
                        @endforeach
                    </tr>

                    <tr>
                        <td colspan="{{ count($periodos)+1 }}" style="padding:4px 0;"></td>
                    </tr>

                    {{-- RDO BRUTO OPERATIVO --}}
                    <tr style="background:#F0F8FF; border-top:2px solid #17a2b8; border-bottom:2px solid #17a2b8;">
                        <td class="fw-bold" style="padding:8px 10px; color:#1B2A4A;">RDO. BRUTO OPERATIVO</td>
                        @foreach($resumen as $r)
                        <td class="text-end fw-bold" style="padding:8px 10px; color:#28a745;">{{ $fmt($r['rdo_bruto_operativo']) }}</td>
                        @endforeach
                    </tr>

                    <tr>
                        <td colspan="{{ count($periodos)+1 }}" style="padding:4px 0;"></td>
                    </tr>

                    {{-- SECCION: USO DE FONDOS --}}
                    <tr style="background: var(--bs-light, #f8f9fa); border-top:2px solid #17a2b8;">
                        <td colspan="{{ count($periodos) + 1 }}" class="fw-bold text-uppercase" style="padding:6px 10px; color:#1B2A4A; letter-spacing:0.06em;">
                            Uso de Fondos
                        </td>
                    </tr>
                    @forelse($detalle->get('USO_FONDOS', []) as $fila)
                    <tr style="border-bottom:1px solid #dee2e6;">
                        <td style="padding:5px 10px 5px 20px;">{{ $fila['label'] }}</td>
                        @foreach($fila['valores'] as $v)
                        @php $per = $periodos->get($loop->index); @endphp
                        <td class="text-end {{ $col($v) }} {{ $v != 0 ? 'celda-detalle-comparativo' : '' }}"
                            style="padding:5px 10px;{{ $v != 0 ? ' cursor:pointer;' : '' }}"
                            data-concepto="{{ $fila['concepto'] }}"
                            data-label="{{ $fila['label'] }}"
                            data-periodo="{{ $per }}"> {{ $fmt($v) }}</td>
                        @endforeach
                    </tr>
                    @empty
                    <tr>
                        <td colspan="{{ count($periodos)+1 }}" class="text-muted" style="padding-left:20px;">Sin datos.</td>
                    </tr>
                    @endforelse
                    <tr style="border-top:2px solid #dc3545;">
                        <td class="fw-bold" style="padding:7px 10px; color:#dc3545;">TOTAL USO DE FONDOS</td>
                        @foreach($resumen as $r)
                        <td class="text-end fw-bold {{ $col($r['total_uso_fondos']) }}" style="padding:7px 10px;">{{ $fmt($r['total_uso_fondos']) }}</td>
                        @endforeach
                    </tr>

                    <tr>
                        <td colspan="{{ count($periodos)+1 }}" style="padding:4px 0;"></td>
                    </tr>

                    {{-- FF NETO --}}
                    <tr style="background:#F0F8FF; border-top:2px solid #17a2b8; border-bottom:2px solid #17a2b8;">
                        <td class="fw-bold" style="padding:8px 10px; color:#1B2A4A;">FF NETO</td>
                        @foreach($resumen as $r)
                        <td class="text-end fw-bold" style="padding:8px 10px; color:#28a745;">{{ $fmt($r['ff_neto']) }}</td>
                        @endforeach
                    </tr>

                    <tr>
                        <td colspan="{{ count($periodos)+1 }}" style="padding:4px 0;"></td>
                    </tr>

                    {{-- SALDO FINAL --}}
                    <tr style="background: var(--color-navy, #1B2A4A);">
                        <td class="fw-bold text-white" style="padding:8px 10px;">SALDO FINAL</td>
                        @foreach($resumen as $r)
                        <td class="text-end fw-bold" style="padding:8px 10px; color:#7DCCFF;">{{ $fmt($r['saldo_final']) }}</td>
                        @endforeach
                    </tr>

                </tbody>
            </table>
        </div>
        </div>

        <div class="modal fade" id="modalDetalleComparativo" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog modal-lg modal-dialog-centered">
                <div class="modal-content">
                    <div class="modal-header">
                        <div>
                            <h5 class="modal-title mb-0" id="compNombreConcepto"></h5>
                            <small class="text-muted" id="compMesVista"></small>
                        </div>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body p-0">
                        <div style="max-height:60vh; overflow-y:auto;">
                            <table class="table table-sm table-hover mb-0">
                                <thead style="position:sticky; top:0; background:#fff; z-index:1;">
                                    <tr>
                                        <th>Fecha</th>
                                        <th>Sub-concepto</th>
                                        <th>Detalle</th>
                                        <th class="text-end">Importe</th>
                                    </tr>
                                </thead>
                                <tbody id="compBodyDetalle"></tbody>
                            </table>
                        </div>
                    </div>
                    <div class="modal-footer justify-content-between">
                        <span class="fw-semibold">Total</span>
                        <span class="fw-bold fs-5" id="compTotalDetalle"></span>
                    </div>
                </div>
            </div>
        </div>