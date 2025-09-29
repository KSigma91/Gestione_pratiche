@extends('layouts.navbar')

@section('content')
<div class="container">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h3>Pratiche di {{ \Carbon\Carbon::parse("$year-$month-01")->locale('it')->isoFormat('MMMM YYYY') }}</h3>
        <a class="btn btn-secondary mb-3" href="{{ route('admin.pratiche.archive') }}"><i class="fas fa-arrow-left me-2"></i>Torna all'archivio</a>
    </div>

    <div class="table-responsive">
        <table class="table table-striped table-hover table-sm">
            <thead class="table-light">
                <tr>
                    <th>#</th>
                    <th>Codice</th>
                    <th>Cliente</th>
                    <th>Tipo</th>
                    <th>Caso</th>
                    <th>Stato</th>
                    <th>Arrivo</th>
                    <th>Azioni</th>
                </tr>
            </thead>
            <tbody>
                @foreach($pratiche as $p)
                <tr>
                    <td>{{ $p->id }}</td>
                    <td>{{ $p->codice }}</td>
                    <td>{{ $p->cliente_nome }}</td>
                    <td>{{ $p->tipo_pratica }}</td>
                    <td>{{ $p->caso ?? '-' }}</td>
                    <td>
                        @switch($p->stato)
                            @case('in_giacenza')
                            <span class="badge bg-warning text-dark">In giacenza</span>
                            @break
                            @case('in_lavorazione')
                            <span class="badge bg-info text-dark">In lavorazione</span>
                            @break
                            @case('completata')
                            <span class="badge bg-success">Completata</span>
                            @break
                            @case('annullata')
                            <span class="badge bg-secondary">Annullata</span>
                            @break
                            @default
                            <span class="badge bg-info">{{ ucfirst(str_replace('_', ' ', $p->stato)) }}</span>
                        @endswitch
                    </td>
                    <td>
                        @if($p->data_arrivo)
                          Il {{ $p->data_arrivo->format('d/m/Y') }} alle {{ $p->data_arrivo->format('H:i') }}
                        @else
                          -
                        @endif
                    </td>
                    <td>
                        {{-- Azioni: modifica, cestino, elimina, ecc --}}
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    <div class="mt-3">
        {{ $pratiche->links() }}
    </div>
</div>
@endsection
