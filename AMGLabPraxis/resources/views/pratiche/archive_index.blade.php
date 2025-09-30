@extends('layouts.navbar')

@section('content')
<div class="container py-4">
    <div class="row justify-content-center">
        <div class="col-lg-8">

            <div class="d-flex justify-content-between align-items-center mb-4">
                <h3 class="mb-0">Archivio pratiche</h3>
                <a class="btn btn-secondary" href="{{ route('admin.pratiche.index') }}">
                    <i class="fas fa-arrow-left me-1"></i> Torna alla lista
                </a>
            </div>

            @if(($archive ?? collect())->isEmpty())
                <div class="card">
                    <div class="card-body">
                        <div class="alert alert-info mb-0">Nessuna pratica archiviata al momento.</div>
                    </div>
                </div>
            @else
                @php
                    $grouped = $archive->groupBy('year');
                @endphp

                @foreach($grouped as $year => $months)
                    @php
                        // id unico per collapse (es. year-2025)
                        $collapseId = 'archive-year-' . $year;
                    @endphp

                    <div class="card mb-3 shadow-sm">
                        <div class="card-header d-flex justify-content-between align-items-center">
                            <div>
                                <button type="button" class="btn btn-link text-decoration-none p-0 toggle-archive" data-bs-target="#{{ $collapseId }}">
                                    <h5 class="mb-0">{{ $year }}</h5>
                                </button>
                                <small class="text-muted d-block">Mesi e numero pratiche</small>
                            </div>
                            <div>
                                <button type="button" class="btn btn-sm btn-outline-primary toggle-archive" data-bs-target="#{{ $collapseId }}">
                                    Mostra/Nascondi
                                </button>
                            </div>
                        </div>

                        <div id="{{ $collapseId }}" class="collapse">
                            <div class="card-body">
                                <div class="row g-2">
                                    @foreach($months as $item)
                                        @php
                                            $m = (int) $item->month;
                                            $count = $item->total;
                                            // mese in italiano (assicurati che Carbon::setLocale('it') sia stato chiamato)
                                            $monthName = \Carbon\Carbon::create()->month($m)->translatedFormat('F');
                                            $monthNameUc = ucfirst($monthName);
                                        @endphp

                                        <div class="col-auto">
                                            <a href="{{ route('admin.pratiche.archive.view', ['year' => $year, 'month' => $m]) }}" class="btn btn-outline-secondary btn-sm d-inline-flex align-items-center" title="Visualizza pratiche per {{ $monthNameUc }} {{ $year }}">
                                                <i class="far fa-calendar-alt me-2"></i>
                                                <span>{{ $monthNameUc }}</span>
                                                <span class="badge bg-primary ms-2">{{ $count }}</span>
                                            </a>
                                        </div>
                                    @endforeach
                                </div>
                                <div class="btn-group d-flex justify-content-end" role="group" aria-label="Export">
                                    <div class="btn-group-sm d-flex align-items-center gap-2 mt-5">
                                        <small><b>Esporta con: </b></small>
                                        <a href="{{ route('admin.pratiche.export.year.csv', $year) }}" class="btn btn-outline-secondary" title="Esporta CSV">
                                            <i class="fas fa-file-csv"></i> CSV
                                        </a>

                                        <a href="{{ route('admin.pratiche.export.year.excel', $year) }}" class="btn btn-outline-success" title="Esporta Excel">
                                            <i class="fas fa-file-excel"></i> Excel
                                        </a>

                                        <a href="{{ route('admin.pratiche.export.year.word', $year) }}" class="btn btn-outline-primary" title="Esporta Word">
                                            <i class="fas fa-file-word"></i> Word
                                        </a>

                                        <a href="{{ route('admin.pratiche.export.year.pdf', $year) }}" class="btn btn-outline-danger" title="Esporta PDF" target="_blank">
                                            <i class="fas fa-file-pdf"></i> PDF
                                        </a>
                                    </div>
                                </div>
                            </div>
                            <div class="card-footer text-muted">
                                Totale mesi: {{ $months->count() }}
                            </div>
                        </div>
                    </div>
                @endforeach
            @endif

        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    // listener delegato per tutti i pulsanti .toggle-archive
    document.querySelectorAll('.toggle-archive').forEach(function(btn) {
        btn.addEventListener('click', function(e) {
            e.preventDefault();
            var targetSelector = btn.getAttribute('data-bs-target');
            if (!targetSelector) return;
            var targetEl = document.querySelector(targetSelector);
            if (!targetEl) return;

            // Ottieni o crea l'istanza Collapse e fai il toggle
            var collapseInstance = bootstrap.Collapse.getOrCreateInstance(targetEl, {toggle: false});
            collapseInstance.toggle();
        });
    });
});
</script>
@endpush
