<!doctype html>
<html>
<head>
    <meta charset="utf-8" />
    <title>Pratiche {{ $year ?? '' }}</title>
    <style>
        body { font-family: Inter, sans-serif; font-size: 12px; }

        table { width:100%; border-collapse: collapse; }

        th, td { border: 1px solid #444; padding: 6px; text-align: left; vertical-align: top; }

        th { background: #eee; }

        h3 { margin-bottom: 0.5rem; }

        .pdf-export img { height: 50px; display:inline-block; vertical-align:middle; }
    </style>
</head>
<body>
    @if(!empty($logoFileUrl))
    <div class="pdf-export">
        <img src="{{ $logoFileUrl }}" alt="Logo">
    </div>
    @endif
    <h3>Pratiche - {{ $year ?? '' }}</h3>

    <table>
        <thead>
            <tr>
                <th>ID</th>
                <th>Codice</th>
                <th>Cliente</th>
                <th>Tipo</th>
                <th>Caso</th>
                <th>Stato</th>
                <th>Data Arrivo</th>
            </tr>
        </thead>
        <tbody>
        @forelse($pratiche as $r)
            <tr>
                <td>{{ $r->id }}</td>
                <td>{{ $r->codice }}</td>
                <td>{{ $r->cliente_nome }}</td>
                <td>{{ $r->tipo_pratica }}</td>
                <td>{{ $r->caso }}</td>
                <td>{{ $r->stato }}</td>
                <td>{{ $r->data_arrivo ? $r->data_arrivo->format('d/m/Y H:i') : '' }}</td>
            </tr>
        @empty
            <tr>
                <td colspan="7" class="text-center">Nessuna pratica trovata per questo periodo.</td>
            </tr>
        @endforelse
        </tbody>
    </table>
</div>

</body>
</html>
