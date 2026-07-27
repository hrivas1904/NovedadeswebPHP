<div class="d-flex justify-content-end mb-2">
    <input type="month" id="mesPresupEjecutado" class="form-control form-control-sm" style="width:150px;" value="{{ $mes }}">
</div>

<div class="card p-0" style="overflow:hidden;">
    <div style="overflow-x:auto;">
        <table class="table table-sm mb-0" style="border-collapse: collapse;">

            <thead>
                <tr>
                    <th style="padding:6px 8px;">Concepto</th>
                    <th class="text-end" style="padding:6px 8px;">Presupuestado</th>
                    <th class="text-end" style="padding:6px 8px;">Ejecutado</th>
                    <th class="text-end" style="padding:6px 8px;">Desvío</th>
                </tr>
            </thead>

            <tbody>
                @forelse($filas as $i => $f)
                @php
                $desvio = (float) $f->desvio;
                $signo = $desvio > 0 ? '+' : '';
                $fondo = $i % 2 === 0 ? '#FFFFFF' : '#F8F9FA';
                @endphp
                <tr style="background:{{ $fondo }}; border-bottom:1px solid #dee2e6;">
                    <td style="padding:6px 8px;">{{ $f->concepto }}</td>
                    <td class="text-end text-secondary" style="padding:6px 8px;">$ {{ number_format(abs($f->presupuestado), 0, ',', '.') }}</td>
                    <td class="text-end" style="padding:6px 8px;">$ {{ number_format(abs($f->ejecutado), 0, ',', '.') }}</td>
                    <td class="text-end fw-semibold" style="padding:6px 8px;">{{ $signo }}{{ number_format($desvio, 0, ',', '.') }}</td>
                </tr>
                @empty
                <tr>
                    <td colspan="4" class="text-muted text-center p-3">Sin datos para este período.</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>