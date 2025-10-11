@extends('layouts.navbar')

@section('content')
<div class="py-4">
    <div class="d-flex flex-wrap justify-content-between align-items-start mb-4">
        <h3 class="mb-0">Dettaglio Pratica</h3>
        <div class="mt-2 mt-md-0">
            <a href="{{ route('admin.pratiche.index') }}" class="btn btn-secondary">
                <i class="fas fa-arrow-left me-1"></i> Torna alla lista
            </a>
        </div>
    </div>

    <div class="card shadow-sm mb-4">
        <div class="card-header bg-light">
            <h5 class="mb-0">{{ $pratica->codice ?? 'Pratica #' . $pratica->id }}</h5>
        </div>
        <div class="card-body">
            <div class="row mb-3 lh-lg">
                <div class="col-md-6">
                    <strong>Cliente:</strong> {{ $pratica->cliente_nome }}<br>
                    <strong>Tipo Pratica:</strong> {{ $pratica->tipo_pratica }}<br>
                    <strong>Caso:</strong> {{ $pratica->caso ?? '-' }}<br>
                </div>
                <div class="col-md-6">
                    <strong>Stato:</strong>
                    @include('partials._state_badge', ['stato' => $pratica->stato])<br>
                    <strong>Data Arrivo:</strong>
                    {{ $pratica->data_arrivo ? $pratica->data_arrivo->format('d/m/Y H:i') : '-' }}<br>
                    <strong>Data Scadenza:</strong>
                    {{ $pratica->data_scadenza ? $pratica->data_scadenza->format('d/m/Y H:i') : '-' }}<br>
                </div>
                <div class="col-12 mt-3">
                    <strong class="me-1">Fattura:</strong>
                    @if($pratica->stato_fattura === 'emessa')
                        <span class="text-success"><i class="fas fa-file-invoice fs-5"></i></span>
                    @else
                        <span class="text-muted"><i class="fas fa-file-invoice fs-5"></i></span><br>
                    @endif
                </div>
                <div class="col-12">
                    <strong class="me-1">Pagamento:</strong>
                    @if($pratica->stato_pagamento === 'pagato')
                        <span class="text-success"><i class="fas fa-euro-sign fs-5"></i></span>
                    @else
                        <span class="text-muted"><i class="fas fa-euro-sign fs-5"></i></span>
                    @endif
                </div>
            </div>

            <hr>

            <div class="mb-3">
                <strong>Note:</strong>
                @if($pratica->note)
                    <div class="mt-2 p-3 bg-light rounded">
                        {!! nl2br(e($pratica->note)) !!}
                    </div>
                @else
                    <p class="text-muted">Nessuna nota inserita.</p>
                @endif
            </div>

            <hr>

            <div class="d-flex justify-content-between align-items-center text-end">
                <a href="{{ route('admin.pratiche.edit', $pratica->id) }}" class="btn text-white" style="background-color: #3B71CA">
                    <i class="fas fa-edit"></i> Modifica
                </a>
                <small class="text-muted" style="font-size: .8rem">
                    Creata: {{ $pratica->created_at ? $pratica->created_at->format('d/m/Y H:i') : '-' }}<br>
                    Ultima modifica: {{ $pratica->updated_at ? $pratica->updated_at->format('d/m/Y H:i') : '-' }}
                </small>
            </div>
        </div>
    </div>
</div>
@endsection

