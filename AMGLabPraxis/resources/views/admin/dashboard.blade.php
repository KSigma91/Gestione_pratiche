@extends('layouts.navbar')

@section('content')
<style>
body {
    background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='40' height='40' opacity='0.2'%3E%3Crect width='20' height='20' fill='%23ffffff'/%3E%3Crect x='20' y='20' width='20' height='20' fill='%23ffffff'/%3E%3C/svg%3E");
    background-color: #f8f9fa;
}
.kpi-card {
    border: 0;
    border-radius: 12px;
    color: #fff;
    overflow: hidden;
    position: relative;
}
.kpi-card .card-body { position: relative; z-index: 2; }
.kpi-bg-1 { background: linear-gradient(135deg,#5b8cff 0%,#7bdff6 100%); }
.kpi-bg-2 { background: linear-gradient(135deg,#ffb86b 0%,#ff7a7a 100%); }
.kpi-bg-3 { background: linear-gradient(135deg,#34d399 0%,#10b981 100%); }
.kpi-bg-4 { background: linear-gradient(135deg,#a78bfa 0%,#7c3aed 100%); }

.kpi-icon {
    position: absolute;
    right: 12px;
    top: 12px;
    opacity: 0.12;
    font-size: 56px;
}

.chart-card {
    height: 500px;
    position: relative;
}

@media screen and (max-width: 767px) {
    .chart-card {
        height: 830px;
    }
}

@media screen and (max-width: 500px) {
    .chart-card {
        height: 750px;
    }
}
.chart-card canvas {
    width: 100% !important;
    height: 100% !important;
    display: block;
}


.card-ghost {
    background: rgba(255,255,255,0.02);
    border-radius: 12px;
    border: 1px solid rgba(255,255,255,0.03);
}

.small-muted { font-size: .86rem; color: #6b7280; }

.recent-list .list-group-item { border-radius: 8px; margin-bottom: .5rem; }
.chart-card { border-radius: 12px; box-shadow: 0 6px 18px rgba(11,15,30,0.05); }

@media (prefers-color-scheme: light) {
  .kpi-card { color: #fff; }
  .card-ghost { background: #fff; border-color: rgba(0,0,0,0.06); }
}
</style>

<div class="py-4">
    <div class="d-flex flex-wrap justify-content-between align-items-start mb-3">
        <div>
            <h2 class="mb-2">Dashboard <span class="text-dark fs-5">— Panoramica</span></h2>
            <div class="small-muted">Ultimo aggiornamento: {{ \Carbon\Carbon::now()->setTimezone(config('app.timezone'))->format('d/m/Y H:i') }}</div>
        </div>
        <div class="d-flex justify-content-end align-items-center gap-2 my-3 my-md-0">
            <a href="{{ route('admin.pratiche.index') }}" class="btn btn-outline-primary btn-sm">
                <i class="fas fa-list me-1"></i> Lista pratiche
            </a>
            <a href="{{ route('admin.pratiche.create') }}" class="btn btn-sm text-white" style="background: dodgerblue ">
                <i class="fas fa-plus me-1"></i> Nuova Pratica
            </a>
        </div>
    </div>
<!-- KPI -->
    <div class="row g-3 mb-4">
        <div class="col-sm-6 col-md-3">
            <div class="card kpi-card kpi-bg-1">
                <div class="card-body">
                    <div class="kpi-icon"><i class="fas fa-folder-open"></i></div>
                    <h6 class="mb-1">Totale pratiche</h6>
                    <div class="d-flex align-items-end">
                        <h3 class="me-3 mb-0">{{ number_format($totale,0,',','.') }}</h3>
                        <small class="small-muted">Tutti gli stati</small>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-sm-6 col-md-3">
            <div class="card kpi-card kpi-bg-2">
                <div class="card-body">
                    <div class="kpi-icon"><i class="fas fa-clock"></i></div>
                    <h6 class="mb-1">In giacenza</h6>
                    <div class="d-flex align-items-end">
                        <h3 class="me-3 mb-0">{{ number_format($inGiacenza,0,',','.') }}</h3>
                        <small class="small-muted">Da controllare</small>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-sm-6 col-md-3">
            <div class="card kpi-card kpi-bg-3">
                <div class="card-body">
                    <div class="kpi-icon"><i class="fas fa-briefcase"></i></div>
                    <h6 class="mb-1">In lavorazione</h6>
                    <div class="d-flex align-items-end">
                        <h3 class="me-3 mb-0">{{ number_format($inLavorazione,0,',','.') }}</h3>
                        <small class="small-muted">Attive</small>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-sm-6 col-md-3">
            <div class="card kpi-card kpi-bg-4">
                <div class="card-body">
                    <div class="kpi-icon"><i class="fas fa-check-circle"></i></div>
                    <h6 class="mb-1">Completate</h6>
                    <div class="d-flex align-items-end">
                        <h3 class="me-3 mb-0">{{ number_format($completate,0,',','.') }}</h3>
                        <small class="small-muted">Chiuse</small>
                    </div>
                </div>
            </div>
        </div>
    </div>
<!-- Charts + recenti -->
    <div class="row g-4">
        <div class="col-lg-8">
            <div class="card chart-card p-3 pb-5">
                <div class="d-flex justify-content-between align-items-center mb-2">
                    <div>
                        <h5 class="mb-0">Pratiche messe in giacenza (ultimi 12 mesi)</h5>
                        <small class="small-muted">Trend mensile</small>
                    </div>
                    <div class="small-muted">Visualizzazione: ultimi 12 mesi</div>
                </div>
                <canvas id="chartGiacenza" style="padding-bottom: 3rem" height="50"></canvas>
            </div>
            <div class="card chart-card mt-5 p-3">
                <div class="d-flex justify-content-between align-items-center mb-2">
                    <div>
                        <h5 class="mb-0">Distribuzione stato</h5>
                        <small class="small-muted">Percentuale per stato</small>
                    </div>
                    <div class="small-muted">Aggiornato ora</div>
                </div>
                <div class="row">
                    <div class="col-md-6 d-flex align-items-center justify-content-center">
                        <canvas id="chartStato" style="max-width:320px;"></canvas>
                    </div>
                    <div class="col-md-6">
                        <ul class="list-unstyled mb-0">
                            @foreach($statiCount as $stato => $cnt)
                                <li class="d-flex justify-content-between align-items-center py-2 border-bottom">
                                    <div>
                                        <strong>{{ ucfirst(str_replace('_',' ', $stato)) }}</strong>
                                        <div class="small-muted">Pratiche</div>
                                    </div>
                                    <div><span class="badge bg-secondary rounded-pill">{{ $cnt }}</span></div>
                                </li>
                            @endforeach
                        </ul>
                    </div>
                </div>
            </div>
        </div>
<!-- Colonna destra: recenti + attività -->
        <div class="col-lg-4">
            <div class="card mb-3 p-3">
                <h6 class="mb-2">Ultime pratiche aggiornate</h6>
                <div class="recent-list">
                    @forelse($recent as $r)
                        <div class="list-group-item">
                            <div class="d-flex justify-content-between">
                                <div>
                                    <a href="{{ route('admin.pratiche.edit', $r->id) }}" class="fw-bold text-decoration-none">{{ $r->codice }}</a>
                                    <div class="small-muted">{{ $r->cliente_nome }}</div>
                                </div>
                                <div class="text-end small-muted">
                                    {{ \Carbon\Carbon::parse($r->updated_at)->setTimezone(config('app.timezone'))->diffForHumans() }}
                                    <div class="mt-1"><span class="badge bg-secondary bg-opacity-25 text-dark">{{ ucfirst(str_replace('_',' ', $r->stato)) }}</span></div>
                                </div>
                            </div>
                        </div>
                    @empty
                        <div class="text-muted">Nessuna pratica recente</div>
                    @endforelse
                </div>
            </div>
            @if(!empty($recentActivities) && count($recentActivities))
            <div class="card card-ghost p-3">
                <h6 class="mb-2">Ultime attività</h6>
                <ul class="list-unstyled mb-0">
                    @foreach($recentActivities as $act)
                        <li class="py-2 border-bottom">
                            <div class="small"><strong>{{ $act->description }}</strong></div>
                            <div class="small-muted">{{ \Carbon\Carbon::parse($act->created_at)->setTimezone(config('app.timezone'))->format('d/m/Y H:i') }}
                                @if($act->causer) — {{ optional($act->causer)->name }} @endif
                            </div>
                        </li>
                    @endforeach
                </ul>
            </div>
            @endif
        </div>
    </div>
</div>
@endsection

@push('scripts')
<!-- Chart.js -->
<script src="https://cdn.jsdelivr.net/npm/chart.js@3.9.1/dist/chart.min.js"></script>

<script>
document.addEventListener('DOMContentLoaded', function () {
    // Dati da Laravel
    const labels = @json($labels);
    const dataGiacenza = @json($datasets);
    const statiCount = @json($statiCount);

    // === Line chart con gradient ===
    const ctx = document.getElementById('chartGiacenza').getContext('2d');
    var gradient = ctx.createLinearGradient(0, 0, 0, 300);
    gradient.addColorStop(0, 'rgba(91,140,255,0.35)');
    gradient.addColorStop(1, 'rgba(123,223,246,0.05)');

    new Chart(ctx, {
        type: 'line',
        data: {
            labels: labels,
            datasets: [{
                label: 'Mesi',
                data: dataGiacenza,
                fill: true,
                backgroundColor: gradient,
                borderColor: '#3b82f6',
                pointBackgroundColor: '#ffffff',
                pointBorderColor: '#3b82f6',
                tension: 0.35,
                pointRadius: 4,
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                tooltip: {
                    mode: 'index',
                    intersect: false,
                    callbacks: {
                        label: function(context) {
                            return context.dataset.label + ': ' + context.formattedValue + ' pratiche';
                        }
                    }
                },
                legend: { display: false }
            },
            scales: {
                x: {
                    grid: { display: true }
                },
                y: {
                    beginAtZero: true,
                    ticks: { precision:0 }
                }
            }
        }
    });

    // === Doughnut chart ===
    const ctx2 = document.getElementById('chartStato').getContext('2d');
    const labelsStato = Object.keys(statiCount);
    const dataStato = Object.values(statiCount);

    // palette semplice (auto-expand if needed)
    const palette = [
        '#ffcc66','#34d399','#60a5fa','#fb7185','#a78bfa','#f97316'
    ];
    new Chart(ctx2, {
        type: 'doughnut',
        data: {
            labels: labelsStato.map(s => s.replace('_',' ')),
            datasets: [{
                data: dataStato,
                backgroundColor: palette.slice(0, labelsStato.length),
                hoverOffset: 6
            }]
        },
        options: {
            responsive: true,
            plugins: {
                legend: { position: 'bottom' },
                tooltip: {
                    callbacks: {
                        label: function(context) {
                            const label = context.label || '';
                            const value = context.parsed || 0;
                            return label + ': ' + value + ' pratiche';
                        }
                    }
                }
            }
        }
    });
});
</script>
@endpush
