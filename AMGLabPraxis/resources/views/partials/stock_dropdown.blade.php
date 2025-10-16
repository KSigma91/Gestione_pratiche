@php
    if (!isset($global_giacenza_count) || !isset($global_recent_alerts)) {
        try {
            $global_giacenza_count = \App\Models\Practice::where('stato', 'in_giacenza')->count();
            $global_recent_alerts = \App\Models\Practice::where('stato', 'in_giacenza')
                ->orderBy('data_arrivo', 'asc')
                ->limit(8)
                ->get(['id', 'codice', 'cliente_nome', 'data_arrivo']);
        } catch (\Exception $e) {
            $global_giacenza_count = 0;
            $global_recent_alerts = collect();
        }
    }
@endphp

<div class="nav-item dropdown dropdown-hover">
    <a class="nav-link dropdown-toggle position-relative d-flex align-items-center text-white ps-0 ms-lg-2" href="#" id="giacenzaDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false">
        <i class="fas fa-clock me-2"></i>

        {{-- Mostra il badge solo se count > 0 --}}
        @if(($global_giacenza_count ?? 0) > 0)
            <span class="badge bg-danger position-absolute top-0 end-100 rounded-1">
                {{ $global_giacenza_count }}
            </span>
        @endif

        <span class="text-decoration-none">Giacenze</span>
    </a>

    <ul class="dropdown-menu dropdown-menu-end shadow-sm p-2" aria-labelledby="giacenzaDropdown"
        style="min-width:320px; max-width:420px;">
        <li class="px-2">
            <small class="d-block">
                Pratiche in giacenza ({{ $global_giacenza_count ?? 0 }})
            </small>
        </li>
        <li><hr class="dropdown-divider"></li>

        @if(($global_recent_alerts ?? collect())->isEmpty())
            <li>
                <span class="dropdown-item-text text-muted small">
                    Nessuna pratica in giacenza
                </span>
            </li>
        @else
            <div style="max-height:300px; overflow:auto;">
                @foreach($global_recent_alerts as $a)
                    <li>
                        <a class="dropdown-item d-flex justify-content-between align-items-start"
                           href="{{ route('admin.pratiche.index') }}?cliente={{ urlencode($a->cliente_nome) }}">
                            <div>
                                <strong class="small">{{ $a->codice }}</strong><br>
                                <small class="text-muted">{{ $a->cliente_nome }}</small>
                            </div>
                            <small class="text-muted">
                                {{ \Carbon\Carbon::parse($a->data_arrivo)->format('d/m/Y') }}
                            </small>
                        </a>
                    </li>
                @endforeach
            </div>
        @endif

        <li><hr class="dropdown-divider"></li>
        <li class="text-center">
            <a class="dropdown-item text-primary"
               href="{{ route('admin.pratiche.index', ['stato' => 'in_giacenza']) }}">
                <small>Vedi tutte le giacenze</small>
            </a>
        </li>
    </ul>
</div>


{{-- JS to enable hover-open on non-touch devices (Bootstrap 5) --}}
@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
        if (window.matchMedia && window.matchMedia('(hover: hover)').matches) {
        document.querySelectorAll('.dropdown-hover').forEach(function (el) {

            var toggle = el.querySelector('.dropdown-toggle');

            var bs = bootstrap.Dropdown.getOrCreateInstance(toggle);

            el.addEventListener('mouseenter', function () {
                bs.show();
            });
            el.addEventListener('mouseleave', function () {
                bs.hide();
            });
        });
    }
});
</script>
@endpush

{{-- small CSS tweak (you can put it in your main CSS): --}}
@push('styles')
<style>
.dropdown-hover .dropdown-menu { padding: 0.5rem; }
.dropdown-hover .dropdown-item { padding-top: .5rem; padding-bottom: .5rem; }
/* stile per il cestino moderno */
.card-shadow {
    box-shadow: 0 0.125rem 0.5rem rgba(0,0,0,0.1);
}

.card-title {
    font-size: 1.25rem;
    margin-bottom: 0.5rem;
}

.card-text {
    margin-bottom: 0.25rem;
}

.btn-group-sm .btn {
    padding: 0.25rem 0.5rem;
    font-size: 0.85rem;
}

.card:hover {
    background-color: #f8f9fa;
}

</style>
@endpush
