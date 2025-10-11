<!doctype html>
<html lang="it">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Gestionale Pratiche - Admin</title>
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-sRIl4kxILFvY47J16cr9ZwB07vP4J8+LH7qKQnuqkuIAvNWLzeN8tE5YBujZqJLB" crossorigin="anonymous">
    {{-- CSS --}}
    <link rel="stylesheet" href="{{ asset('css/app.css') }}">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <!-- Font Google -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@300;400;500;700&display=swap" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Lato:ital,wght@0,100;0,300;0,400;0,700;0,900;1,100;1,300;1,400;1,700;1,900&display=swap" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Bebas+Neue&display=swap" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Racing+Sans+One&display=swap" rel="stylesheet">
    <!-- Tingle.js CSS -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/tingle/0.15.3/tingle.min.css" />
    <!-- Tingle.js JS -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/tingle/0.15.3/tingle.min.js"></script>
    {{-- Snappy --}}
    <base href="{{ url('/') }}/">
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
</head>
@include('partials.activity_button')
@stack('scripts')
<body>
    <nav class="navbar sticky-top navbar-expand-lg navbar-dark shadow-sm">
        <div class="container-fluid container-lg">
            <a class="navbar-brand text-white fs-3 p-0 mb-1" href="{{ url('/') }}">
                <span style="font-family: 'Markazi Text'">AMG Lab</span> <span style="font-family: 'Racing Sans One'; font-size: 1.4rem">Praxis</span>
            </a>
{{-- Hamburger menu --}}
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#mainNav" aria-controls="mainNav" aria-expanded="false" aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="mainNav">
                <ul class="navbar-nav align-items-start align-items-lg-center gap-2 ms-2 me-lg-auto">
                    @guest
                    <li class="nav-item"><a class="nav-link text-white me-2" href="{{ route('login') }}">Accedi</a></li>
                    <li class="nav-item"><a class="nav-link text-white" href="{{ route('register') }}">Registrati</a></li>
                    @else
{{-- Dashboard --}}
                    <li class="nav-item">
                        <a href="{{ route('admin.dashboard') }}" class="nav-link text-white"><i class="fas fa-chart-pie me-1"></i> Dashboard</a>
                    </li>
{{-- Pratiche --}}
                    <li class="nav-item">
                        <a class="nav-link text-white" href="{{ route('admin.pratiche.index') }}"><i class="fas fa-folder-open me-1"></i> Pratiche</a>
                    </li>
{{-- Archivio --}}
                    <li class="nav-item">
                        <a class="nav-link text-white" href="{{ route('admin.pratiche.archive') }}"><i class="fas fa-archive me-1"></i> Archivio</a>
                    </li>
{{-- Cestino --}}
                    <li class="nav-item">
                        <a class="nav-link position-relative text-white ps-0 ms-lg-2" href="{{ route('admin.pratiche.trash') }}">
                            <i class="fas fa-trash-alt me-1"></i> Cestino
                            @if(isset($global_trash_count) && $global_trash_count > 0)
                                <span class="badge position-absolute top-0 end-100 rounded-1 bg-danger">{{ $global_trash_count }}</span>
                            @endif
                        </a>
                    </li>
{{-- Giacenze --}}
                    <li class="nav-item">
                        @include('partials.stock_dropdown')
                    </li>
                    @endguest
                </ul>
                <ul class="navbar-nav align-items-start align-items-lg-center ms-2 ms-lg-auto">
                    @auth
{{-- Notifiche giacenze --}}
                    <li class="nav-item">
                        @include('partials.stock_notify_dropdown')
                    </li>

{{-- Nome utente --}}
                    <li class="nav-item dropdown mt-2 mt-md-1 mt-lg-0">
                        <a class="nav-link dropdown-toggle text-white d-flex align-items-center" href="#" id="userMenu" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                            <div class="d-none d-md-block">
                                {{ optional(auth()->user())->name ?? 'Utente' }}
                            </div>
                            @php
                                $user = auth()->user();
                                $name = trim(optional($user)->name ?? 'Utente');
                                $parts = preg_split('/\s+/u', $name);
                                if (count($parts) === 1) {
                                    $initials = mb_strtoupper(mb_substr($parts[0], 0, 2, 'UTF-8'), 'UTF-8');
                                } else {
                                    $initials = mb_strtoupper(mb_substr($parts[0], 0, 1, 'UTF-8') . mb_substr($parts[count($parts)-1], 0, 1, 'UTF-8'), 'UTF-8');
                                }
                            @endphp
                            <div class="av av-sm av-gradient ms-0 ms-md-2" style="font-size: .9rem" role="img" aria-label="{{ $name }}">{{ e($initials) }}</div>
                        </a>
                        <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="userMenu">
{{-- Sessioni --}}
                            @php $user = auth()->user(); @endphp
                            <li class="dropdown-item-text small text-muted">
                                Ultimo accesso:
                                @if($user && $user->last_login_at)
                                    <em>{{ \Carbon\Carbon::parse($user->last_login_at)->setTimezone(config('app.timezone'))->format('d/m/Y H:i') }}</em>
                                @else
                                    mai
                                @endif
                                <br>
                                IP ultimo accesso: <strong>{{ $user && $user->last_login_ip ? $user->last_login_ip : '-' }}</strong>
                            </li>

                            <li><hr class="dropdown-divider"></li>
                            <li><a class="dropdown-item" href="{{ route('admin.sessions.index') }}"><i class="fas fa-shield-alt me-2"></i>Info sessione</a></li>
{{-- Log activity --}}
                            <li class="dropdown-item">
                                <a class="text-dark text-decoration-none" href="{{ route('admin.logs') }}"><i class="fas fa-history me-1"></i> Log attività</a>
                            </li>
                            <li><hr class="dropdown-divider"></li>
{{-- Logout --}}
                            <li>
                                <a class="dropdown-item" href="{{ route('logout') }}"
                                    onclick="event.preventDefault(); document.getElementById('logout-form').submit();">
                                    <i class="fas fa-sign-out-alt me-1"></i> Esci
                                </a>
                                <form id="logout-form" action="{{ route('logout') }}" method="POST" class="d-none">@csrf</form>
                            </li>
                        </ul>
                    </li>
                    @endauth
                </ul>
            </div>
        </div>
    </nav>

    <div class="container">
        <div class="mt-4">
            <small style="font-size: .8rem">@include('partials.breadcrumbs')</small>
        </div>
    </div>

    <div class="container">
        @if(session('status'))
            <div class="alert alert-success">{{ session('status') }}</div>
        @endif

        @yield('content')
    </div>
{{-- Footer --}}
    @includeIf('partials.footer')

    <style>
        @import url('https://fonts.googleapis.com/css2?family=Markazi+Text:wght@400..700&display=swap');
        html, body {
            font-family: 'Inter', sans-serif;
            background: linear-gradient(135deg, #fdfeff 0%, #fdfeff 100%);
            /* background-color: #fdfeff; */
            min-height: 100vh;
            font-size: .97em;
        }
        .navbar {
            background-color: #232f3e;
            height: 60px;
            z-index: 1030;

            @media screen and (max-width: 991px) {
                height: auto;
            }
        }
        #nav-overlay {
            position: fixed;
            inset: 0;               /* top:0; right:0; bottom:0; left:0; */
            background: rgba(0,0,0,0.25); /* opacità leggera — personalizza */
            z-index: 1020;
            display: none;
            cursor: default;
        }
        /* mostra overlay con una classe */
        #nav-overlay.show {
            display: block;
        }
        /* per accessibilità: rendiamo l'overlay non interattivo per screen readers */
        #nav-overlay[aria-hidden="true"] {
            pointer-events: auto;
        }
        .av {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            font-weight: 400;
            color: white;
            background: #6c757d;               /* default gray */
            user-select: none;
            border-radius: 50%;
            text-transform: uppercase;
            line-height: 1;
            font-family: Inter, system-ui, -apple-system, "Segoe UI", Roboto, "Helvetica Neue", Arial, sans-serif;
            box-shadow: 0 1px 2px rgba(0,0,0,0.06);
        }

        .av-sm { width: 32px; height: 32px; font-size: 12px; }
        .av-gradient {
            background: linear-gradient(135deg, #00c6ff 0%, #0072ff 100%);
        }
        .icon-btn {
            font-size: 1rem;
            line-height: 1;
            width: 36px;
            height: 35px;
        }

        .icon-btn i { pointer-events: none; } /* icona non cattura click */

        .icon-btn:focus { outline: 2px solid rgba(0,123,255,.25); outline-offset: 2px; }
        .log-props-compact .log-prop-line {
            display: block;
            max-width: 34ch;      /* lunghezza visiva, regola se vuoi più/meno spazio */
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
            font-size: .85rem;
            color: #333;
        }
        .log-props-compact .badge {
            cursor: pointer;
        }
        .pag-position {
            display: flex;
            justify-content: center;
            margin-top: 1rem;
        }
        .badge {
            padding: 3px 6px;
        }
    </style>

    <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.11.8/dist/umd/popper.min.js" integrity="sha384-I7E8VVD/ismYTF4hNIPjVp/Zjvgyol6VFvRkX/vR+Vc4jQkC+hVqc2pM8ODewa9r" crossorigin="anonymous"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.min.js" integrity="sha384-G/EV+4j2dNv+tEPo3++6LCgdCROaejBqfUeNjuKAiuXbjrxilcCdDz6ZAVfHWe1Y" crossorigin="anonymous"></script>
    <script src="{{ asset('js/app.js') }}"></script>

    @if(auth()->check())
        <script>
        (function() {
            // proteggi contro browser senza history api
            if (!window.history || !window.history.pushState) return;

            // Sostituisce l'entry corrente (utile se si viene da pagina di login)
            try {
                window.history.replaceState({}, document.title, window.location.href);
                // aggiunge una nuova entry identica: back riporterà a questa entry e non a login
                window.history.pushState({}, document.title, window.location.href);
            } catch(e) {
                // se fallisce, non interrompere l'esecuzione
                console.warn('history API non supportata o permessi limitati', e);
            }
            // Intercetta il back (popstate) e "annulla" la navigazione indietro
            window.addEventListener('popstate', function (event) {
                // Ripristina sempre la stessa entry, così l'utente non va indietro.
                try {
                    window.history.pushState({}, document.title, window.location.href);
                } catch (e) {}
                // opzionale: feedback visivo
                if (typeof Swal !== 'undefined') {
                    Swal.fire({
                            icon: 'info',
                            title: 'Impossibile tornare indietro',
                            text: 'Per tornare indietro effettua il logout o usa la navigazione interna.',
                            timer: 1400,
                            showConfirmButton: false
                        });
                } else {
                    // piccolo toast fallback (puoi personalizzarlo)
                    console.info('Back button disabilitato per motivi di sicurezza.');
                }
            }, false);
        })();

    // Gestione chiusura navbar in mobile
        document.addEventListener('DOMContentLoaded', function () {
            // Trova il collapse della navbar: preferiamo cercare l'elemento con .navbar-collapse all'interno della navbar
            var navbar = document.querySelector('.navbar'); // se hai piu' navbar, mira a quella corretta
            if (!navbar) return;

            // trova l'elemento collapse dentro la navbar
            var collapseEl = navbar.querySelector('.navbar-collapse');

            // se non c'è collapse (es. desktop only) esci
            if (!collapseEl) return;

            // crea l'overlay (una sola volta)
            var overlay = document.createElement('div');
            overlay.id = 'nav-overlay';
            overlay.setAttribute('aria-hidden', 'true');

            // aggiungiamo l'overlay al body
            document.body.appendChild(overlay);

            // funzione per ottenere l'istanza Bootstrap Collapse (se presente)
            function getCollapseInstance() {
                // Bootstrap 5: bootstrap.Collapse.getOrCreateInstance
                if (window.bootstrap && typeof window.bootstrap.Collapse !== 'undefined') {
                    return window.bootstrap.Collapse.getInstance(collapseEl) || null;
                }
                return null;
            }

            // mostra overlay quando il collapse è aperto
            collapseEl.addEventListener('shown.bs.collapse', function () {
                overlay.classList.add('show');
                // permetti al click di chiudere il menu
                overlay.addEventListener('click', onOverlayClick);
                // per touch su mobile
                overlay.addEventListener('touchstart', onOverlayClick);
            });

            // rimuovi overlay quando il collapse si chiude
            collapseEl.addEventListener('hidden.bs.collapse', function () {
                overlay.classList.remove('show');
                overlay.removeEventListener('click', onOverlayClick);
                overlay.removeEventListener('touchstart', onOverlayClick);
            });

            // funzione che chiude il collapse
            function onOverlayClick(e) {
                e.preventDefault();
                // non vogliamo che il click sull'overlay apra dropdown o altri eventi
                var inst = getCollapseInstance();
                if (inst) {
                    inst.hide();
                } else {
                    // fallback: rimuoviamo la classe show sul collapse
                    collapseEl.classList.remove('show');
                    overlay.classList.remove('show');
                }
            }

            /* EXTRA: chiudi il menu se l'utente clicca fuori MA anche non sull'overlay (ad es. su elementi della pagina
            che possono ricevere il click per qualche motivo) - attenzione a non chiudere se si clicca dentro la navbar
            o si interagisce con i dropdown interni. L'overlay gestisce la maggior parte dei casi, ma aggiungiamo
            un listener documentale per maggiore robustezza. */

            document.addEventListener('click', function (ev) {
                // se il menu non è aperto non fare nulla
                if (!collapseEl.classList.contains('show')) return;

                var tgt = ev.target;

                // se click dentro navbar (o nel collapse) non chiudere
                if (tgt.closest && tgt.closest('.navbar')) return;

                // se il click è su un dropdown che appartiene alla navbar (anche se il menu è append-to-body),
                // facciamo uno small check: se esiste un toggle dropdown dentro la navbar con aria-expanded="true",
                // e il click è dentro qualche .dropdown-menu visibile, non chiudere.
                var openNavbarDropdownToggle = document.querySelector('.navbar [data-bs-toggle="dropdown"][aria-expanded="true"]');
                if (openNavbarDropdownToggle) {
                    // cerca tutti i dropdown-menu visibili
                    var visibleMenus = document.querySelectorAll('.dropdown-menu.show');
                    for (var i=0; i<visibleMenus.length; i++) {
                        if (visibleMenus[i].contains(tgt)) {
                            // click dentro dropdown-menu relativo: non chiudere
                            return;
                        }
                    }
                }

                // se arriviamo qui, chiudiamo il collapse
                var inst = getCollapseInstance();
                if (inst) inst.hide();
                else {
                    collapseEl.classList.remove('show');
                    overlay.classList.remove('show');
                }
            }, true); // useCapture true per intercettare prima altri handlers (più affidabile)
        });
        </script>
    @endif
</body>
</html>
