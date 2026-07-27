<div class="p-2">
    <div class="d-flex align-items-center gap-2 mb-3">
        <span class="fw-semibold text-uppercase" style="color: var(--color-navy, #1B2A4A); letter-spacing:0.05em;">
            Año:
        </span>
        <select id="selectAnioResumen" class="form-select form-select-sm" style="width:auto;">
            @foreach($anios as $a)
            <option value="{{ $a }}" {{ (string) $a === (string) $anio ? 'selected' : '' }}>{{ $a }}</option>
            @endforeach
        </select>
    </div>

    <div class="card p-0" style="overflow:hidden;">
        <div style="overflow-x:auto;">
            <table class="table table-sm mb-0" style="border-collapse: collapse; min-width:780px;">

                <thead>
                    <tr style="background: var(--color-navy, #1B2A4A);">
                        <th class="" style="padding:8px 10px; text-align:left;">Mes</th>
                        <th class="" style="padding:8px 10px; text-align:right;">Saldo inicio</th>
                        <th class="" style="padding:8px 10px; text-align:right;">Ing. presup.</th>
                        <th class="" style="padding:8px 10px; text-align:right;">Egr. presup.</th>
                        <th class="" style="padding:8px 10px; text-align:right;">Neto presup.</th>
                        <th class="fw-semibold" style="padding:8px 10px; text-align:right;">Ing. ejecut.</th>
                        <th class="fw-semibold" style="padding:8px 10px; text-align:right;">Egr. ejecut.</th>
                        <th class="fw-semibold" style="padding:8px 10px; text-align:right;">Neto ejecut.</th>
                        <th class="fw-semibold" style="padding:8px 10px; text-align:right;">Saldo fin</th>
                    </tr>
                </thead>

                <tbody>
                    @forelse($meses as $i => $m)
                    <tr style="background:{{ $i % 2 === 0 ? '#FFFFFF' : '#F8F9FA' }}; border-bottom:1px solid #dee2e6;">
                        <td class="fw-semibold" style="padding:6px 10px; color:#1B2A4A;">{{ $m->mes_nombre }}</td>
                        <td class="text-end text-secondary" style="padding:6px 10px;">$ {{ number_format($m->saldo_inicio, 0, ',', '.') }}</td>
                        <td class="text-end text-secondary" style="padding:6px 10px;">$ {{ number_format($m->presupuestado_ingresos, 0, ',', '.') }}</td>
                        <td class="text-end text-secondary" style="padding:6px 10px;">$ {{ number_format($m->presupuestado_egresos, 0, ',', '.') }}</td>
                        <td class="text-end fw-medium" style="padding:6px 10px; color:#28a745;">$ {{ number_format($m->presupuestado_neto, 0, ',', '.') }}</td>
                        <td class="text-end fw-medium" style="padding:6px 10px; color:#28a745;">$ {{ number_format($m->ejecutado_ingresos, 0, ',', '.') }}</td>
                        <td class="text-end fw-medium" style="padding:6px 10px; color:#dc3545;">$ {{ number_format($m->ejecutado_egresos, 0, ',', '.') }}</td>
                        <td class="text-end fw-semibold" style="padding:6px 10px; color:#28a745;">$ {{ number_format($m->ejecutado_neto, 0, ',', '.') }}</td>
                        <td class="text-end fw-bold" style="padding:6px 10px; color:#28a745;">$ {{ number_format($m->saldo_final, 0, ',', '.') }}</td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="9" class="text-muted text-center p-3">Sin datos para este año.</td>
                    </tr>
                    @endforelse
                </tbody>

                <tfoot>
                    <tr style="background: var(--color-navy, #1B2A4A); border-top:2px solid #17a2b8;">
                        <td class="fw-bold" style="padding:7px 10px;">Total {{ $anio }}</td>
                        <td style="padding:7px 10px;"></td>
                        <td class="text-end" style="padding:7px 10px;">$ {{ number_format($totales['presupuestado_ingresos'], 0, ',', '.') }}</td>
                        <td class="text-end" style="padding:7px 10px;">$ {{ number_format($totales['presupuestado_egresos'], 0, ',', '.') }}</td>
                        <td class="text-end fw-semibold" style="padding:7px 10px;">$ {{ number_format($totales['presupuestado_neto'], 0, ',', '.') }}</td>
                        <td class="text-end fw-semibold" style="padding:7px 10px;;">$ {{ number_format($totales['ejecutado_ingresos'], 0, ',', '.') }}</td>
                        <td class="text-end fw-semibold" style="padding:7px 10px;">$ {{ number_format($totales['ejecutado_egresos'], 0, ',', '.') }}</td>
                        <td class="text-end fw-bold" style="padding:7px 10px;">$ {{ number_format($totales['ejecutado_neto'], 0, ',', '.') }}</td>
                        <td style="padding:7px 10px;"></td>
                    </tr>
                </tfoot>
            </table>
        </div>
    </div>
</div>