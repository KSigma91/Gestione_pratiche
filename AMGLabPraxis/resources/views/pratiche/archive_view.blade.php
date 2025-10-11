{{-- resources/views/pratiche/archive_view.blade.php --}}
@extends('layouts.navbar')

@section('content')
@php
    use Carbon\Carbon;
    $monthName = isset($month) ? ucfirst(Carbon::create()->month((int)$month)->translatedFormat('F')) : '';
@endphp

<div class="py-4">
    <div class="d-flex flex-wrap justify-content-between align-items-center mb-3">
        <div>
            <h3 class="mb-0">Archivio pratiche
                @if(isset($year) && isset($month))
                    <small class="text-dark fs-6"> — {{ $monthName }} {{ $year }}</small>
                @elseif(isset($year))
                    <small class="text-dark fs-6"> — Anno {{ $year }}</small>
                @endif
            </h3>
            <small class="text-muted">Visuale dettagliata per mese</small>
        </div>

        <div class="d-flex flex-wrap gap-2 mt-2 mt-md-0">
            <a href="{{ route('admin.pratiche.index') }}" class="btn btn-outline-primary">
                <i class="fas fa-list me-1"></i> Lista pratiche
            </a>
            <a href="{{ route('admin.pratiche.archive') }}" class="btn btn-secondary">
                <i class="fas fa-arrow-left me-1"></i> Torna all'Archivio
            </a>
        </div>
    </div>
{{-- header grafico / sommario --}}
    <div class="row mb-3">
        <div class="col-md-4">
            <div class="card mb-3 border-0 text-white">
                <div class="card-body bg-card rounded-1">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="card-title mb-0">Totale</h6>
                            <div class="fs-4 fw-bold">{{ $pratiche->total() ?? $pratiche->count() }}</div>
                        </div>
                        <div class="text-end">
                            <i class="fas fa-archive fa-2x opacity-75"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-8">
            <div class="card h-100">
                <div class="card-body">
                    <div class="d-flex flex-column flex-md-row justify-content-between align-items-start gap-3">
                        <div>
                            <h6 class="mb-1">Mese</h6>
                            <p class="mb-0 text-muted">{{ $monthName }} {{ $year }}</p>
                        </div>
                        <div>
                            <h6 class="mb-1">Note rapide</h6>
                            <p class="mb-0 text-muted">Clicca su <span class="badge bg-dark"><i class="fas fa-eye me-1"></i> Anteprima</span> per aprire le note.</p>
                        </div>
                        <div class="text-start text-md-end">
                            <h6 class="mb-1">Visualizza</h6>
                            <p class="mb-0"><small class="text-muted">Anteprima</small></p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
{{-- lista pratiche in cards (timeline style) --}}
    <div class="row">
        <div class="col-12">
            @if($pratiche->isEmpty())
                <div class="card">
                    <div class="card-body text-center py-5">
                        <i class="fas fa-folder-open fa-2x text-muted mb-2"></i>
                        <p class="mb-0 text-muted">Nessuna pratica per questo periodo.</p>
                    </div>
                </div>
            @else
                <div class="timeline-container">
                    @foreach($pratiche as $p)
                        <article class="timeline-item mb-3">
                            <div class="card shadow-sm">
                                <div class="card-body">
                                    <div class="d-flex align-items-start">
                                        {{-- left meta --}}
                                        <div class="me-3 text-center" style="width: 110px;">
                                            <div class="bg-light rounded p-2 mb-2">
                                                <div class="fw-bold">{{ optional($p->data_arrivo)->format('d/m') }}</div>
                                                <small class="text-muted">{{ optional($p->data_arrivo)->format('H:i') }}</small>
                                            </div>
                                            <div class="small text-muted">ID {{ $p->id }}</div>
                                        </div>
{{-- main content --}}
                                        <div class="flex-fill">
                                            <div class="d-flex flex-wrap justify-content-between align-items-start">
                                                <div>
                                                    <h5 class="mb-1">{{ $p->codice }} <small style="color: lightslategrey">— {{ $p->cliente_nome }}</small></h5>
                                                    <div class="small text-primary">
                                                        <i class="fas fa-briefcase me-1"></i> {{ $p->tipo_pratica }}
                                                        @if(!empty($p->caso))
                                                            <span class="mx-2">•</span> <i class="fas fa-file-signature me-1"></i>{{ $p->caso }}
                                                        @endif
                                                    </div>
                                                </div>
                                                <div class="text-start text-md-end">
                                                    <div class="my-2">
                                                        @include('partials._state_badge', ['stato' => $p->stato])
                                                    </div>
                                                    <div class="mt-1">
                                                        <small class="text-muted">Arrivo: {{ $p->data_arrivo ? $p->data_arrivo->format('d/m/Y H:i') : '-' }}</small>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="d-flex flex-wrap gap-2 mt-2">
{{-- stato fattura --}}
                                                @if($p->stato_fattura === 'emessa')
                                                    <span class="badge bg-success" style="font-size: .82em"><i class="fas fa-file-invoice me-1 fs-5"></i> Emessa</span>
                                                @else
                                                    <span class="badge bg-secondary" style="font-size: .82em"><i class="fas fa-file-invoice me-1 fs-5"></i> Non emessa</span>
                                                @endif
{{-- stato pagamento --}}
                                                @if($p->stato_pagamento === 'pagato')
                                                    <span class="badge bg-success" style="font-size: .82em"><i class="fas fa-euro-sign me-1 fs-5"></i> Pagato</span>
                                                @else
                                                    <span class="badge bg-secondary" style="font-size: .82em"><i class="fas fa-euro-sign me-1 fs-5"></i> Non pagato</span>
                                                @endif
                                            </div>
{{-- note preview --}}
                                            <div class="mt-3">
                                                @if($p->note)
                                                    <p class="mb-2" style="max-width:80%;">{!! nl2br(e(\Illuminate\Support\Str::limit($p->note, 220))) !!}</p>
                                                @else
                                                    <p class="mb-2 text-muted">— Nessuna nota —</p>
                                                @endif
                                            </div>
{{-- action row --}}
                                            <div class="d-flex flex-wrap justify-content-between align-items-center mt-2">
                                                <div class="small text-muted">
                                                    Creato: {{ $p->created_at ? $p->created_at->format('d/m/Y H:i') : '-' }}
                                                </div>

                                                <div class="btn-group mt-2" role="group" aria-label="Azioni pratica">
{{-- Anteprima nota --}}
                                                    @if($p->note)
                                                        <button type="button" class="btn btn-sm btn-outline-secondary preview-note" data-note="{{ e($p->note) }}" title="Anteprima nota">
                                                            <i class="fas fa-eye me-1"></i> Anteprima
                                                        </button>
                                                    @endif
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </article>
                    @endforeach
                </div>

                <div class="pag-position">
                    {{ $pratiche->withQueryString()->links() }}
                </div>
            @endif
        </div>
    </div>
</div>
{{-- Modal per anteprima nota --}}
<div class="modal fade" id="previewNoteModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Anteprima nota</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Chiudi"></button>
            </div>
            <div class="modal-body" id="previewNoteBody"><div class="text-center text-muted py-3">Caricamento...</div></div>
                <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Chiudi</button>
            </div>
        </div>
    </div>
</div>
@endsection

<style>
.bg-card { background: linear-gradient(135deg, #4b6cb7 0%, #182848 100%); }

.timeline-container { position: relative; padding-left: 0; }
.timeline-item + .timeline-item { margin-top: 1rem; }

.timeline-item .card { border: 0; border-radius: .6rem; overflow: visible; }
.timeline-item .card-body { padding: 1rem; }

.badge.bg-warning.text-white { color: #212529 !important; }

@media (max-width: 767.98px) {
    .timeline-item .card { margin-left: 0; }
    .timeline-item .card-body { padding: .8rem; }
}
</style>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    // Elementi modal
    const previewModalEl = document.getElementById('previewNoteModal');
    if (!previewModalEl) return;

    // crea/recupera istanza bootstrap Modal
    let previewModal = bootstrap.Modal.getInstance(previewModalEl) || new bootstrap.Modal(previewModalEl, {
        keyboard: true,
        backdrop: true
    });

    const previewBody = document.getElementById('previewNoteBody');

    // mostra contenuto e apri modal
    document.querySelectorAll('.preview-note').forEach(function(btn){
        btn.addEventListener('click', function(){
            const raw = btn.getAttribute('data-note') || '';
            // decode HTML entities (we escaped server-side)
            const tmp = document.createElement('textarea'); tmp.innerHTML = raw;
            const decoded = tmp.value;
            // preserva i ritorni a capo
            const html = decoded.replace(/\r\n|\r|\n/g, '<br>');
            if (previewBody) previewBody.innerHTML = '<div class="small">' + html + '</div>';
            // (ri)crea l'istanza nel caso fosse stata disposed precedentemente
            previewModal = bootstrap.Modal.getOrCreateInstance(previewModalEl);
            previewModal.show();
        });
    });

    // Pulizia completa quando il modal viene nascosto
    previewModalEl.addEventListener('hidden.bs.modal', function () {
        // svuota il corpo
        if (previewBody) previewBody.innerHTML = '';

        // rimuovi eventuali backdrops rimasti
        document.querySelectorAll('.modal-backdrop').forEach(function(b){
            b.parentNode && b.parentNode.removeChild(b);
        });

        // assicurati che la classe modal-open non rimanga sul body
        document.body.classList.remove('modal-open');
        document.body.style.paddingRight = ''; // ripristina eventuale padding
    });
});
</script>
@endpush
