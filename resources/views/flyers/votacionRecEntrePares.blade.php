<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reconocimiento entre Pares</title>
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-sRIl4kxILFvY47J16cr9ZwB07vP4J8+LH7qKQnuqkuIAvNWLzeN8tE5YBujZqJLB" crossorigin="anonymous">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/7.3.1/css/all.min.css" />
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Fraunces:opsz,wght@9..144,400;9..144,500;9..144,600&family=Space+Grotesk:wght@500;600;700&family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>
        :root {
            --navy: #00558C;
            --blue: #0089C7;
            --teal: #1DAC8A;
            --navy-deep: #003a63;
            --gray: #6D6E71;
            --ink: #12232f;
            --bg: #f3f7f9;
            --paper: #ffffff;
            --amber: #c97a2b;
            --amber-bg: #fdf1e3;
            --line: #dbe6ea;
            --radius: 14px;
        }

        * {
            box-sizing: border-box;
        }

        html {
            scroll-behavior: smooth;
        }

        body {
            margin: 0;
            background: var(--bg);
            color: var(--ink);
            font-family: 'Inter', system-ui, sans-serif;
            line-height: 1.55;
            -webkit-font-smoothing: antialiased;
        }

        h1,
        h2,
        h3,
        .disp {
            font-family: 'Space Grotesk', 'Inter', sans-serif;
            font-weight: 700;
            letter-spacing: -0.01em;
            color: var(--navy-deep);
            margin: 0;
        }

        .serif {
            font-family: 'Fraunces', serif;
            font-weight: 500;
            font-style: italic;
        }

        a {
            color: var(--blue);
        }

        img,
        svg {
            display: block;
        }

        /* ---------- Top brand bar ---------- */
        .brandbar {
            background: var(--paper);
            border-bottom: 1px solid var(--line);
            position: sticky;
            top: 0;
            z-index: 50;
        }

        .brandbar-inner {
            max-width: 1080px;
            margin: 0 auto;
            padding: 14px 24px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 16px;
        }

        .logo {
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .logo-mark {
            height: 32px;
            width: auto;
            flex: none;
        }

        .quicknav {
            display: flex;
            gap: 6px;
        }

        .quicknav a {
            font-size: 13.5px;
            font-weight: 600;
            text-decoration: none;
            color: var(--navy);
            padding: 8px 14px;
            border-radius: 999px;
            border: 1px solid var(--line);
            transition: background .15s, color .15s, border-color .15s;
            white-space: nowrap;
        }

        .quicknav a:hover {
            background: var(--navy);
            color: #fff;
            border-color: var(--navy);
        }

        /* ---------- Hero ---------- */
        .hero {
            position: relative;
            background: linear-gradient(155deg, var(--navy-deep) 0%, var(--navy) 46%, var(--blue) 100%);
            overflow: hidden;
            color: #fff;
        }

        .hero-inner {
            max-width: 1080px;
            margin: 0 auto;
            padding: 64px 24px 56px;
            position: relative;
            z-index: 2;
        }

        .eyebrow {
            font-size: 12.5px;
            font-weight: 700;
            letter-spacing: .12em;
            text-transform: uppercase;
            color: #bfe6d6;
            margin-bottom: 14px;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .eyebrow .dot {
            width: 6px;
            height: 6px;
            border-radius: 50%;
            background: var(--teal);
            box-shadow: 0 0 0 4px rgba(29, 172, 138, .25);
        }

        .hero h1 {
            font-size: clamp(30px, 4.2vw, 46px);
            line-height: 1.08;
            max-width: 720px;
            color: #fff;
        }

        .hero h1 em {
            font-family: 'Fraunces', serif;
            font-style: italic;
            font-weight: 500;
            color: #a8d8ee;
        }

        .hero p.lead {
            margin-top: 16px;
            max-width: 560px;
            font-size: 16.5px;
            color: #dcecf3;
            font-weight: 400;
        }

        .hero-stripes {
            position: absolute;
            right: -40px;
            top: 0;
            bottom: 0;
            width: 520px;
            opacity: .9;
            z-index: 1;
            pointer-events: none;
        }

        .hero-stripes svg {
            width: 100%;
            height: 100%;
        }

        /* deadline banner */
        .banner {
            margin-top: 32px;
            background: rgba(255, 255, 255, .08);
            border: 1px solid rgba(255, 255, 255, .25);
            border-left: 4px solid var(--teal);
            border-radius: 12px;
            padding: 16px 20px;
            display: flex;
            gap: 14px;
            align-items: flex-start;
            max-width: 640px;
            backdrop-filter: blur(2px);
        }

        .banner svg {
            flex: none;
            margin-top: 2px;
        }

        .banner strong {
            color: #fff;
            font-family: 'Space Grotesk';
            font-size: 15px;
        }

        .banner p {
            margin: 4px 0 0;
            font-size: 14.5px;
            color: #e3f0f4;
        }

        /* ---------- Layout ---------- */
        .wrap {
            max-width: 1080px;
            margin: 0 auto;
            padding: 0 24px;
        }

        .section {
            padding: 64px 0 20px;
            scroll-margin-top: 76px;
        }

        .section-head {
            display: flex;
            align-items: flex-end;
            justify-content: space-between;
            gap: 20px;
            margin-bottom: 36px;
            flex-wrap: wrap;
            border-bottom: 1px solid var(--line);
            padding-bottom: 20px;
        }

        .section-tag {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            font-size: 12px;
            font-weight: 700;
            letter-spacing: .08em;
            text-transform: uppercase;
            padding: 6px 12px;
            border-radius: 999px;
            margin-bottom: 10px;
        }

        .tag-blue {
            color: var(--navy);
            background: #e5f0f7;
        }

        .tag-teal {
            color: #116e57;
            background: #e1f5ee;
        }

        .section-head h2 {
            font-size: clamp(24px, 3vw, 32px);
        }

        .section-sub {
            color: var(--gray);
            font-size: 14.5px;
            max-width: 460px;
        }

        /* ---------- Steps ---------- */
        .steps {
            display: flex;
            flex-direction: column;
            gap: 0;
            position: relative;
        }

        .step {
            display: grid;
            grid-template-columns: 64px 1fr;
            gap: 24px;
            padding: 28px 0;
            border-bottom: 1px solid var(--line);
            position: relative;
        }

        .step:last-child {
            border-bottom: none;
        }

        .step-num {
            width: 52px;
            height: 52px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-family: 'Space Grotesk';
            font-weight: 700;
            font-size: 18px;
            color: #fff;
            position: relative;
            z-index: 2;
        }

        .sec-atencion .step-num {
            background: linear-gradient(135deg, var(--navy), var(--blue));
        }

        .sec-receta .step-num {
            background: linear-gradient(135deg, var(--teal), #17c79a);
        }

        .step-body h3 {
            font-size: 18.5px;
            margin-bottom: 8px;
        }

        .step-body p {
            margin: 0 0 10px;
            color: #33454f;
            font-size: 15px;
        }

        .step-body ul {
            margin: 8px 0 12px;
            padding-left: 20px;
            color: #33454f;
            font-size: 15px;
        }

        .step-body li {
            margin-bottom: 5px;
        }

        .kbd {
            display: inline-block;
            font-family: 'Space Grotesk';
            font-weight: 600;
            font-size: 13.5px;
            background: #eef4f6;
            border: 1px solid var(--line);
            border-radius: 6px;
            padding: 2px 8px;
            color: var(--navy-deep);
        }

        code.url {
            display: inline-block;
            background: var(--navy-deep);
            color: #dff1ff;
            font-family: 'Space Grotesk';
            font-size: 14px;
            padding: 6px 12px;
            border-radius: 8px;
            letter-spacing: .01em;
            margin: 6px 0;
        }

        /* callouts */
        .callout {
            border-radius: 10px;
            padding: 14px 16px;
            font-size: 14.5px;
            display: flex;
            gap: 10px;
            margin-top: 6px;
        }

        .callout svg {
            flex: none;
            margin-top: 2px;
        }

        .callout.info {
            background: #e9f5fb;
            border: 1px solid #bfe0ee;
            color: #0a4a68;
        }

        .callout.warn {
            background: var(--amber-bg);
            border: 1px solid #f0d3ac;
            color: #7a4a12;
        }

        .callout>div>strong:first-child {
            display: block;
            margin-bottom: 2px;
        }

        /* screenshots */
        .shot {
            margin-top: 16px;
            border-radius: 10px;
            overflow: hidden;
            border: 1px solid var(--line);
            background: #fff;
            box-shadow: 0 12px 26px -18px rgba(0, 45, 75, .4);
            max-width: 640px;
        }

        .shot.wide {
            max-width: 100%;
        }

        .shot.narrow {
            max-width: 340px;
        }

        .shot img {
            width: 100%;
            display: block;
        }

        .shot-cap {
            font-size: 12px;
            color: var(--gray);
            padding: 8px 12px;
            border-top: 1px solid var(--line);
            background: #fafcfd;
        }

        .shot-row {
            display: flex;
            gap: 16px;
            flex-wrap: wrap;
            margin-top: 16px;
        }

        .shot-row .shot {
            margin-top: 0;
            flex: 1 1 260px;
        }

        /* icon legend caption list */
        .legend-list {
            list-style: none;
            margin: 10px 0 0;
            padding: 0;
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(190px, 1fr));
            gap: 8px 18px;
        }

        .legend-list li {
            font-size: 13.5px;
            color: #33454f;
            display: flex;
            gap: 8px;
            align-items: baseline;
        }

        .legend-list b {
            font-family: 'Space Grotesk';
            color: var(--navy);
            font-size: 13px;
            flex: none;
            width: 16px;
            text-align: center;
        }

        /* ---------- Checklist card (hero adjacent) ---------- */
        .prep {
            background: var(--paper);
            border: 1px solid var(--line);
            border-radius: var(--radius);
            padding: 24px 26px;
            margin: -34px 0 40px;
            box-shadow: 0 18px 34px -22px rgba(0, 45, 75, .35);
            position: relative;
            z-index: 5;
        }

        .prep h3 {
            font-size: 15px;
            text-transform: uppercase;
            letter-spacing: .06em;
            color: var(--navy);
            margin-bottom: 14px;
        }

        .prep-grid {
            display: grid;
            grid-template-columns: 1fr;
            gap: 18px;
            max-width: 420px;
        }

        .prep-item {
            display: flex;
            gap: 10px;
            align-items: flex-start;
        }

        .prep-item svg {
            flex: none;
            margin-top: 2px;
            color: var(--teal);
        }

        .prep-item span {
            font-size: 14px;
            color: #33454f;
        }

        /* footer */
        footer {
            margin-top: 40px;
            padding: 32px 0 48px;
            border-top: 1px solid var(--line);
        }

        footer .wrap {
            display: flex;
            align-items: center;
            justify-content: space-between;
            flex-wrap: wrap;
            gap: 12px;
        }

        footer p {
            font-size: 13px;
            color: var(--gray);
            margin: 0;
        }

        footer .fmark {
            display: flex;
            align-items: center;
            gap: 8px;
        }

        footer .fmark svg {
            width: 22px;
            height: 19px;
        }

        @media (max-width:760px) {
            .quicknav {
                display: none;
            }

            .hero-stripes {
                width: 220px;
                right: -80px;
                opacity: .55;
            }

            .prep-grid {
                grid-template-columns: 1fr;
            }

            .step {
                grid-template-columns: 44px 1fr;
                gap: 16px;
            }

            .step-num {
                width: 40px;
                height: 40px;
                font-size: 15px;
            }
        }
    </style>
</head>

<body>
    <div class="brandbar">
        <div class="brandbar-inner">
            <div class="logo">
                <img class="logo-mark" src="data:image/png;base64,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" alt="Hospital Privado Tres Cerritos">
            </div>

        </div>
    </div>

    <header class="hero">
        <div class="hero-stripes" aria-hidden="true">
            <svg viewBox="0 0 520 460" xmlns="http://www.w3.org/2000/svg">
                <polygon points="120,460 220,460 480,60 380,60" fill="#ffffff" opacity="0.06" />
                <polygon points="200,460 280,460 500,120 420,120" fill="#ffffff" opacity="0.08" />
                <polygon points="270,460 330,460 480,220 420,220" fill="#1DAC8A" opacity="0.35" />
            </svg>
        </div>
        <div class="hero-inner">
            <div class="eyebrow"><span class="dot"></span>Votación · Reconocimiento entre Pares</div>
            <h1>Reconocimiento entre pares 2026<br><em>Hace fácil lo difícil</em></h1>
            <p class="lead">
                En el hospital, todos los días algo se complica: un paciente que llega mal, un equipo que falla, un turno que queda corto, un trámite que se traba, un familiar angustiado. Y en cada sector hay alguien que, cuando eso pasa, no se desespera ni le pasa el problema a otro. Lo resuelve sin hacer ruido y les deja las cosas más fáciles a los demás.
                Eso es lo que queremos reconocer.

                No estamos votando al que más horas trabaja, al más simpático ni al que tiene más amigos. Votamos a quien, cuando todo se complica, encuentra la forma de que las cosas salgan.
            </p>

            <div class="banner">
                <svg width="22" height="22" viewBox="0 0 24 24" fill="none">
                    <path d="M12 3l9 16H3l9-16z" stroke="#1DAC8A" stroke-width="1.8" stroke-linejoin="round" />
                    <path d="M12 10v4" stroke="#1DAC8A" stroke-width="1.8" stroke-linecap="round" />
                    <circle cx="12" cy="17" r="1" fill="#1DAC8A" />
                </svg>
                <div>
                    <strong>Las 3 personas más votadas de cada grupo serán reconocidas en la cena de fin de año del 4 de diciembre</strong>
                    <p>
                        CÓMO VOTAR<br />
                        • El hospital está dividido en 3 grupos. Vas a elegir una persona en cada grupo.<br />
                        • Podés votar a compañeros de cualquier sector, incluido el tuyo, pero no a vos mismo.<br />
                        • En cada voto, contanos por qué, con una situación concreta.<br />
                        • Si no conocés lo suficiente a nadie de un grupo, elegí "No conozco lo suficiente a este grupo". Es mejor que votar por votar.<br />
                        • Se vota una sola vez.<br />
                    </p>
                </div>
            </div>
        </div>
    </header>

    <main class="wrap my-4">
        <section id="sectionEncuesta">
            <div class="row g-3">
                <div class="col-12 col-lg-7">
                    <div class="d-flex flex-column gap-3">
                        <div class="card p-3">
                            <div class="d-flex flex-column gap-2" id="divValidacionDni">

                                <label for="inputDni" class="form-label text-muted fw-bold">
                                    Ingresar DNI (sin puntos)
                                </label>

                                <div class="input-group">
                                    <input
                                        type="text"
                                        class="form-control"
                                        id="inputDni"
                                        name="dni"
                                        placeholder="Ej.: 30123456"
                                        maxlength="8"
                                        inputmode="numeric"
                                        autocomplete="off">

                                    <button
                                        type="button"
                                        class="btn btn-primary"
                                        id="btnValidarDni">
                                        <i class="fa-solid fa-id-card me-2"></i>
                                        Validar identidad
                                    </button>
                                </div>

                                <div id="mensajeValidacionDni" class="d-none"></div>

                            </div>
                        </div>

                        <div id="divResponderPreguntas" class="d-none d-flex flex-column gap-3">
                            <div class="card p-3">
                                <div class="d-flex flex-column gap-2">
                                    <label class="label-control text-muted fw-bold fs-6">Grupo 1 de 3</label>
                                    <small class="text-muted">
                                        Áreas de este grupo: Enfermería
                                    </small>
                                    <label class="label-control text-muted fw-bold">¿Quién de este grupo hace fácil lo difícil?</label>
                                    <select class="form-select" id="selectorGrupo1" data-id="1">
                                        <option value="">Selecciona un/a colaborador/a</option>
                                    </select>
                                    <label class="label-control text-muted fw-bold">¿Por qué?</label>
                                    <input class="form-control" id="inputRta12" placeholder="Escribe aquí..." data-id="2">
                                    <label class="label-control text-muted fw-bold">¿Por qué la elegiste?</label>
                                    <small class="text-muted">
                                        Contanos una situación concreta en la que esta persona hizo fácil algo difícil.
                                    </small>
                                    <input class="form-control" id="inputRta13" placeholder="Escribe aquí..." data-id="3">
                                </div>
                            </div>
                            <div class="card p-3">
                                <div class="d-flex flex-column gap-2">
                                    <label class="label-control text-muted fw-bold fs-6">Grupo 2 de 3</label>
                                    <small class="text-muted">
                                        Áreas de este grupo: Limpieza · Recepción y Consultorios (Recepción, Consultorios Externos y Sede Mitre) · Esterilización · Mantenimiento · Sistemas
                                    </small>
                                    <label class="label-control text-muted fw-bold">¿Quién de este grupo hace fácil lo difícil?</label>
                                    <select class="form-select" id="selectorGrupo2" data-id="1">
                                        <option value="">Selecciona un/a colaborador/a</option>
                                    </select>
                                    <label class="label-control text-muted fw-bold">¿Por qué?</label>
                                    <input class="form-control" id="inputRta22" placeholder="Escribe aquí..." data-id="2">
                                    <label class="label-control text-muted fw-bold">¿Por qué la elegiste?</label>
                                    <small class="text-muted">
                                        Contanos una situación concreta en la que esta persona hizo fácil algo difícil.
                                    </small>
                                    <input class="form-control" id="inputRta23" placeholder="Escribe aquí..." data-id="3">
                                </div>
                            </div>
                            <div class="card p-3">
                                <div class="d-flex flex-column gap-2">
                                    <label class="label-control text-muted fw-bold fs-6">Grupo 3 de 3</label>
                                    <small class="text-muted">
                                        Áreas de este grupo: Quirófano · Facturación · Administración · Diagnóstico por Imágenes · Farmacia · Recursos Humanos
                                    </small>
                                    <label class="label-control text-muted fw-bold">¿Quién de este grupo hace fácil lo difícil?</label>
                                    <select class="form-select" id="selectorGrupo3" data-id="1">
                                        <option value="">Selecciona un/a colaborador/a</option>
                                    </select>
                                    <label class="label-control text-muted fw-bold">¿Por qué?</label>
                                    <input class="form-control" id="inputRta32" placeholder="Escribe aquí..." data-id="2">
                                    <label class="label-control text-muted fw-bold">¿Por qué la elegiste?</label>
                                    <small class="text-muted">
                                        Contanos una situación concreta en la que esta persona hizo fácil algo difícil.
                                    </small>
                                    <input class="form-control" id="inputRta33" placeholder="Escribe aquí..." data-id="3">
                                </div>
                            </div>
                        </div>

                        <div id="contenedorBotonSend" class="d-flex justify-content-end" >
                            <button type="button" class="btn-primary btn d-none" id="btnEnviarVotacion">Enviar respuestas</button>
                        </div>
                    </div>
                </div>
            </div>
        </section>
    </main>

    <footer>
        <div class="wrap">
            <div class="fmark">
                <img src="data:image/png;base64,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" alt="Hospital Privado Tres Cerritos" style="height:26px; width:auto;">
                <p>Hospital Privado Tres Cerritos · Votación · Reconocimiento entre pares 2026</p>
            </div>
            <p></p>
        </div>
    </footer>

    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.11.8/dist/umd/popper.min.js" integrity="sha384-I7E8VVD/ismYTF4hNIPjVp/Zjvgyol6VFvRkX/vR+Vc4jQkC+hVqc2pM8ODewa9r" crossorigin="anonymous"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.min.js" integrity="sha384-G/EV+4j2dNv+tEPo3++6LCgdCROaejBqfUeNjuKAiuXbjrxilcCdDz6ZAVfHWe1Y" crossorigin="anonymous"></script>
    <script>
        const DASHBOARD_TOKEN = "{{ request()->route('token') }}";
    </script>
    <script src="{{ asset('js/personal/votaciones.js') }}"></script>

</body>

</html>