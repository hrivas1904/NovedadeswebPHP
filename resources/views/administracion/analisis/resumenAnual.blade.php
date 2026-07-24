<div class="p-2">
    <div class="d-flex align-items-center gap-2 mb-3">
        <span class="fw-semibold text-uppercase" style="color: var(--color-navy, #1B2A4A); letter-spacing:0.05em;">
            Año:
        </span>
        <select id="selectAnioResumen" class="form-select form-select-sm" style="width:auto;">
            <option>2024</option>
            <option>2025</option>
            <option selected>2026</option>
        </select>
    </div>

    <div class="card p-0" style="overflow:hidden;">
        <div style="overflow-x:auto;">
            <table class="table table-sm mb-0" style="border-collapse: collapse; min-width:780px;">

                {{-- HEADER --}}
                <thead>
                    <tr style="background: var(--color-navy, #1B2A4A);">
                        <th class="text-light" style="padding:8px 10px; text-align:left;">Mes</th>
                        <th class="text-light" style="padding:8px 10px; text-align:right;">Saldo inicio</th>
                        <th class="text-light" style="padding:8px 10px; text-align:right;">Ing. presup.</th>
                        <th class="text-light" style="padding:8px 10px; text-align:right;">Egr. presup.</th>
                        <th class="text-light" style="padding:8px 10px; text-align:right;">Neto presup.</th>
                        <th class="text-white fw-semibold" style="padding:8px 10px; text-align:right;">Ing. ejecut.</th>
                        <th class="text-white fw-semibold" style="padding:8px 10px; text-align:right;">Egr. ejecut.</th>
                        <th class="text-white fw-semibold" style="padding:8px 10px; text-align:right;">Neto ejecut.</th>
                        <th class="text-white fw-semibold" style="padding:8px 10px; text-align:right;">Saldo fin</th>
                    </tr>
                </thead>

                {{-- BODY --}}
                <tbody>
                    <tr style="background:#FFFFFF; border-bottom:1px solid #dee2e6;">
                        <td class="fw-semibold" style="padding:6px 10px; color:#1B2A4A;">Enero</td>
                        <td class="text-end text-secondary" style="padding:6px 10px;">$ 1.100.000</td>
                        <td class="text-end text-secondary" style="padding:6px 10px;">$ 4.400.000</td>
                        <td class="text-end text-secondary" style="padding:6px 10px;">$ 2.700.000</td>
                        <td class="text-end fw-medium" style="padding:6px 10px; color:#28a745;">$ 1.700.000</td>
                        <td class="text-end fw-medium" style="padding:6px 10px; color:#28a745;">$ 4.350.000</td>
                        <td class="text-end fw-medium" style="padding:6px 10px; color:#dc3545;">$ 2.750.000</td>
                        <td class="text-end fw-semibold" style="padding:6px 10px; color:#28a745;">$ 1.600.000</td>
                        <td class="text-end fw-bold" style="padding:6px 10px; color:#28a745;">$ 2.150.000</td>
                    </tr>

                    <tr style="background:#F8F9FA; border-bottom:1px solid #dee2e6;">
                        <td class="fw-semibold" style="padding:6px 10px; color:#1B2A4A;">Febrero</td>
                        <td class="text-end text-secondary" style="padding:6px 10px;">$ 1.250.000</td>
                        <td class="text-end text-secondary" style="padding:6px 10px;">$ 4.600.000</td>
                        <td class="text-end text-secondary" style="padding:6px 10px;">$ 2.800.000</td>
                        <td class="text-end fw-medium" style="padding:6px 10px; color:#28a745;">$ 1.800.000</td>
                        <td class="text-end fw-medium" style="padding:6px 10px; color:#28a745;">$ 4.500.000</td>
                        <td class="text-end fw-medium" style="padding:6px 10px; color:#dc3545;">$ 2.800.000</td>
                        <td class="text-end fw-semibold" style="padding:6px 10px; color:#28a745;">$ 1.700.000</td>
                        <td class="text-end fw-bold" style="padding:6px 10px; color:#28a745;">$ 2.450.000</td>
                    </tr>

                    <tr style="background:#FFFFFF; border-bottom:1px solid #dee2e6;">
                        <td class="fw-semibold" style="padding:6px 10px; color:#1B2A4A;">Marzo</td>
                        <td class="text-end text-secondary" style="padding:6px 10px;">$ 1.400.000</td>
                        <td class="text-end text-secondary" style="padding:6px 10px;">$ 4.700.000</td>
                        <td class="text-end text-secondary" style="padding:6px 10px;">$ 2.900.000</td>
                        <td class="text-end fw-medium" style="padding:6px 10px; color:#28a745;">$ 1.800.000</td>
                        <td class="text-end fw-medium" style="padding:6px 10px; color:#28a745;">$ 4.800.000</td>
                        <td class="text-end fw-medium" style="padding:6px 10px; color:#dc3545;">$ 2.950.000</td>
                        <td class="text-end fw-semibold" style="padding:6px 10px; color:#28a745;">$ 1.850.000</td>
                        <td class="text-end fw-bold" style="padding:6px 10px; color:#28a745;">$ 2.500.000</td>
                    </tr>

                    <tr style="background:#F8F9FA; border-bottom:1px solid #dee2e6;">
                        <td class="fw-semibold" style="padding:6px 10px; color:#1B2A4A;">Abril</td>
                        <td class="text-end text-secondary" style="padding:6px 10px;">—</td>
                        <td class="text-end text-secondary" style="padding:6px 10px;">—</td>
                        <td class="text-end text-secondary" style="padding:6px 10px;">—</td>
                        <td class="text-end fw-medium text-secondary" style="padding:6px 10px;">—</td>
                        <td class="text-end fw-medium" style="padding:6px 10px; color:#28a745;">$ 4.980.000</td>
                        <td class="text-end fw-medium" style="padding:6px 10px; color:#dc3545;">$ 2.950.000</td>
                        <td class="text-end fw-semibold" style="padding:6px 10px; color:#28a745;">$ 2.030.000</td>
                        <td class="text-end fw-bold" style="padding:6px 10px; color:#28a745;">$ 2.440.000</td>
                    </tr>

                    {{-- Meses futuros / sin ejecutar --}}
                    <tr style="background:#FFFFFF; border-bottom:1px solid #dee2e6;">
                        <td class="fw-semibold" style="padding:6px 10px; color:#1B2A4A;">Mayo</td>
                        <td class="text-end text-secondary" style="padding:6px 10px;">—</td>
                        <td class="text-end text-secondary" style="padding:6px 10px;">—</td>
                        <td class="text-end text-secondary" style="padding:6px 10px;">—</td>
                        <td class="text-end fw-medium text-secondary" style="padding:6px 10px;">—</td>
                        <td class="text-end text-secondary" style="padding:6px 10px;">—</td>
                        <td class="text-end text-secondary" style="padding:6px 10px;">—</td>
                        <td class="text-end fw-semibold text-secondary" style="padding:6px 10px;">—</td>
                        <td class="text-end fw-bold text-secondary" style="padding:6px 10px;">—</td>
                    </tr>
                </tbody>

                {{-- FOOTER: TOTALES --}}
                <tfoot>
                    <tr style="background: var(--color-navy, #1B2A4A); border-top:2px solid #17a2b8;">
                        <td class="fw-bold text-white" style="padding:7px 10px;">Total 2026</td>
                        <td style="padding:7px 10px;"></td>
                        <td class="text-end text-light" style="padding:7px 10px;">$ 18.680.000</td>
                        <td class="text-end text-light" style="padding:7px 10px;">$ 11.350.000</td>
                        <td class="text-end fw-semibold" style="padding:7px 10px; color:#7FFFC4;">$ 7.330.000</td>
                        <td class="text-end fw-semibold" style="padding:7px 10px; color:#7FFFC4;">$ 18.630.000</td>
                        <td class="text-end fw-semibold" style="padding:7px 10px; color:#FFB3B3;">$ 11.450.000</td>
                        <td class="text-end fw-bold" style="padding:7px 10px; color:#7FFFC4;">$ 7.180.000</td>
                        <td style="padding:7px 10px;"></td>
                    </tr>
                </tfoot>
            </table>
        </div>
    </div>
</div>