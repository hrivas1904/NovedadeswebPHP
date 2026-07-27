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
                    <td class="text-end fw-bold" style="padding:6px 10px; color:#1B2A4A;">$ {{ number_format($r['saldo_inicio'], 0, ',', '.') }}</td>
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
                    <td class="text-end" style="padding:5px 10px;">$ {{ number_format(abs($v), 0, ',', '.') }}</td>
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
                    <td class="text-end fw-bold" style="padding:7px 10px; color:#28a745;">$ {{ number_format($r['total_ingresos'], 0, ',', '.') }}</td>
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
                    <td class="text-end" style="padding:5px 10px;">$ {{ number_format(abs($v), 0, ',', '.') }}</td>
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
                    <td class="text-end fw-bold" style="padding:7px 10px; color:#dc3545;">$ {{ number_format(abs($r['total_pagos_operativos']), 0, ',', '.') }}</td>
                    @endforeach
                </tr>

                <tr>
                    <td colspan="{{ count($periodos)+1 }}" style="padding:4px 0;"></td>
                </tr>

                {{-- RDO BRUTO OPERATIVO --}}
                <tr style="background:#F0F8FF; border-top:2px solid #17a2b8; border-bottom:2px solid #17a2b8;">
                    <td class="fw-bold" style="padding:8px 10px; color:#1B2A4A;">RDO. BRUTO OPERATIVO</td>
                    @foreach($resumen as $r)
                    <td class="text-end fw-bold" style="padding:8px 10px; color:#28a745;">$ {{ number_format($r['rdo_bruto_operativo'], 0, ',', '.') }}</td>
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
                    <td class="text-end" style="padding:5px 10px;">$ {{ number_format(abs($v), 0, ',', '.') }}</td>
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
                    <td class="text-end fw-bold" style="padding:7px 10px; color:#dc3545;">$ {{ number_format(abs($r['total_uso_fondos']), 0, ',', '.') }}</td>
                    @endforeach
                </tr>

                <tr>
                    <td colspan="{{ count($periodos)+1 }}" style="padding:4px 0;"></td>
                </tr>

                {{-- FF NETO --}}
                <tr style="background:#F0F8FF; border-top:2px solid #17a2b8; border-bottom:2px solid #17a2b8;">
                    <td class="fw-bold" style="padding:8px 10px; color:#1B2A4A;">FF NETO</td>
                    @foreach($resumen as $r)
                    <td class="text-end fw-bold" style="padding:8px 10px; color:#28a745;">$ {{ number_format($r['ff_neto'], 0, ',', '.') }}</td>
                    @endforeach
                </tr>

                <tr>
                    <td colspan="{{ count($periodos)+1 }}" style="padding:4px 0;"></td>
                </tr>

                {{-- SALDO FINAL --}}
                <tr style="background: var(--color-navy, #1B2A4A);">
                    <td class="fw-bold text-white" style="padding:8px 10px;">SALDO FINAL</td>
                    @foreach($resumen as $r)
                    <td class="text-end fw-bold" style="padding:8px 10px; color:#7DCCFF;">$ {{ number_format($r['saldo_final'], 0, ',', '.') }}</td>
                    @endforeach
                </tr>

            </tbody>
        </table>
    </div>
</div>