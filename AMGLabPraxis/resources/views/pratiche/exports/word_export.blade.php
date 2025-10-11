<!doctype html>
<html>
<head>
    <meta charset="utf-8" />
    <title>Pratiche</title>
    <style>
        body { font-family: Inter, sans-serif; font-size: 12px; }

        table { width:100%; border-collapse: collapse; }

        th, td { border: 1px solid #444; padding: 6px; text-align: left; vertical-align: top; }

        th { background: #eee; }

        h2 { margin-bottom: 0.5rem; }

        .word-export img { height: 16px; display: inline-block; vertical-align: middle; }
    </style>
</head>
<body>
    @if(!empty($logoSrc))
    <div class="word-export">
        <img src="{{ $logoSrc }}" alt="Logo">
    </div>
    @endif
    <h2>Pratiche</h2>
    <table border="1" cellpadding="4" cellspacing="0">
        <thead>
            <tr>
                <th>ID</th><th>Codice</th><th>Cliente</th><th>Tipo</th><th>Caso</th><th>Stato</th><th>Data Arrivo</th><th>Data Scadenza</th><th>Note</th>
            </tr>
        </thead>
        <tbody>
        @foreach($pratiche as $r)
            <tr>
                <td>{{ $r->id }}</td>
                <td>{{ $r->codice }}</td>
                <td>{{ $r->cliente_nome }}</td>
                <td>{{ $r->tipo_pratica }}</td>
                <td>{{ $r->caso }}</td>
                <td>{{ $r->stato }}</td>
                <td>{{ $r->data_arrivo ? $r->data_arrivo->format('d/m/Y H:i') : '' }}</td>
                <td>{{ $r->data_scadenza ? $r->data_scadenza->format('d/m/Y H:i') : '' }}</td>
                <td>{{ $r->note }}</td>
            </tr>
        @endforeach
        </tbody>
    </table>
</body>
</html>
