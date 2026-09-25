<style>
    .edd-shell { --edd-line: var(--border-color, #e2e8f0); max-width: 1440px; margin: 0 auto; padding: 12px 0 32px; color: var(--text-main, #1e293b); }
    .edd-shell h1, .edd-shell h2, .edd-shell h3 { color: var(--title-color, #0b3c6d); }
    .edd-header { display: flex; justify-content: space-between; align-items: flex-start; gap: 16px; margin-bottom: 24px; }
    .edd-eyebrow { font-size: .8rem; font-weight: 600; letter-spacing: .035em; }
    .edd-muted { color: var(--text-muted, #64748b); }
    .edd-design-badge { color: var(--text-main, #1e293b); background: var(--color-light, #e2e8f0); padding: 9px 12px; white-space: nowrap; }
    .edd-navigation { display: flex; flex-wrap: wrap; gap: 6px; padding-bottom: 12px; border-bottom: 1px solid var(--edd-line); margin-bottom: 20px; }
    .edd-navigation a { color: var(--text-main, #1e293b); padding: 10px 16px; border-radius: 8px; text-decoration: none; font-weight: 500; }
    .edd-navigation a:hover { background: var(--color-light, #e2e8f0); }
    .edd-navigation a[aria-current="page"] { background: var(--color-default, #00558c); color: #fff; }
    .edd-navigation a:focus-visible, .edd-shell summary:focus-visible { outline: 3px solid var(--color-second, #0089c7); outline-offset: 3px; }
    .edd-notice { padding: 14px 16px; border-left: 3px solid var(--color-second, #0089c7); background: var(--bg-card, #fff); border-radius: 4px; font-size: .9rem; }
    .edd-panel { background: var(--bg-card, #fff); border: 1px solid var(--edd-line); border-radius: 12px; padding: 24px; height: 100%; }
    .edd-panel-header { display: flex; justify-content: space-between; align-items: center; gap: 12px; flex-wrap: wrap; margin-bottom: 20px; }
    .edd-panel-header h2 { margin: 0; }
    .edd-metric { font-size: 2rem; font-weight: 600; margin: 10px 0 2px; }
    .edd-empty { padding: 32px 16px; text-align: center; color: var(--text-muted, #64748b); }
    .edd-empty p { max-width: 540px; margin: 8px auto 0; }
    .edd-flow { list-style: none; display: grid; grid-template-columns: repeat(5, minmax(0, 1fr)); gap: 12px; padding: 0; margin: 16px 0 0; }
    .edd-flow li { border-top: 3px solid var(--edd-line); padding-top: 12px; }
    .edd-flow strong { display: block; font-size: .9rem; margin: 8px 0 6px; }
    .edd-flow p { font-size: .8rem; margin: 0; color: var(--text-muted, #64748b); }
    .edd-step { font-size: .75rem; font-weight: 600; color: var(--text-muted, #64748b); }
    .edd-subnav { display: flex; flex-wrap: wrap; gap: 8px; margin: 16px 0 24px; }
    .edd-subnav a { padding: 8px 14px; border: 1px solid var(--edd-line); border-radius: 6px; color: var(--text-main, #1e293b); text-decoration: none; }
    .edd-subnav a[aria-current="page"] { font-weight: 600; border-color: var(--color-second, #0089c7); background: var(--color-light, #e2e8f0); }
    .edd-shell .table { --bs-table-color: var(--text-main, #1e293b); --bs-table-bg: transparent; --bs-table-border-color: var(--edd-line); }
    .edd-shell .table th { font-size: .82rem; font-weight: 600; padding-block: 14px; }
    .edd-shell .table td { vertical-align: middle; padding-block: 14px; }
    .edd-shell fieldset { min-width: 0; }
    .edd-shell legend { font-size: 1rem; font-weight: 600; margin-bottom: 16px; }
    .edd-shell .form-control:disabled, .edd-shell .form-select:disabled { color: var(--text-muted, #64748b); background-color: var(--bg-input, #fff); border-color: var(--edd-line); opacity: 1; }
    .edd-shell summary { cursor: pointer; font-weight: 600; color: var(--title-color, #0b3c6d); }
    .edd-shell .badge { white-space: normal; }
    @media (max-width: 767px) {
        .edd-header { flex-direction: column; margin-bottom: 16px; }
        .edd-panel { padding: 18px; }
        .edd-navigation a { padding: 9px 11px; font-size: .9rem; }
        .edd-flow { grid-template-columns: 1fr; }
        .edd-flow li { border-top: 0; border-left: 3px solid var(--edd-line); padding: 4px 0 4px 14px; }
        .edd-flow strong { display: inline; margin-left: 8px; }
        .edd-flow p { margin-top: 6px; }
    }
</style>
