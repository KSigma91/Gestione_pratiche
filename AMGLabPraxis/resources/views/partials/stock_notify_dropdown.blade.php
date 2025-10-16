<li class="nav-item dropdown dropdown-hover mt-2 mt-lg-0">
    <a class="nav-link dropdown-toggle position-relative text-white ps-0 ms-lg-1" href="#" id="dropdownNotifiche" role="button" data-bs-toggle="dropdown" aria-expanded="false">
        @if($global_notifiche_giacenza->count() > 0)
            <span class="badge bg-danger position-absolute top-0 end-100">{{ $global_notifiche_giacenza->count() }}</span>
        @endif
        <i class="fas fa-bell"></i>
    </a>
    <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="dropdownNotifiche">
        @forelse($global_notifiche_giacenza as $not)
            <li class="dropdown-item">
                <a href="{{ route('admin.pratiche.edit', $not->pratica_id) }}" class="d-flex align-items-center text-dark">
                    <span class="">Pratica <strong class="fst-italic">{{ $not->pratica->codice }}</strong> è in giacenza da oltre 15 giorni</span>
                    <form method="POST" action="{{ route('admin.notifiche.markLetta', $not->id) }}">
                        @csrf
                        <button type="submit" class="btn btn-sm text-danger fs-5"><i class="fab fa-readme"></i></i></button>
                    </form>
                </a>
            </li>
        @empty
            <li class="dropdown-item text-muted small">Nessuna notifica</li>
        @endforelse
    </ul>
</li>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    if (window.matchMedia && window.matchMedia('(hover: hover)').matches) {
        document.querySelectorAll('.dropdown-hover').forEach(function (el) {

            var toggle = el.querySelector('.dropdown-toggle');
            var bs = bootstrap.Dropdown.getOrCreateInstance(toggle);
            var timeoutShow, timeoutHide;

            el.addEventListener('mouseenter', function () {
                clearTimeout(timeoutHide);
                timeoutShow = setTimeout(function () {
                    bs.show();
                }, 100);
            });

            el.addEventListener('mouseleave', function () {
                clearTimeout(timeoutShow);
                timeoutHide = setTimeout(function () {
                    bs.hide();
                }, 100);
            });
        });
    }
});
</script>
@endpush

{{-- small CSS tweak (you can put it in your main CSS): --}}
@push('styles')
<style>
.dropdown-hover .dropdown-menu {
    display: none;
    opacity: 0;
    transition: opacity 0.2s ease-in-out;
 }
.dropdown-hover:hover .dropdown-item {
    display: block;
    opacity: 1;
 }
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
