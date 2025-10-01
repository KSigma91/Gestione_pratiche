<!doctype html>
<html lang="it">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Gestionale Pratiche - Admin</title>
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-sRIl4kxILFvY47J16cr9ZwB07vP4J8+LH7qKQnuqkuIAvNWLzeN8tE5YBujZqJLB" crossorigin="anonymous">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js" integrity="sha384-FKyoEForCGlyvwx9Hj09JcYn3nv7wiPVlz7YYwJrWVcXK/BmnVDxM+D2scQbITxI" crossorigin="anonymous"></script>
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
    <link href="https://cdnjs.cloudflare.com/ajax/libs/tingle/0.8.4/tingle.min.css" rel="stylesheet">
    <!-- Tingle.js JS -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/tingle/0.8.4/tingle.min.js"></script>
    {{-- Snappy --}}
    <base href="{{ url('/') }}/">
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
</head>
@include('partials.activity_button')
@stack('scripts')
<body>
    <nav class="navbar sticky-top navbar-expand-lg navbar-light mb-5 shadow-sm">
        <div class="container">
            <a class="navbar-brand text-white fs-3 p-0" href="{{ url('/') }}"><span style="font-family: 'Markazi Text'">AMG Lab</span> <span style="font-family: 'Racing Sans One'; font-size: 1.4rem">Praxis</span></a>
            <div class="collapse navbar-collapse d-flex justify-content-end">
                <ul class="navbar-nav">
                    @guest
                        <li class="nav-item"><a class="nav-link text-white me-2" href="{{ route('login') }}">Login</a></li>
                        <li class="nav-item"><a class="nav-link text-white" href="{{ route('register') }}">Registrati</a></li>
                    @else
{{-- Giacenze --}}
                        {{-- @include('partials.stock_dropdown') --}}
                        <li class="nav-item">
                            @include('partials.stock_notify_dropdown')
                        </li>

{{-- Cestino --}}
                        <li class="nav-item mx-2">
                            <a class="nav-link text-white" href="{{ route('admin.pratiche.trash') }}">
                                {{-- <span class="badge badge-secondary">{{ $global_trash_count ?? 0 }}</span> Cestino --}}
                                <span class="me-1 badge bg-secondary">{{ $global_trash_count ?? 0 }}</span>
                                <i class="fas fa-trash"></i>
                            </a>
                        </li>
{{-- Dashboard - index --}}
                        <li class="nav-item">
                            <a href="{{ route('admin.dashboard') }}" class="nav-link text-white">Dashboard</a>
                        </li>
{{-- Storico --}}
                        <li class="nav-item">
                            <a class="nav-link text-white" href="{{ route('admin.pratiche.archive') }}">Archivio</a>
                        </li>
{{-- Nome utente --}}
                        <li class="nav-item"><span class="nav-link text-white">{{ Auth::user()->name }}</span></li>
{{-- Logout --}}
                        <li class="nav-item">
                            <a class="nav-link text-white" href="#" onclick="event.preventDefault();document.getElementById('logout-form').submit();">Logout</a>
                            <form id="logout-form" action="{{ route('logout') }}" method="POST" style="display:none;">@csrf</form>
                        </li>
                    @endguest
                </ul>
            </div>
        </div>
    </nav>
    <div class="container mt-4">
        @if(session('status'))
            <div class="alert alert-success">{{ session('status') }}</div>
        @endif
        @yield('content')
    </div>

    <style>
        @import url('https://fonts.googleapis.com/css2?family=Markazi+Text:wght@400..700&display=swap');

        html, body {
            font-family: 'Inter', sans-serif;
        }

        .navbar {
            background-color: #232f3e;
        }

        /* pulsanti icona piccoli */
        .icon-btn {
            padding: 8px 10px;
            font-size: 1rem;
            line-height: 1;
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
    </style>

    <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.11.8/dist/umd/popper.min.js" integrity="sha384-I7E8VVD/ismYTF4hNIPjVp/Zjvgyol6VFvRkX/vR+Vc4jQkC+hVqc2pM8ODewa9r" crossorigin="anonymous"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.min.js" integrity="sha384-G/EV+4j2dNv+tEPo3++6LCgdCROaejBqfUeNjuKAiuXbjrxilcCdDz6ZAVfHWe1Y" crossorigin="anonymous"></script>

    @stack('scripts')
</body>
</html>
