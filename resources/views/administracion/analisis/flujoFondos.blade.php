<div class="d-flex justify-content-end mb-2">
    <input type="month" id="mesFlujoFondos" class="form-control form-control-sm" style="width:150px;" value="{{ $mes }}">
</div>

<div class="card p-0" style="overflow:hidden;">

    <div class="d-flex justify-content-between align-items-center px-3 py-2 text-white"
        style="background: var(--color-navy, #1B2A4A);">
        <span class="fw-bold h6">
            FLUJO DE FONDOS — {{ $mesLabel }}
        </span>
        <div class="d-flex gap-4">
            <span class="fw-bold h6">IMPORTE</span>
            <span class="fw-bold h6">% s/Ingresos</span>
        </div>
    </div>

    <table class="table table-sm mb-0" style="border-collapse: collapse;">
        <tbody>

            <tr style="background:#F0F7FF; border-bottom:1px solid #dee2e6;">
                <td class="fw-semibold" style="color:#1B2A4A;">SALDO DE INICIO</td>
                <td class="text-end fw-bold" style="color:#1B2A4A;">$ {{ number_format($resumen['saldo_inicio'], 2, ',', '.') }}</td>
                <td></td>
            </tr>

            <tr>
                <td colspan="3" class="fw-bold text-uppercase pt-2" style="color:#6c757d;">
                    Ingresos
                </td>
            </tr>

            @forelse($detalle->get('INGRESOS', []) as $fila)
                <tr>
                    <td style="padding-left:20px;">{{ $fila['label'] }}</td>
                    <td class="text-end">$ {{ number_format(abs($fila['importe_neto']), 2, ',', '.') }}</td>
                    <td class="text-end text-muted">{{ number_format($fila['pct'], 2, ',', '.') }}%</td>
                </tr>
            @empty
                <tr><td colspan="3" class="text-muted" style="padding-left:20px;">Sin datos.</td></tr>
            @endforelse

            <tr style="border-top:1px solid var(--color-accent-green); background-color: var(--color-accent-green);">
                <td class="fw-bold">TOTAL INGRESOS</td>
                <td class="text-end fw-bold">$ {{ number_format($resumen['total_ingresos'], 2, ',', '.') }}</td>
                <td></td>
            </tr>

            <tr>
                <td colspan="3" style="height:10px;"></td>
            </tr>

            <tr>
                <td colspan="3" class="fw-bold text-uppercase pt-2" style="color:#6c757d;">
                    Pagos Operativos
                </td>
            </tr>

            @forelse($detalle->get('PAGOS_OPERATIVOS', []) as $fila)
                <tr>
                    <td style="padding-left:20px;">{{ $fila['label'] }}</td>
                    <td class="text-end">$ {{ number_format(abs($fila['importe_neto']), 2, ',', '.') }}</td>
                    <td class="text-end text-muted">{{ number_format($fila['pct'], 2, ',', '.') }}%</td>
                </tr>
            @empty
                <tr><td colspan="3" class="text-muted" style="padding-left:20px;">Sin datos.</td></tr>
            @endforelse

            <tr style="border-top:1px solid #dee2e6;">
                <td class="fw-bold">TOTAL PAGOS OPERATIVOS</td>
                <td class="text-end fw-bold">$ {{ number_format(abs($resumen['total_pagos_operativos']), 2, ',', '.') }}</td>
                <td></td>
            </tr>

            <tr>
                <td colspan="3" style="height:8px;"></td>
            </tr>

            {{-- RDO. BRUTO OPERATIVO --}}
            <tr style="background:#E8F5EE; border-top:2px solid #17a2b8; border-bottom:2px solid #17a2b8;">
                <td class="fw-bold" style="color:#28a745; padding:10px;">RDO. BRUTO OPERATIVO</td>
                <td class="text-end fw-bold" style="color:#28a745; padding:10px;">$ {{ number_format($resumen['rdo_bruto_operativo'], 2, ',', '.') }}</td>
                <td class="text-end fw-semibold" style="color:#28a745; padding:10px;">{{ number_format($resumen['rdo_bruto_pct'], 2, ',', '.') }}%</td>
            </tr>

            <tr>
                <td colspan="3" style="height:8px;"></td>
            </tr>

            {{-- SUBTITULO: USO DE FONDOS --}}
            <tr>
                <td colspan="3" class="fw-bold text-uppercase pt-2" style="color:#6c757d;">
                    Uso de Fondos
                </td>
            </tr>

            @forelse($detalle->get('USO_FONDOS', []) as $fila)
                <tr>
                    <td style="padding-left:20px;">{{ $fila['label'] }}</td>
                    <td class="text-end">$ {{ number_format(abs($fila['importe_neto']), 2, ',', '.') }}</td>
                    <td class="text-end text-muted">{{ number_format($fila['pct'], 2, ',', '.') }}%</td>
                </tr>
            @empty
                <tr><td colspan="3" class="text-muted" style="padding-left:20px;">Sin datos.</td></tr>
            @endforelse

            {{-- TOTAL USO DE FONDOS --}}
            <tr style="border-top:1px solid #dee2e6;">
                <td class="fw-bold">TOTAL USO DE FONDOS</td>
                <td class="text-end fw-bold">$ {{ number_format(abs($resumen['total_uso_fondos']), 2, ',', '.') }}</td>
                <td></td>
            </tr>

            <tr>
                <td colspan="3" style="height:8px;"></td>
            </tr>

            <tr style="background:#D4EDDA; border-top:2px solid #28a745;">
                <td class="fw-bold" style="color:#28a745; padding:10px;">FF NETO</td>
                <td class="text-end fw-bold" style="color:#28a745; padding:10px;">$ {{ number_format($resumen['ff_neto'], 2, ',', '.') }}</td>
                <td></td>
            </tr>

            <tr style="background: var(--color-navy, #1B2A4A);">
                <td class="fw-bold text-white" style="padding:10px;">SALDO FINAL</td>
                <td class="text-end fw-bold" style="color:#7DCCFF; padding:10px;">$ {{ number_format($resumen['saldo_final'], 2, ',', '.') }}</td>
                <td></td>
            </tr>

        </tbody>
    </table>
</div>